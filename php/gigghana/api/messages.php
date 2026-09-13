<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');
requireLogin();

$userId = $_SESSION['user_id'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    $db = getDB();

    if ($action === 'send') {
        if (!verifyCSRF($_POST['csrf'] ?? '')) { echo json_encode(['success'=>false,'error'=>'Invalid token']); exit; }
        $convId  = (int)($_POST['conversation_id'] ?? 0);
        $content = trim($_POST['content'] ?? '');

        if (!$convId || !$content) { echo json_encode(['success'=>false,'error'=>'Missing data']); exit; }

        // Verify user is part of this conversation
        $stVerify = $db->prepare("SELECT * FROM conversations WHERE id=? AND (user1_id=? OR user2_id=?)");
        $stVerify->execute([$convId, $userId, $userId]);
        if (!$stVerify->fetch()) { echo json_encode(['success'=>false,'error'=>'Unauthorized']); exit; }

        $stmt = $db->prepare("INSERT INTO messages (conversation_id, sender_id, content) VALUES (?,?,?)");
        $stmt->execute([$convId, $userId, htmlspecialchars($content, ENT_QUOTES)]);
        $msgId = $db->lastInsertId();

        $db->prepare("UPDATE conversations SET last_message_at=NOW() WHERE id=?")->execute([$convId]);

        echo json_encode(['success'=>true, 'message_id'=>$msgId]);
        exit;
    }

    if ($action === 'poll') {
        $convId   = (int)($_GET['conversation_id'] ?? 0);
        $lastId   = (int)($_GET['last_id']          ?? 0);

        if (!$convId) { echo json_encode(['messages'=>[]]); exit; }

        // Verify
        $stVerify = $db->prepare("SELECT id FROM conversations WHERE id=? AND (user1_id=? OR user2_id=?)");
        $stVerify->execute([$convId, $userId, $userId]);
        if (!$stVerify->fetch()) { echo json_encode(['messages'=>[]]); exit; }

        $stMsgs = $db->prepare(
            "SELECT m.id, m.content, m.sender_id, m.created_at,
             (m.sender_id = ?) AS is_mine
             FROM messages m
             WHERE m.conversation_id=? AND m.id>? AND m.sender_id!=?
             ORDER BY m.created_at ASC"
        );
        $stMsgs->execute([$userId, $convId, $lastId, $userId]);
        $msgs = $stMsgs->fetchAll();

        // Mark as read
        $db->prepare("UPDATE messages SET is_read=1 WHERE conversation_id=? AND sender_id!=? AND is_read=0")->execute([$convId, $userId]);

        echo json_encode(['messages' => $msgs]);
        exit;
    }

    if ($action === 'start') {
        // Start or get conversation with another user
        if (!verifyCSRF($_POST['csrf'] ?? '')) { echo json_encode(['success'=>false,'error'=>'Invalid token']); exit; }
        $otherId = (int)($_POST['user_id'] ?? 0);
        $jobId   = (int)($_POST['job_id'] ?? 0);

        if (!$otherId || $otherId === $userId) { echo json_encode(['success'=>false,'error'=>'Invalid user']); exit; }

        // Check existing
        $u1 = min($userId, $otherId);
        $u2 = max($userId, $otherId);
        $stmt = $db->prepare("SELECT id FROM conversations WHERE user1_id=? AND user2_id=? LIMIT 1");
        $stmt->execute([$u1, $u2]);
        $existing = $stmt->fetch();

        if ($existing) {
            echo json_encode(['success'=>true, 'conversation_id'=>$existing['id']]);
        } else {
            $uuid = generateUUID();
            $ins = $db->prepare("INSERT INTO conversations (uuid, user1_id, user2_id, job_id) VALUES (?,?,?,?)");
            $ins->execute([$uuid, $u1, $u2, $jobId ?: null]);
            echo json_encode(['success'=>true, 'conversation_id'=>$db->lastInsertId()]);
        }
        exit;
    }

    if ($action === 'unread_count') {
        $stmt = $db->prepare(
            "SELECT COUNT(*) FROM messages m
             JOIN conversations c ON c.id=m.conversation_id
             WHERE (c.user1_id=? OR c.user2_id=?) AND m.sender_id!=? AND m.is_read=0"
        );
        $stmt->execute([$userId, $userId, $userId]);
        echo json_encode(['count' => (int)$stmt->fetchColumn()]);
        exit;
    }

    echo json_encode(['error' => 'Unknown action']);

} catch(Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['success'=>false, 'error'=>'Server error']);
}