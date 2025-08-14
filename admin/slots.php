<?php require_once __DIR__ . '/../config.php';
require_role(['admin','superadmin']);
$pdo = get_pdo();
$me = current_user();

$ground_id = (int)($_GET['ground_id'] ?? 0);
if ($ground_id <= 0) { header('Location: grounds.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	verify_csrf();
	$action = $_POST['action'] ?? '';
	if ($action === 'create') {
		$stmt = $pdo->prepare('INSERT INTO time_slots (ground_id, start_time, end_time, is_active) VALUES (?, ?, ?, ?)');
		$stmt->execute([$ground_id, $_POST['start_time'], $_POST['end_time'], isset($_POST['is_active']) ? 1 : 0]);
		flash('success', 'Slot added');
	}
	if ($action === 'delete') {
		$id = (int)($_POST['id'] ?? 0);
		$stmt = $pdo->prepare('DELETE FROM time_slots WHERE id = ? AND ground_id = ?');
		$stmt->execute([$id, $ground_id]);
		flash('success', 'Slot deleted');
	}
	if ($action === 'toggle') {
		$id = (int)($_POST['id'] ?? 0);
		$stmt = $pdo->prepare('UPDATE time_slots SET is_active = NOT is_active WHERE id = ? AND ground_id = ?');
		$stmt->execute([$id, $ground_id]);
		flash('success', 'Slot updated');
	}
	header('Location: slots.php?ground_id=' . $ground_id); exit;
}

$ground = $pdo->prepare('SELECT * FROM grounds WHERE id = ?');
$ground->execute([$ground_id]);
$ground = $ground->fetch();
if (!$ground) { header('Location: grounds.php'); exit; }
if ($me['role'] === 'admin' && (int)($ground['admin_id'] ?? 0) !== (int)$me['id']) { http_response_code(403); echo 'Forbidden'; exit; }

$slots = $pdo->prepare('SELECT * FROM time_slots WHERE ground_id = ? ORDER BY start_time');
$slots->execute([$ground_id]);
$slots = $slots->fetchAll();

include __DIR__ . '/../partials/header.php';
?>

	<h2 class="text-2xl md:text-3xl font-semibold mb-4">Manage Slots - <?php echo htmlspecialchars($ground['name']); ?></h2>
	<div class="grid gap-4 grid-cols-1 lg:grid-cols-2">
		<div class="card">
			<h3 class="text-lg font-semibold">Add Slot</h3>
			<form method="post" class="space-y-2">
				<input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
				<input type="hidden" name="action" value="create">
				<div class="field"><label>Start Time</label><input type="time" name="start_time" required></div>
				<div class="field"><label>End Time</label><input type="time" name="end_time" required></div>
				<div class="field"><label><input type="checkbox" name="is_active" checked> Active</label></div>
				<button class="btn">Add</button>
			</form>
		</div>
		<div class="card overflow-x-auto">
			<h3 class="text-lg font-semibold">Slots</h3>
			<table class="min-w-full">
				<thead><tr><th>Start</th><th>End</th><th>Active</th><th>Actions</th></tr></thead>
				<tbody>
					<?php foreach ($slots as $s): ?>
					<tr>
						<td><?php echo substr($s['start_time'],0,5); ?></td>
						<td><?php echo substr($s['end_time'],0,5); ?></td>
						<td><?php echo $s['is_active'] ? 'Yes' : 'No'; ?></td>
						<td>
							<form method="post" style="display:inline">
								<input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
								<input type="hidden" name="action" value="toggle">
								<input type="hidden" name="id" value="<?php echo (int)$s['id']; ?>">
								<button class="btn secondary">Toggle</button>
							</form>
							<form method="post" style="display:inline">
								<input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
								<input type="hidden" name="action" value="delete">
								<input type="hidden" name="id" value="<?php echo (int)$s['id']; ?>">
								<button class="btn danger" onclick="return confirm('Delete this slot?')">Delete</button>
							</form>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
