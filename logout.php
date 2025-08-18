<?php require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/Auth.php';
AuthService::logout();
// Prevent browser caching after logout
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Expires: 0');
header('Pragma: no-cache');
flash('success', 'Logged out successfully');
header('Location: ' . BASE_URL . '/login.php');
exit;

