<?php require_once __DIR__ . '/../config.php';
require_role('superadmin');
$pdo = get_pdo();

// Create / Update / Delete grounds with admin assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	verify_csrf();
	$action = $_POST['action'] ?? '';
	if ($action === 'create') {
		$adminId = (int)($_POST['admin_id'] ?? 0);
		if ($adminId > 0) {
			$admin = $pdo->prepare('SELECT id FROM users WHERE id = ? AND role = "admin" AND is_active = 1');
			$admin->execute([$adminId]);
			if (!$admin->fetch()) { $adminId = null; }
		} else { $adminId = null; }
		$stmt = $pdo->prepare('INSERT INTO grounds (name, location, description, price_per_hour, admin_id, maps_link, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)');
		$stmt->execute([
			trim($_POST['name'] ?? ''),
			trim($_POST['location'] ?? ''),
			trim($_POST['description'] ?? ''),
			(float)($_POST['price_per_hour'] ?? 0),
			$adminId,
			trim($_POST['maps_link'] ?? ''),
			isset($_POST['is_active']) ? 1 : 0
		]);
		flash('success', 'Ground created');
		header('Location: grounds.php'); exit;
	}
	if ($action === 'update') {
		$id = (int)($_POST['id'] ?? 0);
		$adminId = (int)($_POST['admin_id'] ?? 0);
		if ($adminId > 0) {
			$admin = $pdo->prepare('SELECT id FROM users WHERE id = ? AND role = "admin" AND is_active = 1');
			$admin->execute([$adminId]);
			if (!$admin->fetch()) { $adminId = null; }
		} else { $adminId = null; }
		$stmt = $pdo->prepare('UPDATE grounds SET name=?, location=?, description=?, price_per_hour=?, admin_id=?, maps_link=?, is_active=? WHERE id=?');
		$stmt->execute([
			trim($_POST['name'] ?? ''),
			trim($_POST['location'] ?? ''),
			trim($_POST['description'] ?? ''),
			(float)($_POST['price_per_hour'] ?? 0),
			$adminId,
			trim($_POST['maps_link'] ?? ''),
			isset($_POST['is_active']) ? 1 : 0,
			$id
		]);
		flash('success', 'Ground updated');
		header('Location: grounds.php'); exit;
	}
	if ($action === 'delete') {
		$id = (int)($_POST['id'] ?? 0);
		$stmt = $pdo->prepare('DELETE FROM grounds WHERE id=?');
		$stmt->execute([$id]);
		flash('success', 'Ground deleted');
		header('Location: grounds.php'); exit;
	}
}

$admins = $pdo->query('SELECT id, name FROM users WHERE role = "admin" AND is_active = 1 ORDER BY name')->fetchAll();
$grounds = $pdo->query('SELECT g.*, u.name AS admin_name FROM grounds g LEFT JOIN users u ON g.admin_id = u.id ORDER BY g.created_at DESC')->fetchAll();

include __DIR__ . '/../partials/header.php';
?>

	<h2>Super Admin - Manage Grounds</h2>
	<div class="grid">
		<div class="card">
			<h3>Add Ground</h3>
			<form method="post">
				<input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
				<input type="hidden" name="action" value="create">
				<div class="field"><label>Name</label><input name="name" required></div>
				<div class="field"><label>Location</label><input name="location"></div>
				<div class="field"><label>Description</label><textarea name="description"></textarea></div>
				<div class="field"><label>Price per hour</label><input type="number" step="0.01" name="price_per_hour" required></div>
				<div class="field"><label>Maps Link</label><input type="url" name="maps_link"></div>
				<div class="field"><label>Assign Admin</label>
					<select name="admin_id">
						<option value="">-- None --</option>
						<?php foreach ($admins as $a): ?>
							<option value="<?php echo (int)$a['id']; ?>"><?php echo htmlspecialchars($a['name']); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="field"><label><input type="checkbox" name="is_active" checked> Active</label></div>
				<button class="btn" type="submit">Create</button>
			</form>
		</div>
		<div class="card">
			<h3>All Grounds</h3>
			<table>
				<thead><tr><th>Name</th><th>Location</th><th>Admin</th><th>Price</th><th>Active</th><th>Maps</th><th>Actions</th></tr></thead>
				<tbody>
					<?php foreach ($grounds as $g): ?>
					<tr>
						<td><?php echo htmlspecialchars($g['name']); ?></td>
						<td><?php echo htmlspecialchars($g['location']); ?></td>
						<td><?php echo htmlspecialchars($g['admin_name'] ?? ''); ?></td>
						<td>₹<?php echo number_format($g['price_per_hour'], 2); ?></td>
						<td><?php echo $g['is_active'] ? 'Yes' : 'No'; ?></td>
						<td>
							<?php if (!empty($g['maps_link'])): ?>
								<a href="<?php echo htmlspecialchars($g['maps_link']); ?>" target="_blank" class="btn btn-sm secondary">
									🗺️ View Map
								</a>
							<?php else: ?>
								<span class="text-slate-400 text-sm">No map</span>
							<?php endif; ?>
						</td>
						<td>
							<form method="post" style="display:inline">
								<input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
								<input type="hidden" name="action" value="delete">
								<input type="hidden" name="id" value="<?php echo (int)$g['id']; ?>">
								<button class="btn danger" onclick="return confirm('Delete this ground?')">Delete</button>
							</form>
							<button class="btn secondary" onclick="document.getElementById('edit-<?php echo (int)$g['id']; ?>').style.display='block'">Edit</button>
						</td>
					</tr>
					<tr id="edit-<?php echo (int)$g['id']; ?>" style="display:none">
						<td colspan="7">
							<form method="post">
								<input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
								<input type="hidden" name="action" value="update">
								<input type="hidden" name="id" value="<?php echo (int)$g['id']; ?>">
								<div class="grid">
									<div class="field"><label>Name</label><input name="name" value="<?php echo htmlspecialchars($g['name']); ?>" required></div>
									<div class="field"><label>Location</label><input name="location" value="<?php echo htmlspecialchars($g['location']); ?>"></div>
									<div class="field"><label>Price per hour</label><input type="number" step="0.01" name="price_per_hour" value="<?php echo htmlspecialchars($g['price_per_hour']); ?>" required></div>
									<div class="field"><label>Description</label><textarea name="description"><?php echo htmlspecialchars($g['description']); ?></textarea></div>
									<div class="field"><label>Maps Link</label><input type="url" name="maps_link" value="<?php echo htmlspecialchars($g['maps_link']); ?>"></div>
									<div class="field"><label>Assign Admin</label>
										<select name="admin_id">
											<option value="">-- None --</option>
											<?php foreach ($admins as $a): ?>
												<option value="<?php echo (int)$a['id']; ?>" <?php echo ((int)$g['admin_id'] === (int)$a['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($a['name']); ?></option>
											<?php endforeach; ?>
										</select>
									</div>
									<div class="field"><label><input type="checkbox" name="is_active" <?php echo $g['is_active'] ? 'checked' : ''; ?>> Active</label></div>
								</div>
								<button class="btn" type="submit">Save</button>
							</form>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>

