<?php require_once __DIR__ . '/../config.php';
require_role(['admin','superadmin']);
$pdo = get_pdo();

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
		$stmt = $pdo->prepare('INSERT INTO grounds (name, location, description, price_per_hour, is_active, image_path) VALUES (?, ?, ?, ?, ?, ?)');
		$stmt->execute([
			trim($_POST['name'] ?? ''),
			trim($_POST['location'] ?? ''),
			trim($_POST['description'] ?? ''),
			(float)($_POST['price_per_hour'] ?? 0),
			isset($_POST['is_active']) ? 1 : 0,
			$imagePath
		]);
		flash('success', 'Ground created');
		header('Location: grounds.php'); exit;
	}
	if ($action === 'update') {
		$id = (int)($_POST['id'] ?? 0);
		$newImage = handle_image_upload('image', $uploadDir); // optional
		if ($newImage) {
			$stmt = $pdo->prepare('UPDATE grounds SET name=?, location=?, description=?, price_per_hour=?, is_active=?, image_path=? WHERE id=?');
			$stmt->execute([
				trim($_POST['name'] ?? ''),
				trim($_POST['location'] ?? ''),
				trim($_POST['description'] ?? ''),
				(float)($_POST['price_per_hour'] ?? 0),
				isset($_POST['is_active']) ? 1 : 0,
				$newImage,
				$id
			]);
		} else {
			$stmt = $pdo->prepare('UPDATE grounds SET name=?, location=?, description=?, price_per_hour=?, is_active=? WHERE id=?');
			$stmt->execute([
				trim($_POST['name'] ?? ''),
				trim($_POST['location'] ?? ''),
				trim($_POST['description'] ?? ''),
				(float)($_POST['price_per_hour'] ?? 0),
				isset($_POST['is_active']) ? 1 : 0,
				$id
			]);
		}
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

// Fetch grounds
$grounds = $pdo->query('SELECT * FROM grounds ORDER BY created_at DESC')->fetchAll();

include __DIR__ . '/../partials/header.php';
?>

	<h2 class="text-2xl md:text-3xl font-semibold mb-4">Manage Grounds</h2>
	<div class="grid gap-4 grid-cols-1 lg:grid-cols-2">
		<div class="card">
			<h3 class="text-lg font-semibold">Add Ground</h3>
			<form method="post" class="space-y-2" enctype="multipart/form-data">
				<input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
				<input type="hidden" name="action" value="create">
				<div class="field"><label>Name</label><input name="name" required></div>
				<div class="field"><label>Location</label><input name="location"></div>
				<div class="field"><label>Description</label><textarea name="description"></textarea></div>
				<div class="field"><label>Price per hour</label><input type="number" step="0.01" name="price_per_hour" required></div>
				<div class="field"><label>Image</label><input type="file" name="image" accept="image/*"></div>
				<div class="field"><label><input type="checkbox" name="is_active" checked> Active</label></div>
				<button class="btn" type="submit">Create</button>
			</form>
		</div>
		<div class="card overflow-x-auto">
			<h3 class="text-lg font-semibold">All Grounds</h3>
			<table class="min-w-full">
				<thead><tr><th>Image</th><th>Name</th><th>Location</th><th>Price</th><th>Active</th><th>Actions</th></tr></thead>
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
						<td colspan="6">
							<form method="post" class="space-y-2" enctype="multipart/form-data">
								<input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
								<input type="hidden" name="action" value="update">
								<input type="hidden" name="id" value="<?php echo (int)$g['id']; ?>">
								<div class="grid">
									<div class="field"><label>Name</label><input name="name" value="<?php echo htmlspecialchars($g['name']); ?>" required></div>
									<div class="field"><label>Location</label><input name="location" value="<?php echo htmlspecialchars($g['location']); ?>"></div>
									<div class="field"><label>Price per hour</label><input type="number" step="0.01" name="price_per_hour" value="<?php echo htmlspecialchars($g['price_per_hour']); ?>" required></div>
									<div class="field"><label>Description</label><textarea name="description"><?php echo htmlspecialchars($g['description']); ?></textarea></div>
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
