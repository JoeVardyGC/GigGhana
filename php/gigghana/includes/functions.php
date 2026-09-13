<?php
require_once __DIR__ . '/../config/config.php';

// Sanitize input
function clean($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Get current user
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// Redirect function
function redirect($url) {
    header("Location: " . SITE_URL . "/" . $url);
    exit();
}

// Check if user is client
function isClient() {
    $user = getCurrentUser();
    return $user && $user['user_type'] === 'client';
}

// Check if user is provider
function isProvider() {
    $user = getCurrentUser();
    return $user && $user['user_type'] === 'provider';
}

// Check if user is admin
function isAdmin() {
    $user = getCurrentUser();
    return $user && $user['user_type'] === 'admin';
}

// Flash message
function setFlash($type, $message) {
    $_SESSION['flash_type'] = $type; // success, error, warning, info
    $_SESSION['flash_message'] = $message;
}

function getFlash() {
    if (isset($_SESSION['flash_message'])) {
        $flash = [
            'type' => $_SESSION['flash_type'],
            'message' => $_SESSION['flash_message']
        ];
        unset($_SESSION['flash_type']);
        unset($_SESSION['flash_message']);
        return $flash;
    }
    return null;
}

// Upload file
function uploadFile($file, $type = 'image') {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'File upload failed'];
    }
    
    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'File too large (max 10MB)'];
    }
    
    // Check file type
    $allowedTypes = ($type === 'image') ? ALLOWED_IMAGE_TYPES : ALLOWED_DOC_TYPES;
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    
    // Determine upload directory
    $uploadDir = ($type === 'image') ? PROFILE_DIR : ATTACHMENT_DIR;
    $filepath = $uploadDir . $filename;
    
    // Move file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename];
    }
    
    return ['success' => false, 'message' => 'Failed to save file'];
}

// Format money
function formatMoney($amount) {
    return 'GHS ' . number_format($amount, 2);
}

// Time ago function
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $difference = time() - $timestamp;
    
    if ($difference < 60) {
        return $difference . ' seconds ago';
    } elseif ($difference < 3600) {
        return floor($difference / 60) . ' minutes ago';
    } elseif ($difference < 86400) {
        return floor($difference / 3600) . ' hours ago';
    } elseif ($difference < 604800) {
        return floor($difference / 86400) . ' days ago';
    } elseif ($difference < 2592000) {
        return floor($difference / 604800) . ' weeks ago';
    } elseif ($difference < 31536000) {
        return floor($difference / 2592000) . ' months ago';
    } else {
        return floor($difference / 31536000) . ' years ago';
    }
}

// Generate star rating HTML
function starRating($rating, $totalReviews = 0) {
    $fullStars = floor($rating);
    $halfStar = ($rating - $fullStars) >= 0.5 ? 1 : 0;
    $emptyStars = 5 - $fullStars - $halfStar;
    
    $html = '<div class="star-rating">';
    
    // Full stars
    for ($i = 0; $i < $fullStars; $i++) {
        $html .= '<span class="star full">⭐</span>';
    }
    
    // Half star
    if ($halfStar) {
        $html .= '<span class="star half">⭐</span>';
    }
    
    // Empty stars
    for ($i = 0; $i < $emptyStars; $i++) {
        $html .= '<span class="star empty">☆</span>';
    }
    
    $html .= ' <span class="rating-text">' . number_format($rating, 1) . '</span>';
    
    if ($totalReviews > 0) {
        $html .= ' <span class="review-count">(' . $totalReviews . ')</span>';
    }
    
    $html .= '</div>';
    
    return $html;
}

// Send notification
function sendNotification($userId, $type, $title, $message, $link = null) {
    $db = getDB();
    $stmt = $db->prepare("
        INSERT INTO notifications (user_id, type, title, message, link)
        VALUES (?, ?, ?, ?, ?)
    ");
    return $stmt->execute([$userId, $type, $title, $message, $link]);
}

// Calculate platform commission
function calculateCommission($amount) {
    return ($amount * PLATFORM_COMMISSION) / 100;
}

// Verify password strength
function isStrongPassword($password) {
    // At least 8 characters, 1 uppercase, 1 lowercase, 1 number
    return strlen($password) >= 8 
        && preg_match('/[A-Z]/', $password)
        && preg_match('/[a-z]/', $password)
        && preg_match('/[0-9]/', $password);
}

// Generate random string
function generateRandomString($length = 10) {
    return bin2hex(random_bytes($length / 2));
}

// Validate Ghana phone number
function isValidGhanaPhone($phone) {
    // Remove spaces and special characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Should be 10 digits starting with 0, or 12 digits starting with 233
    return (strlen($phone) == 10 && $phone[0] == '0') 
        || (strlen($phone) == 12 && substr($phone, 0, 3) == '233');
}

// Format Ghana phone number
function formatGhanaPhone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    if (strlen($phone) == 10 && $phone[0] == '0') {
        return '+233' . substr($phone, 1);
    } elseif (strlen($phone) == 12 && substr($phone, 0, 3) == '233') {
        return '+' . $phone;
    }
    
    return $phone;
}
?>