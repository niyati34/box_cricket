<?php require_once __DIR__ . '/../config.php';
require_role(['admin','superadmin']);
$pdo = get_pdo();
$me = current_user();

// Handle create/update ground
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	verify_csrf();
	$action = $_POST['action'] ?? '';
	// Ensure upload dir exists
	$uploadDir = __DIR__ . '/../uploads/grounds/';
	if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0777, true); }

	function handle_image_upload($fieldName, $uploadDir) {
		if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) { return null; }
		$allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
		$f = $_FILES[$fieldName];
		$type = mime_content_type($f['tmp_name']);
		if (!isset($allowed[$type])) { return null; }
		$ext = $allowed[$type];
		$base = bin2hex(random_bytes(8)) . '.' . $ext;
		$dest = $uploadDir . $base;
		if (move_uploaded_file($f['tmp_name'], $dest)) {
			return 'uploads/grounds/' . $base;
		}
		return null;
	}

	if ($action === 'create') {
		$imagePath = handle_image_upload('image', $uploadDir);
		$stmt = $pdo->prepare('INSERT INTO grounds (name, location, description, price_per_hour, is_active, image_path, maps_link, admin_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
		$stmt->execute([
			trim($_POST['name'] ?? ''),
			trim($_POST['location'] ?? ''),
			trim($_POST['description'] ?? ''),
			(float)($_POST['price_per_hour'] ?? 0),
			isset($_POST['is_active']) ? 1 : 0,
			$imagePath,
			trim($_POST['maps_link'] ?? ''),
			$me['role'] === 'superadmin' ? null : $me['id'] // Only set admin_id for regular admins
		]);
		flash('success', 'Ground created');
		header('Location: grounds.php'); exit;
	}
	if ($action === 'update') {
		$id = (int)($_POST['id'] ?? 0);
		
		// Check if admin has permission to edit this ground
		if ($me['role'] !== 'superadmin') {
			$checkStmt = $pdo->prepare('SELECT admin_id FROM grounds WHERE id = ?');
			$checkStmt->execute([$id]);
			$ground = $checkStmt->fetch();
			if (!$ground || $ground['admin_id'] != $me['id']) {
				flash('error', 'You do not have permission to edit this ground');
				header('Location: grounds.php'); exit;
			}
		}
		
		$newImage = handle_image_upload('image', $uploadDir); // optional
		if ($newImage) {
			$stmt = $pdo->prepare('UPDATE grounds SET name=?, location=?, description=?, price_per_hour=?, is_active=?, image_path=?, maps_link=? WHERE id=?');
			$stmt->execute([
				trim($_POST['name'] ?? ''),
				trim($_POST['location'] ?? ''),
				trim($_POST['description'] ?? ''),
				(float)($_POST['price_per_hour'] ?? 0),
				isset($_POST['is_active']) ? 1 : 0,
				$newImage,
				trim($_POST['maps_link'] ?? ''),
				$id
			]);
		} else {
			$stmt = $pdo->prepare('UPDATE grounds SET name=?, location=?, description=?, price_per_hour=?, is_active=?, maps_link=? WHERE id=?');
			$stmt->execute([
				trim($_POST['name'] ?? ''),
				trim($_POST['location'] ?? ''),
				trim($_POST['description'] ?? ''),
				(float)($_POST['price_per_hour'] ?? 0),
				isset($_POST['is_active']) ? 1 : 0,
				trim($_POST['maps_link'] ?? ''),
				$id
			]);
		}
		flash('success', 'Ground updated');
		header('Location: grounds.php'); exit;
	}
	if ($action === 'delete') {
		$id = (int)($_POST['id'] ?? 0);
		
		// Check if admin has permission to delete this ground
		if ($me['role'] !== 'superadmin') {
			$checkStmt = $pdo->prepare('SELECT admin_id FROM grounds WHERE id = ?');
			$checkStmt->execute([$id]);
			$ground = $checkStmt->fetch();
			if (!$ground || $ground['admin_id'] != $me['id']) {
				flash('error', 'You do not have permission to delete this ground');
				header('Location: grounds.php'); exit;
			}
		}
		
		$stmt = $pdo->prepare('DELETE FROM grounds WHERE id=?');
		$stmt->execute([$id]);
		flash('success', 'Ground deleted');
		header('Location: grounds.php'); exit;
	}
}

// Fetch grounds - restrict to admin's grounds unless superadmin
if ($me['role'] === 'superadmin') {
	$grounds = $pdo->query('SELECT g.*, u.name AS admin_name FROM grounds g LEFT JOIN users u ON g.admin_id = u.id ORDER BY g.created_at DESC')->fetchAll();
} else {
	$stmt = $pdo->prepare('SELECT g.*, u.name AS admin_name FROM grounds g LEFT JOIN users u ON g.admin_id = u.id WHERE g.admin_id = ? ORDER BY g.created_at DESC');
	$stmt->execute([$me['id']]);
	$grounds = $stmt->fetchAll();
}

include __DIR__ . '/../partials/header.php';
?>

	<h2 class="text-2xl md:text-3xl font-semibold mb-4">Manage Grounds</h2>
	<div class="grid gap-4 grid-cols-1 lg:grid-cols-2">
		<div class="card">
			<h3 class="text-lg font-semibold">Add Ground</h3>
			<div class="mb-4">
				<a href="maps_instructions.php" class="text-blue-600 hover:text-blue-800 text-sm" target="_blank">
					🗺️ Need help adding Google Maps links? Click here for instructions
				</a>
			</div>
			<form method="post" class="space-y-2" enctype="multipart/form-data">
				<input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
				<input type="hidden" name="action" value="create">
				<div class="field"><label>Name</label><input name="name" required></div>
				<div class="field"><label>Location</label><input name="location"></div>
				<div class="field"><label>Description</label><textarea name="description"></textarea></div>
				<div class="field"><label>Price per hour</label><input type="number" step="0.01" name="price_per_hour" required></div>
				<div class="field"><label>Google Maps Link (optional)</label><input type="url" name="maps_link"></div>
				<div class="field"><label>Image</label><input type="file" name="image" accept="image/*"></div>
				<div class="field"><label><input type="checkbox" name="is_active" checked> Active</label></div>
				<button class="btn" type="submit">Create</button>
			</form>
		</div>
		<div class="card overflow-x-auto">
			<h3 class="text-lg font-semibold">
				<?php if ($me['role'] === 'superadmin'): ?>
					All Grounds
				<?php else: ?>
					My Grounds
				<?php endif; ?>
			</h3>
			<table class="min-w-full">
				<thead>
					<tr>
						<th>Image</th>
						<th>Name</th>
						<th>Location</th>
						<th>Price</th>
						<th>Active</th>
						<th>Maps</th>
						<?php if ($me['role'] === 'superadmin'): ?>
							<th>Admin</th>
						<?php endif; ?>
						<th>Actions</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($grounds as $g): ?>
					<tr>
						<td>
							<?php if (!empty($g['image_path'])): ?>
								<img src="<?php echo '../' . htmlspecialchars($g['image_path']); ?>" alt="<?php echo htmlspecialchars($g['name']); ?>" style="width:64px;height:48px;object-fit:cover;border-radius:6px;" />
							<?php else: ?>
								<span class="text-slate-400">No image</span>
							<?php endif; ?>
						</td>
						<td class="font-medium"><?php echo htmlspecialchars($g['name']); ?></td>
						<td><?php echo htmlspecialchars($g['location']); ?></td>
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
						<?php if ($me['role'] === 'superadmin'): ?>
							<td>
								<?php if ($g['admin_name']): ?>
									<span class="badge success"><?php echo htmlspecialchars($g['admin_name']); ?></span>
								<?php else: ?>
									<span class="badge pending">Unassigned</span>
								<?php endif; ?>
							</td>
						<?php endif; ?>
						<td>
							<form method="post" style="display:inline">
								<input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
								<input type="hidden" name="action" value="delete">
								<input type="hidden" name="id" value="<?php echo (int)$g['id']; ?>">
								<button class="btn danger" onclick="return confirm('Delete this ground?')">Delete</button>
							</form>
							<button class="btn secondary" onclick="document.getElementById('edit-<?php echo (int)$g['id']; ?>').style.display='block'">Edit</button>
							<a class="btn" href="slots.php?ground_id=<?php echo (int)$g['id']; ?>">Manage Slots</a>
						</td>
					</tr>
					<tr id="edit-<?php echo (int)$g['id']; ?>" style="display:none">
						<td colspan="<?php echo $me['role'] === 'superadmin' ? '8' : '7'; ?>">
							<form method="post" class="space-y-2" enctype="multipart/form-data">
								<input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
								<input type="hidden" name="action" value="update">
								<input type="hidden" name="id" value="<?php echo (int)$g['id']; ?>">
								<div class="grid">
									<div class="field"><label>Name</label><input name="name" value="<?php echo htmlspecialchars($g['name']); ?>" required></div>
									<div class="field"><label>Location</label><input name="location" value="<?php echo htmlspecialchars($g['location']); ?>"></div>
									<div class="field"><label>Price per hour</label><input type="number" step="0.01" name="price_per_hour" value="<?php echo htmlspecialchars($g['price_per_hour']); ?>" required></div>
									<div class="field"><label>Description</label><textarea name="description"><?php echo htmlspecialchars($g['description']); ?></textarea></div>
									<div class="field"><label>Google Maps Link (optional)</label><input type="url" name="maps_link" value="<?php echo htmlspecialchars($g['maps_link']); ?>"></div>
									<div class="field"><label>Image (optional)</label><input type="file" name="image" accept="image/*"></div>
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
