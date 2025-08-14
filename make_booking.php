<?php require_once __DIR__ . '/config.php';
verify_csrf();
if (!is_logged_in()) { flash('error', 'Please login to book'); header('Location: ' . BASE_URL . '/login.php'); exit; }

$pdo = get_pdo();
$user = current_user();

$ground_id = (int)($_POST['ground_id'] ?? 0);
$slot_id = (int)($_POST['slot_id'] ?? 0);
$play_date = $_POST['play_date'] ?? '';

if ($ground_id <= 0 || $slot_id <= 0 || !$play_date) {
	flash('error', 'Invalid booking details');
	header('Location: ' . BASE_URL . '/grounds.php');
	exit;
}

$pdo->beginTransaction();
try {
	// Verify ground and get price
	$stmt = $pdo->prepare('SELECT id, price_per_hour FROM grounds WHERE id = ? AND is_active = 1 FOR UPDATE');
	$stmt->execute([$ground_id]);
	$ground = $stmt->fetch();
	if (!$ground) { throw new Exception('Ground not available'); }

	// Verify slot
	$stmt = $pdo->prepare('SELECT id FROM time_slots WHERE id = ? AND ground_id = ? AND is_active = 1 FOR UPDATE');
	$stmt->execute([$slot_id, $ground_id]);
	$slot = $stmt->fetch();
	if (!$slot) { throw new Exception('Slot not available'); }

	// Check existing booking
	$stmt = $pdo->prepare('SELECT id FROM bookings WHERE ground_id = ? AND slot_id = ? AND play_date = ? AND status IN ("pending","confirmed") FOR UPDATE');
	$stmt->execute([$ground_id, $slot_id, $play_date]);
	$exists = $stmt->fetch();
	if ($exists) { throw new Exception('Slot already booked'); }

	// Create booking as pending; admins will approve/reject
	$total = (float)$ground['price_per_hour'];
	$stmt = $pdo->prepare('INSERT INTO bookings (user_id, ground_id, slot_id, play_date, total_amount, status) VALUES (?, ?, ?, ?, ?, "pending")');
	$stmt->execute([$user['id'], $ground_id, $slot_id, $play_date, $total]);
	$pdo->commit();
	flash('success', 'Booking requested. Awaiting admin approval.');
	header('Location: ' . BASE_URL . '/my_bookings.php');
} catch (Exception $e) {
	$pdo->rollBack();
	flash('error', $e->getMessage());
	header('Location: ' . BASE_URL . '/ground.php?id=' . $ground_id . '&date=' . urlencode($play_date));
}
exit;

