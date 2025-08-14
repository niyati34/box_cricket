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
		$password = $_POST['password'] ?? '';
		$hash = password_hash($password, PASSWORD_BCRYPT);
		$stmt = $pdo->prepare('INSERT INTO users (name, email, phone, password_hash, role, is_active) VALUES (?, ?, ?, ?, ?, 1)');
		try {
			$stmt->execute([$name, $email, $phone, $hash, $_POST['role']]);
			flash('success', 'User created');
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
		$stmt = $pdo->prepare('UPDATE users SET role = ? WHERE id = ? AND role != "superadmin"');
		$stmt->execute([$_POST['role'], $id]);
		flash('success', 'Role updated');
	}
	header('Location: users.php'); exit;
}

$users = $pdo->query('SELECT id, name, email, phone, role, is_active, created_at FROM users ORDER BY created_at DESC')->fetchAll();

include __DIR__ . '/../partials/header.php';
?>

	<h2 class="text-2xl md:text-3xl font-semibold mb-4">Manage Users</h2>
	<div class="grid gap-4 grid-cols-1 lg:grid-cols-2">
		<div class="card">
			<h3 class="text-lg font-semibold">Create Admin/User</h3>
			<form method="post" class="space-y-2">
				<input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
				<input type="hidden" name="action" value="create">
				<div class="field"><label>Name</label><input name="name" required></div>
				<div class="field"><label>Email</label><input type="email" name="email" required></div>
				<div class="field"><label>Phone</label><input name="phone"></div>
				<div class="field"><label>Password</label><input type="password" name="password" required></div>
				<div class="field"><label>Role</label>
					<select name="role">
						<option value="user">User</option>
						<option value="admin">Admin</option>
					</select>
				</div>
				<button class="btn">Create</button>
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
						<td><?php echo htmlspecialchars($u['role']); ?></td>
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
								<input type="hidden" name="action" value="promote">
								<input type="hidden" name="id" value="<?php echo (int)$u['id']; ?>">
								<select name="role">
									<option value="user" <?php echo $u['role']==='user'?'selected':''; ?>>User</option>
									<option value="admin" <?php echo $u['role']==='admin'?'selected':''; ?>>Admin</option>
								</select>
								<button class="btn">Update Role</button>
							</form>
							<?php endif; ?>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
