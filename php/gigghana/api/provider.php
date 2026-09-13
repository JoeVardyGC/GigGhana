<?php
/**
 * GigGhana — api/providers.php
 * JSON API for provider-related actions.
 * Location: gigghana/api/providers.php
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');
requireLogin();

$userId = $_SESSION['user_id'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    $db = getDB();

    // ── Toggle save provider ──────────────────────────────────────────────
    if ($action === 'toggle_save') {
        $providerId = (int)($_POST['provider_id'] ?? 0);
        if (!$providerId) { echo json_encode(['error' => 'Invalid provider']); exit; }

        $stCheck = $db->prepare("SELECT id FROM saved_providers WHERE user_id=? AND provider_id=?");
        $stCheck->execute([$userId, $providerId]);

        if ($stCheck->fetch()) {
            $db->prepare("DELETE FROM saved_providers WHERE user_id=? AND provider_id=?")->execute([$userId, $providerId]);
            echo json_encode(['success' => true, 'saved' => false]);
        } else {
            $db->prepare("INSERT IGNORE INTO saved_providers (user_id, provider_id) VALUES (?,?)")->execute([$userId, $providerId]);
            echo json_encode(['success' => true, 'saved' => true]);
        }
        exit;
    }

    // ── Get saved providers list ──────────────────────────────────────────
    if ($action === 'saved_list') {
        $stmt = $db->prepare(
            "SELECT u.first_name, u.last_name, u.avatar, u.location, u.id AS user_id,
             p.id AS provider_id, p.tagline, p.hourly_rate, p.rating_avg, p.rating_count,
             p.is_verified, sp.created_at AS saved_at
             FROM saved_providers sp
             JOIN providers p ON p.id = sp.provider_id
             JOIN users u ON u.id = p.user_id
             WHERE sp.user_id = ?
             ORDER BY sp.created_at DESC"
        );
        $stmt->execute([$userId]);
        echo json_encode(['providers' => $stmt->fetchAll()]);
        exit;
    }

    // ── Get provider quick stats (for widgets) ────────────────────────────
    if ($action === 'quick_info') {
        $provUserId = (int)($_GET['user_id'] ?? 0);
        if (!$provUserId) { echo json_encode(['error' => 'Invalid user']); exit; }

        $stmt = $db->prepare(
            "SELECT u.first_name, u.last_name, u.avatar, u.location,
             p.tagline, p.hourly_rate, p.rating_avg, p.rating_count,
             p.completed_jobs, p.availability, p.is_verified
             FROM users u JOIN providers p ON p.user_id = u.id
             WHERE u.id = ? AND u.is_active = 1 LIMIT 1"
        );
        $stmt->execute([$provUserId]);
        $data = $stmt->fetch();
        if ($data) {
            $data['rate_formatted'] = formatCurrency((float)$data['hourly_rate']) . '/hr';
            echo json_encode(['provider' => $data]);
        } else {
            echo json_encode(['error' => 'Provider not found']);
        }
        exit;
    }

    echo json_encode(['error' => 'Unknown action']);

} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['error' => 'Server error']);
}