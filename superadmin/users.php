<?php require_once __DIR__ . '/../config.php';
require_role('superadmin');
$pdo = get_pdo();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	verify_csrf();
	$action = $_POST['action'] ?? '';
	if ($action === 'create') {
		$name = trim($_POST['name'] ?? '');
		$email = trim($_POST['email'] ?? '');
		$phone = trim($_POST['phone'] ?? '');
		$role = $_POST['role'] ?? 'user';
		
		// For admins, auto-generate password; for users, use provided password
		if ($role === 'admin') {
			// Generate a secure random password
			$password = generateSecurePassword();
			$auto_generated = true;
		} else {
			$password = $_POST['password'] ?? '';
			$auto_generated = false;
		}
		
		if (empty($password)) {
			flash('error', 'Password is required');
			header('Location: users.php'); 
			exit;
		}
		
		$hash = password_hash($password, PASSWORD_BCRYPT);
		$stmt = $pdo->prepare('INSERT INTO users (name, email, phone, password_hash, role, is_active) VALUES (?, ?, ?, ?, ?, 1)');
		try {
			$stmt->execute([$name, $email, $phone, $hash, $role]);
			$user_id = $pdo->lastInsertId();
			
			// Send email to admin with auto-generated password
			if ($role === 'admin' && $auto_generated) {
				require_once __DIR__ . '/../lib/Mailer.php';
				$subject = 'Admin Account Created - ' . APP_NAME;
				$body = "
				<p>Hello {$name},</p>
				<p>Your admin account has been created successfully.</p>
				<p><strong>Account Details:</strong></p>
				<ul>
				<li>Email: {$email}</li>
				<li>Role: Admin</li>
				<li>Password: <strong style='font-family:monospace;background:#f3f4f6;padding:4px 8px;border-radius:4px;'>{$password}</strong></li>
				</ul>
				<p><strong>Important:</strong> Please change your password after your first login for security.</p>
				<p>You can login at: " . BASE_URL . "/login.php</p>
				<p>Regards,<br>" . APP_NAME . " Team</p>
				";
				
				$email_sent = Mailer::send($email, $subject, $body);
				if ($email_sent) {
					flash('success', 'Admin created successfully. Password has been sent to their email.');
				} else {
					flash('success', 'Admin created successfully, but email delivery failed. Password: ' . $password);
				}
			} else {
				flash('success', 'User created successfully');
			}
		} catch (PDOException $e) {
			flash('error', 'Email already exists');
		}
	}
	if ($action === 'toggle') {
		$id = (int)($_POST['id'] ?? 0);
		$stmt = $pdo->prepare('UPDATE users SET is_active = NOT is_active WHERE id = ? AND role != "superadmin"');
		$stmt->execute([$id]);
		flash('success', 'User updated');
	}
	if ($action === 'promote') {
		$id = (int)($_POST['id'] ?? 0);
		$new_role = $_POST['role'];
		
		// If promoting to admin, generate new password and send email
		if ($new_role === 'admin') {
			$userStmt = $pdo->prepare('SELECT name, email FROM users WHERE id = ?');
			$userStmt->execute([$id]);
			$user = $userStmt->fetch();
			
			if ($user) {
				$password = generateSecurePassword();
				$hash = password_hash($password, PASSWORD_BCRYPT);
				
				$stmt = $pdo->prepare('UPDATE users SET role = ?, password_hash = ? WHERE id = ? AND role != "superadmin"');
				$stmt->execute([$new_role, $hash, $id]);
				
				// Send email with new password
				require_once __DIR__ . '/../lib/Mailer.php';
				$subject = 'Account Promoted to Admin - ' . APP_NAME;
				$body = "
				<p>Hello {$user['name']},</p>
				<p>Your account has been promoted to Admin role.</p>
				<p><strong>New Account Details:</strong></p>
				<ul>
				<li>Email: {$user['email']}</li>
				<li>Role: Admin</li>
				<li>New Password: <strong style='font-family:monospace;background:#f3f4f6;padding:4px 8px;border-radius:4px;'>{$password}</strong></li>
				</ul>
				<p><strong>Important:</strong> Please change your password after your next login for security.</p>
				<p>You can login at: " . BASE_URL . "/login.php</p>
				<p>Regards,<br>" . APP_NAME . " Team</p>
				";
				
				$email_sent = Mailer::send($user['email'], $subject, $body);
				if ($email_sent) {
					flash('success', 'User promoted to admin. New password has been sent to their email.');
				} else {
					flash('success', 'User promoted to admin, but email delivery failed. New password: ' . $password);
				}
			}
		} else {
			$stmt = $pdo->prepare('UPDATE users SET role = ? WHERE id = ? AND role != "superadmin"');
			$stmt->execute([$new_role, $id]);
			flash('success', 'Role updated');
		}
	}
	if ($action === 'reset_password') {
		$id = (int)($_POST['id'] ?? 0);
		
		$userStmt = $pdo->prepare('SELECT name, email, role FROM users WHERE id = ? AND role != "superadmin"');
		$userStmt->execute([$id]);
		$user = $userStmt->fetch();
		
		if ($user) {
			$password = generateSecurePassword();
			$hash = password_hash($password, PASSWORD_BCRYPT);
			
			$stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
			$stmt->execute([$hash, $id]);
			
			// Send email with new password
			require_once __DIR__ . '/../lib/Mailer.php';
			$subject = 'Password Reset - ' . APP_NAME;
			$body = "
			<p>Hello {$user['name']},</p>
			<p>Your password has been reset by the administrator.</p>
			<p><strong>New Login Details:</strong></p>
			<ul>
			<li>Email: {$user['email']}</li>
			<li>Role: " . ucfirst($user['role']) . "</li>
			<li>New Password: <strong style='font-family:monospace;background:#f3f4f6;padding:4px 8px;border-radius:4px;'>{$password}</strong></li>
			</ul>
			<p><strong>Important:</strong> Please change your password after your next login for security.</p>
			<p>You can login at: " . BASE_URL . "/login.php</p>
			<p>Regards,<br>" . APP_NAME . " Team</p>
			";
			
			$email_sent = Mailer::send($user['email'], $subject, $body);
			if ($email_sent) {
				flash('success', 'Password reset successfully. New password has been sent to their email.');
			} else {
				flash('success', 'Password reset successfully, but email delivery failed. New password: ' . $password);
			}
		} else {
			flash('error', 'User not found');
		}
	}
	header('Location: users.php'); exit;
}

// Function to generate secure password
function generateSecurePassword($length = 12) {
	$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
	$password = '';
	for ($i = 0; $i < $length; $i++) {
		$password .= $chars[random_int(0, strlen($chars) - 1)];
	}
	return $password;
}

$users = $pdo->query('SELECT id, name, email, phone, role, is_active, created_at FROM users ORDER BY created_at DESC')->fetchAll();

include __DIR__ . '/../partials/header.php';
?>

	<h2 class="text-2xl md:text-3xl font-semibold mb-4">Manage Users</h2>
	<div class="grid gap-4 grid-cols-1 lg:grid-cols-2">
		<div class="card">
			<h3 class="text-lg font-semibold">Create Admin/User</h3>
			<form method="post" class="space-y-2" id="createUserForm">
				<input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
				<input type="hidden" name="action" value="create">
				<div class="field"><label>Name</label><input name="name" required></div>
				<div class="field"><label>Email</label><input type="email" name="email" required></div>
				<div class="field"><label>Phone</label><input name="phone"></div>
				<div class="field"><label>Role</label>
					<select name="role" id="roleSelect" onchange="togglePasswordField()">
						<option value="user">User</option>
						<option value="admin">Admin</option>
					</select>
				</div>
				<div class="field" id="passwordField">
					<label>Password</label>
					<input type="password" name="password" id="passwordInput">
					<small class="text-slate-600">Required for regular users</small>
				</div>
				<div class="field" id="adminNote" style="display:none;">
					<div class="bg-blue-50 border border-blue-200 rounded p-3">
						<p class="text-blue-800 text-sm">
							<strong>Note:</strong> For admin accounts, a secure password will be auto-generated and sent to the admin's email address.
						</p>
					</div>
				</div>
				<button class="btn" type="submit">Create</button>
			</form>
		</div>
		<div class="card overflow-x-auto">
			<h3 class="text-lg font-semibold">All Users</h3>
			<table class="min-w-full">
				<thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Active</th><th>Actions</th></tr></thead>
				<tbody>
					<?php foreach ($users as $u): ?>
					<tr>
						<td class="font-medium"><?php echo htmlspecialchars($u['name']); ?></td>
						<td><?php echo htmlspecialchars($u['email']); ?></td>
						<td><?php echo htmlspecialchars($u['phone']); ?></td>
						<td>
							<span class="badge <?php echo $u['role'] === 'admin' ? 'success' : ($u['role'] === 'superadmin' ? 'error' : 'pending'); ?>">
								<?php echo htmlspecialchars($u['role']); ?>
							</span>
						</td>
						<td><?php echo $u['is_active'] ? 'Yes' : 'No'; ?></td>
						<td>
							<?php if ($u['role'] !== 'superadmin'): ?>
							<form method="post" style="display:inline">
								<input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
								<input type="hidden" name="action" value="toggle">
								<input type="hidden" name="id" value="<?php echo (int)$u['id']; ?>">
								<button class="btn secondary">Toggle Active</button>
							</form>
							<form method="post" style="display:inline">
								<input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
								<input type="hidden" name="id" value="<?php echo (int)$u['id']; ?>">
								<input type="hidden" name="action" value="promote">
								<select name="role">
									<option value="user" <?php echo $u['role']==='user'?'selected':''; ?>>User</option>
									<option value="admin" <?php echo $u['role']==='admin'?'selected':''; ?>>Admin</option>
								</select>
								<button class="btn">Update Role</button>
							</form>
							<form method="post" style="display:inline">
								<input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
								<input type="hidden" name="action" value="reset_password">
								<input type="hidden" name="id" value="<?php echo (int)$u['id']; ?>">
								<button class="btn secondary" onclick="return confirm('Are you sure you want to reset the password for <?php echo htmlspecialchars($u['name']); ?>? A new password will be sent to their email.')">Reset Password</button>
							</form>
							<?php endif; ?>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>

<script>
function togglePasswordField() {
	const roleSelect = document.getElementById('roleSelect');
	const passwordField = document.getElementById('passwordField');
	const passwordInput = document.getElementById('passwordInput');
	const adminNote = document.getElementById('adminNote');
	
	if (roleSelect.value === 'admin') {
		passwordField.style.display = 'none';
		adminNote.style.display = 'block';
		passwordInput.removeAttribute('required');
	} else {
		passwordField.style.display = 'block';
		adminNote.style.display = 'none';
		passwordInput.setAttribute('required', 'required');
	}
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
	togglePasswordField();
});
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
