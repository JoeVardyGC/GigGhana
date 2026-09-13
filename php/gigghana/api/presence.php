<?php
/**
 * GigGhana — api/presence.php
 *
 * Handles real-time presence for the messaging system.
 * Called via fetch() from both client and provider messages pages.
 *
 * Actions:
 *   heartbeat   – mark user as online, update conversation last_seen
 *   typing      – set is_typing=1 for this user in this conversation
 *   stop_typing – set is_typing=0
 *   poll        – return {online, typing, unread_count, last_messages[]}
 *
 * Tables used:
 *   users                (last_seen)           ← ALTER already applied
 *   conversation_status  (is_typing, last_seen_at) ← CREATE already applied
 *   messages             (for poll new messages)
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

/* ── JSON only ── */
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

/* ── Auth ── */
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorised']);
    exit;
}

$userId = (int)$_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$convId = (int)($_POST['conversation_id'] ?? $_GET['conversation_id'] ?? 0);

/* ── CSRF for write actions ── */
$writeActions = ['heartbeat', 'typing', 'stop_typing'];
if (in_array($action, $writeActions)) {
    if (!verifyCSRF($_POST['csrf'] ?? '')) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        exit;
    }
}

try {
    $db = getDB();

    /* ────────────────────────────────────────
       HEARTBEAT
       Called every 30 seconds while page is open.
       Updates:
         users.last_seen = NOW()
         conversation_status.last_seen_at = NOW()  (upsert)
    ──────────────────────────────────────── */
    if ($action === 'heartbeat') {
        /* Update user last_seen */
        $db->prepare(
            "UPDATE users SET last_seen = NOW() WHERE id = ?"
        )->execute([$userId]);

        /* Upsert conversation_status if in a conversation */
        if ($convId) {
            /* Verify user belongs to this conversation */
            $stV = $db->prepare(
                "SELECT id FROM conversations
                 WHERE id = ? AND (user1_id = ? OR user2_id = ?) LIMIT 1"
            );
            $stV->execute([$convId, $userId, $userId]);
            if ($stV->fetchColumn()) {
                $db->prepare("
                    INSERT INTO conversation_status (conversation_id, user_id, is_typing, last_seen_at)
                    VALUES (?, ?, 0, NOW())
                    ON DUPLICATE KEY UPDATE last_seen_at = NOW()
                ")->execute([$convId, $userId]);
            }
        }

        echo json_encode(['success' => true]);
        exit;
    }

    /* ────────────────────────────────────────
       TYPING  /  STOP_TYPING
    ──────────────────────────────────────── */
    if ($action === 'typing' || $action === 'stop_typing') {
        if (!$convId) {
            echo json_encode(['success' => false, 'message' => 'No conversation']);
            exit;
        }

        /* Verify membership */
        $stV = $db->prepare(
            "SELECT id FROM conversations
             WHERE id = ? AND (user1_id = ? OR user2_id = ?) LIMIT 1"
        );
        $stV->execute([$convId, $userId, $userId]);
        if (!$stV->fetchColumn()) {
            echo json_encode(['success' => false, 'message' => 'Not your conversation']);
            exit;
        }

        $isTyping = ($action === 'typing') ? 1 : 0;
        $db->prepare("
            INSERT INTO conversation_status (conversation_id, user_id, is_typing, last_seen_at)
            VALUES (?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE is_typing = ?, last_seen_at = NOW()
        ")->execute([$convId, $userId, $isTyping, $isTyping]);

        echo json_encode(['success' => true]);
        exit;
    }

    /* ────────────────────────────────────────
       POLL
       Called every 3 seconds while in a conversation.
       Returns:
         other_online  bool   – is the OTHER user online (last_seen ≤ 5 min ago)
         other_typing  bool   – is the OTHER user currently typing
         unread_count  int    – messages we haven't read yet
         new_messages  array  – messages newer than ?since_id
    ──────────────────────────────────────── */
    if ($action === 'poll') {
        if (!$convId) {
            echo json_encode(['success' => false, 'message' => 'No conversation']);
            exit;
        }

        $sinceId = (int)($_GET['since_id'] ?? 0);

        /* Verify membership + get other user id */
        $stV = $db->prepare(
            "SELECT user1_id, user2_id FROM conversations
             WHERE id = ? AND (user1_id = ? OR user2_id = ?) LIMIT 1"
        );
        $stV->execute([$convId, $userId, $userId]);
        $conv = $stV->fetch();
        if (!$conv) {
            echo json_encode(['success' => false, 'message' => 'Conversation not found']);
            exit;
        }

        $otherId = ((int)$conv['user1_id'] === $userId)
            ? (int)$conv['user2_id']
            : (int)$conv['user1_id'];

        /* Is other user online? (last_seen within 300 seconds) */
        $stOnline = $db->prepare(
            "SELECT last_seen FROM users WHERE id = ? LIMIT 1"
        );
        $stOnline->execute([$otherId]);
        $lastSeen   = $stOnline->fetchColumn();
        $otherOnline = $lastSeen
            && (time() - strtotime($lastSeen)) <= 300;

        /* Is other user typing? */
        $stTyping = $db->prepare("
            SELECT is_typing, last_seen_at
            FROM conversation_status
            WHERE conversation_id = ? AND user_id = ?
            LIMIT 1
        ");
        $stTyping->execute([$convId, $otherId]);
        $typingRow   = $stTyping->fetch();
        /* typing only counts if last_seen_at is within 10 seconds
           (prevents stale typing state if they closed browser) */
        $otherTyping = $typingRow
            && (int)$typingRow['is_typing'] === 1
            && (time() - strtotime($typingRow['last_seen_at'])) <= 10;

        /* New messages since sinceId */
        $newMessages = [];
        if ($sinceId > 0) {
            /* Mark as delivered */
            $db->prepare(
                "UPDATE messages
                 SET is_delivered = 1
                 WHERE conversation_id = ? AND sender_id != ? AND id > ? AND is_delivered = 0"
            )->execute([$convId, $userId, $sinceId]);

            $stNew = $db->prepare("
                SELECT m.id, m.sender_id, m.content, m.message_type,
                       m.file_url, m.file_name, m.file_size,
                       m.is_read, m.is_delivered, m.created_at
                FROM messages m
                WHERE m.conversation_id = ?
                  AND m.id > ?
                  AND m.is_deleted = 0
                ORDER BY m.created_at ASC
                LIMIT 30
            ");
            $stNew->execute([$convId, $sinceId]);
            $newMessages = $stNew->fetchAll(PDO::FETCH_ASSOC);

            /* Mark new incoming messages as read */
            if (!empty($newMessages)) {
                $db->prepare(
                    "UPDATE messages
                     SET is_read = 1
                     WHERE conversation_id = ? AND sender_id != ? AND id > ? AND is_read = 0"
                )->execute([$convId, $userId, $sinceId]);

                /* Reset unread counter on conversation */
                $isU1      = ((int)$conv['user1_id'] === $userId);
                $unreadCol = $isU1 ? 'unread_count_user1' : 'unread_count_user2';
                $db->prepare(
                    "UPDATE conversations SET {$unreadCol} = 0 WHERE id = ?"
                )->execute([$convId]);
            }
        }

        /* Unread count (messages from the other person we haven't read) */
        $stUr = $db->prepare(
            "SELECT COUNT(*) FROM messages
             WHERE conversation_id = ? AND sender_id = ? AND is_read = 0"
        );
        $stUr->execute([$convId, $otherId]);
        $unreadCount = (int)$stUr->fetchColumn();

        /* Format last_seen for display */
        $lastSeenStr = '';
        if ($lastSeen) {
            $diff = time() - strtotime($lastSeen);
            if ($diff < 60)       $lastSeenStr = 'Active now';
            elseif ($diff < 3600) $lastSeenStr = 'Active ' . floor($diff/60) . 'm ago';
            elseif ($diff < 86400)$lastSeenStr = 'Active ' . date('g:i A', strtotime($lastSeen));
            else                   $lastSeenStr = 'Active ' . date('M j', strtotime($lastSeen));
        }

        echo json_encode([
            'success'       => true,
            'other_online'  => $otherOnline,
            'other_typing'  => $otherTyping,
            'last_seen_str' => $lastSeenStr,
            'unread_count'  => $unreadCount,
            'new_messages'  => $newMessages,
        ]);
        exit;
    }

    /* Unknown action */
    echo json_encode(['success' => false, 'message' => 'Unknown action']);

} catch (Exception $e) {
    error_log('presence.php error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error']);
}