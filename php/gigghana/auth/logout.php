<?php
require_once __DIR__ . '/../config/config.php';
session_destroy();
setcookie('gg_remember', '', time()-3600, '/');
header("Location: " . APP_URL . "/index.php?success=Logged+out+successfully");
exit;