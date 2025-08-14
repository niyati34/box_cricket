<?php
require_once __DIR__ . '/../config.php';

class AuthService {
    public static function login($email, $password) {
        $pdo = get_pdo();
        $stmt = $pdo->prepare('SELECT id, name, email, phone, password_hash, role, is_active FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if (!$user || !$user['is_active']) {
            return [false, 'Invalid credentials'];
        }
        if (!password_verify($password, $user['password_hash'])) {
            return [false, 'Invalid credentials'];
        }
        unset($user['password_hash']);
        $_SESSION['user'] = $user;
        return [true, $user];
    }

    public static function logout() {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    public static function register($name, $email, $phone, $password) {
        $pdo = get_pdo();
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare('INSERT INTO users (name, email, phone, password_hash, role) VALUES (?, ?, ?, ?, "user")');
        try {
            $stmt->execute([$name, $email, $phone, $hash]);
            return [true, $pdo->lastInsertId()];
        } catch (PDOException $e) {
            if ((int)$e->getCode() === 23000) {
                return [false, 'Email already registered'];
            }
            return [false, 'Registration failed'];
        }
    }
}

