<?php require_once __DIR__ . '/config.php';
require_login();

$pdo = get_pdo();
$user = current_user();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate current password
    $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = ?');
    $stmt->execute([$user['id']]);
    $user_data = $stmt->fetch();
    
    if (!password_verify($current_password, $user_data['password_hash'])) {
        $error = 'Current password is incorrect';
    } elseif (strlen($new_password) < 6) {
        $error = 'New password must be at least 6 characters long';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New passwords do not match';
    } else {
        // Update password
        $new_hash = password_hash($new_password, PASSWORD_BCRYPT);
        $update_stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        $update_stmt->execute([$new_hash, $user['id']]);
        
        $message = 'Password changed successfully';
    }
}

include __DIR__ . '/partials/header.php';
?>

<h2 class="text-2xl md:text-3xl font-semibold mb-4">Change Password</h2>

<?php if ($message): ?>
    <div class="alert success mb-4"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert error mb-4"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="card max-w-md">
    <form method="post" class="space-y-4">
        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
        
        <div class="field">
            <label for="current_password">Current Password</label>
            <input type="password" id="current_password" name="current_password" required>
        </div>
        
        <div class="field">
            <label for="new_password">New Password</label>
            <input type="password" id="new_password" name="new_password" required>
            <small class="text-slate-600">Must be at least 6 characters long</small>
        </div>
        
        <div class="field">
            <label for="confirm_password">Confirm New Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>
        
        <button type="submit" class="btn w-full">Change Password</button>
    </form>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
