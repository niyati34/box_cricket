<?php
// Basic configuration for database and app settings

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'box_cricket');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME', 'Box Cricket Booking');
define('BASE_URL', '/box_cricket'); // Adjust if app is deployed in subfolder

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// PDO connection
function get_pdo() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    }
    return $pdo;
}

function is_logged_in() {
    return isset($_SESSION['user']);
}

function current_user() {
    return $_SESSION['user'] ?? null;
}

function require_role($roles) {
    if (!is_logged_in()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
    $user = current_user();
    $roles = is_array($roles) ? $roles : [$roles];
    if (!in_array($user['role'], $roles, true)) {
        http_response_code(403);
        echo 'Forbidden';
        exit;
    }
}

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(400);
            echo 'Invalid CSRF token';
            exit;
        }
    }
}

function flash($key, $message = null) {
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return;
    }
    $msg = $_SESSION['flash'][$key] ?? null;
    if ($msg !== null) {
        unset($_SESSION['flash'][$key]);
    }
    return $msg;
}

