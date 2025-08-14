<?php require_once __DIR__ . '/config.php';
if (!is_logged_in()) { header('Location: ' . BASE_URL . '/login.php'); exit; }
$pdo = get_pdo();
$user = current_user();

$stmt = $pdo->prepare('SELECT b.*, g.name AS ground_name, t.start_time, t.end_time FROM bookings b JOIN grounds g ON b.ground_id = g.id JOIN time_slots t ON b.slot_id = t.id WHERE b.user_id = ? ORDER BY b.play_date DESC, t.start_time DESC');
$stmt->execute([$user['id']]);
$bookings = $stmt->fetchAll();

include __DIR__ . '/partials/header.php';
?>

	<h2 class="text-2xl md:text-3xl font-semibold mb-4">My Bookings</h2>
	<div class="card overflow-x-auto">
		<table class="min-w-full">
			<thead>
				<tr>
					<th>Date</th>
					<th>Ground</th>
					<th>Slot</th>
					<th>Amount</th>
					<th>Status</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($bookings as $b): ?>
					<tr>
						<td><?php echo htmlspecialchars($b['play_date']); ?></td>
						<td><?php echo htmlspecialchars($b['ground_name']); ?></td>
						<td><?php echo substr($b['start_time'],0,5) . ' - ' . substr($b['end_time'],0,5); ?></td>
						<td>₹<?php echo number_format($b['total_amount'], 2); ?></td>
						<td><?php echo htmlspecialchars($b['status']); ?></td>
						<td>
							<?php if (in_array($b['status'], ['pending','confirmed'])): ?>
							<form method="post" action="user_cancel_booking.php" style="display:inline">
								<input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
								<input type="hidden" name="id" value="<?php echo (int)$b['id']; ?>">
								<button class="btn danger">Cancel</button>
							</form>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
