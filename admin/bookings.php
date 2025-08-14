<?php require_once __DIR__ . '/../config.php';
require_role(['admin','superadmin']);
$pdo = get_pdo();
$me = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	verify_csrf();
	$id = (int)($_POST['id'] ?? 0);
	$action = $_POST['action'] ?? '';
	// Ensure the booking belongs to a ground managed by this admin (or superadmin bypass)
	$ownerCheck = $pdo->prepare('SELECT b.id FROM bookings b JOIN grounds g ON b.ground_id=g.id WHERE b.id=? AND (? = "superadmin" OR g.admin_id = ?)');
	$ownerCheck->execute([$id, $me['role'], $me['id']]);
	if (!$ownerCheck->fetch()) { flash('error', 'Not authorized'); header('Location: bookings.php'); exit; }

	if ($action === 'approve') {
		$stmt = $pdo->prepare('UPDATE bookings SET status = "confirmed" WHERE id = ? AND status = "pending"');
		$stmt->execute([$id]);
		flash('success', 'Booking approved');
	}
	if ($action === 'cancel') {
		// Delete booking to free the slot (due to unique constraint on bookings)
		$del = $pdo->prepare('DELETE FROM bookings WHERE id = ? AND status IN ("pending","confirmed")');
		$del->execute([$id]);
		flash('success', 'Booking cancelled');
	}
}

$where = $me['role'] === 'admin' ? 'WHERE g.admin_id = ?' : '';
$stmt = $pdo->prepare('SELECT b.*, u.name AS user_name, g.name AS ground_name, t.start_time, t.end_time FROM bookings b JOIN users u ON b.user_id=u.id JOIN grounds g ON b.ground_id=g.id JOIN time_slots t ON b.slot_id=t.id ' . $where . ' ORDER BY b.created_at DESC');
if ($me['role'] === 'admin') { $stmt->execute([$me['id']]); } else { $stmt->execute(); }
$bookings = $stmt->fetchAll();

include __DIR__ . '/../partials/header.php';
?>

	<h2 class="text-2xl md:text-3xl font-semibold mb-4">All Bookings</h2>
	<div class="card overflow-x-auto">
		<table class="min-w-full">
			<thead>
				<tr>
					<th>User</th><th>Date</th><th>Ground</th><th>Slot</th><th>Amount</th><th>Status</th><th>Actions</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($bookings as $b): ?>
				<tr>
					<td class="font-medium"><?php echo htmlspecialchars($b['user_name']); ?></td>
					<td><?php echo htmlspecialchars($b['play_date']); ?></td>
					<td><?php echo htmlspecialchars($b['ground_name']); ?></td>
					<td><?php echo substr($b['start_time'],0,5) . ' - ' . substr($b['end_time'],0,5); ?></td>
					<td>₹<?php echo number_format($b['total_amount'],2); ?></td>
					<td><?php echo htmlspecialchars($b['status']); ?></td>
					<td>
						<?php if ($b['status'] === 'pending'): ?>
						<form method="post" style="display:inline">
							<input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
							<input type="hidden" name="id" value="<?php echo (int)$b['id']; ?>">
							<input type="hidden" name="action" value="approve">
							<button class="btn">Approve</button>
						</form>
						<?php endif; ?>
						<?php if ($b['status'] === 'pending' || $b['status'] === 'confirmed'): ?>
						<form method="post" style="display:inline">
							<input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
							<input type="hidden" name="id" value="<?php echo (int)$b['id']; ?>">
							<input type="hidden" name="action" value="cancel">
							<button class="btn danger">Cancel</button>
						</form>
						<?php endif; ?>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
