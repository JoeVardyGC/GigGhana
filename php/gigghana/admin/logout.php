<?php
/**
 * GigGhana — admin/logout.php
 */
require_once __DIR__ . '/../config/admin_auth.php';
adminLogout(true);
header('Location: ' . ADMIN_BASE . '/admin/login.php?reason=logout');
exit;