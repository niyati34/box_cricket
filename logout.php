<?php require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/Auth.php';
AuthService::logout();
flash('success', 'Logged out successfully');
header('Location: ' . BASE_URL . '/index.php');
exit;

