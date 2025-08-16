<?php require_once __DIR__ . '/config.php';
verify_csrf();
if (!is_logged_in()) { header('Location: ' . BASE_URL . '/login.php'); exit; }
$pdo = get_pdo();
$user = current_user();

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) { flash('error', 'Invalid booking'); header('Location: ' . BASE_URL . '/my_bookings.php'); exit; }

// Get booking details before deletion
$stmt = $pdo->prepare('SELECT b.*, u.name AS user_name, u.email AS user_email, g.name AS ground_name, g.admin_id, t.start_time, t.end_time FROM bookings b JOIN users u ON b.user_id=u.id JOIN grounds g ON b.ground_id=g.id JOIN time_slots t ON b.slot_id=t.id WHERE b.id = ? AND b.user_id = ? AND b.status IN ("pending","confirmed")');
$stmt->execute([$id, $user['id']]);
$booking = $stmt->fetch();
if (!$booking) { flash('error', 'Cannot cancel this booking'); header('Location: ' . BASE_URL . '/my_bookings.php'); exit; }

// Get admin details
$adminStmt = $pdo->prepare('SELECT name, email FROM users WHERE id = ?');
$adminStmt->execute([$booking['admin_id']]);
$admin = $adminStmt->fetch();

// Delete booking to free the slot
$del = $pdo->prepare('DELETE FROM bookings WHERE id = ?');
$del->execute([$id]);

// Send email notifications
require_once __DIR__ . '/lib/Mailer.php';

// Email to user
$userSubject = 'Booking Cancelled - ' . APP_NAME;
$userBody = "
<p>Hello {$booking['user_name']},</p>
<p>Your booking has been cancelled successfully.</p>
<p><strong>Booking Details:</strong></p>
<ul>
<li>Ground: {$booking['ground_name']}</li>
<li>Date: {$booking['play_date']}</li>
<li>Time: " . substr($booking['start_time'],0,5) . ' - ' . substr($booking['end_time'],0,5) . "</li>
<li>Amount: ₹" . number_format($booking['total_amount'],2) . "</li>
</ul>
<p>If you have any questions, please contact us.</p>
<p>Regards,<br>" . APP_NAME . "</p>
";
Mailer::send($booking['user_email'], $userSubject, $userBody);

// Email to admin
if ($admin) {
    $adminSubject = 'User Cancelled Booking - ' . APP_NAME;
    $adminBody = "
    <p>Hello {$admin['name']},</p>
    <p>A user has cancelled their booking.</p>
    <p><strong>Booking Details:</strong></p>
    <ul>
    <li>User: {$booking['user_name']} ({$booking['user_email']})</li>
    <li>Ground: {$booking['ground_name']}</li>
    <li>Date: {$booking['play_date']}</li>
    <li>Time: " . substr($booking['start_time'],0,5) . ' - ' . substr($booking['end_time'],0,5) . "</li>
    <li>Amount: ₹" . number_format($booking['total_amount'],2) . "</li>
    </ul>
    <p>The slot is now available for other bookings.</p>
    <p>Regards,<br>" . APP_NAME . "</p>
    ";
    Mailer::send($admin['email'], $adminSubject, $adminBody);
}

flash('success', 'Booking cancelled and notifications sent');
header('Location: ' . BASE_URL . '/my_bookings.php');
exit;

