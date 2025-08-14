<?php require_once __DIR__ . '/config.php';
verify_csrf();
if (!is_logged_in()) { header('Location: ' . BASE_URL . '/login.php'); exit; }
$pdo = get_pdo();
$user = current_user();

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) { flash('error', 'Invalid booking'); header('Location: ' . BASE_URL . '/my_bookings.php'); exit; }

// Ensure the booking belongs to this user
$stmt = $pdo->prepare('SELECT id FROM bookings WHERE id = ? AND user_id = ? AND status IN ("pending","confirmed")');
$stmt->execute([$id, $user['id']]);
$booking = $stmt->fetch();
if (!$booking) { flash('error', 'Cannot cancel this booking'); header('Location: ' . BASE_URL . '/my_bookings.php'); exit; }

// Delete booking to free the slot
$del = $pdo->prepare('DELETE FROM bookings WHERE id = ?');
$del->execute([$id]);
flash('success', 'Booking cancelled');
header('Location: ' . BASE_URL . '/my_bookings.php');
exit;

