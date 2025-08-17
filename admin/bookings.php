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
		$pdo->beginTransaction();
		try {
			$sel = $pdo->prepare('SELECT b.*, u.email, u.name, g.name AS ground_name, t.start_time, t.end_time FROM bookings b JOIN users u ON b.user_id=u.id JOIN grounds g ON b.ground_id=g.id JOIN time_slots t ON b.slot_id=t.id WHERE b.id = ? FOR UPDATE');
			$sel->execute([$id]);
			$bk = $sel->fetch();
			if (!$bk || $bk['status'] !== 'pending') { throw new Exception('Invalid booking'); }

			$qr = !empty($bk['qr_token']) ? $bk['qr_token'] : bin2hex(random_bytes(16));
			$upd = $pdo->prepare('UPDATE bookings SET status = "confirmed", qr_token = ? WHERE id = ?');
			$upd->execute([$qr, $id]);
			$pdo->commit();

			require_once __DIR__ . '/../lib/Mailer.php';
			$qrUrl = BASE_URL . '/verify_qr.php?token=' . urlencode($qr);

			// Generate PNG binary using Google Chart as fallback
			$qrPngBinary = null;
			try {
				$png = @file_get_contents('https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=' . urlencode($qrUrl) . '&choe=UTF-8');
				if ($png !== false) { $qrPngBinary = $png; }
			} catch (Exception $e) { /* ignore */ }

			$subject = 'Booking Confirmed - ' . APP_NAME;
			$body = "Hello {$bk['name']},\n\nYour booking is confirmed.\n\nGround: {$bk['ground_name']}\nDate: {$bk['play_date']}\nTime: " . substr($bk['start_time'],0,5) . ' - ' . substr($bk['end_time'],0,5) . "\nAmount: ₹" . number_format($bk['total_amount'],2) . "\n\nShow this QR at entry: {$qrUrl}\n\nRegards,\n" . APP_NAME;
			Mailer::send($bk['email'], $subject, nl2br($body), $qrUrl, $qrPngBinary);

			flash('success', 'Booking approved and email sent');
		} catch (Exception $e) {
			$pdo->rollBack();
			flash('error', 'Approve failed: ' . $e->getMessage());
		}
	}
	if ($action === 'reject') {
		$pdo->beginTransaction();
		try {
			$sel = $pdo->prepare('SELECT b.*, u.email, u.name, g.name AS ground_name, t.start_time, t.end_time FROM bookings b JOIN users u ON b.user_id=u.id JOIN grounds g ON b.ground_id=g.id JOIN time_slots t ON b.slot_id=t.id WHERE b.id = ? FOR UPDATE');
			$sel->execute([$id]);
			$bk = $sel->fetch();
			if (!$bk || $bk['status'] !== 'pending') { throw new Exception('Invalid booking'); }

			// Delete the rejected booking
			$del = $pdo->prepare('DELETE FROM bookings WHERE id = ?');
			$del->execute([$id]);
			$pdo->commit();

			// Send rejection email to user
			require_once __DIR__ . '/../lib/Mailer.php';
			$subject = 'Booking Request Rejected - ' . APP_NAME;
			$body = "
			<p>Hello {$bk['name']},</p>
			<p>We regret to inform you that your booking request has been rejected.</p>
			<p><strong>Booking Details:</strong></p>
			<ul>
			<li>Ground: {$bk['ground_name']}</li>
			<li>Date: {$bk['play_date']}</li>
			<li>Time: " . substr($bk['start_time'],0,5) . ' - ' . substr($bk['end_time'],0,5) . "</li>
			<li>Amount: ₹" . number_format($bk['total_amount'],2) . "</li>
			</ul>
			<p>If you have any questions about this decision, please contact us.</p>
			<p>Regards,<br>" . APP_NAME . "</p>
			";
			Mailer::send($bk['email'], $subject, $body);

			flash('success', 'Booking rejected and email sent');
		} catch (Exception $e) {
			$pdo->rollBack();
			flash('error', 'Reject failed: ' . $e->getMessage());
		}
	}
	if ($action === 'cancel') {
		$pdo->beginTransaction();
		try {
			$sel = $pdo->prepare('SELECT b.*, u.email, u.name, g.name AS ground_name, t.start_time, t.end_time FROM bookings b JOIN users u ON b.user_id=u.id JOIN grounds g ON b.ground_id=g.id JOIN time_slots t ON b.slot_id=t.id WHERE b.id = ? FOR UPDATE');
			$sel->execute([$id]);
			$bk = $sel->fetch();
			if (!$bk || !in_array($bk['status'], ['pending', 'confirmed'])) { throw new Exception('Invalid booking'); }

			// Delete the cancelled booking
			$del = $pdo->prepare('DELETE FROM bookings WHERE id = ? AND status IN ("pending","confirmed")');
			$del->execute([$id]);
			$pdo->commit();

			// Send cancellation email to user
			require_once __DIR__ . '/../lib/Mailer.php';
			$subject = 'Booking Cancelled by Admin - ' . APP_NAME;
			$body = "
			<p>Hello {$bk['name']},</p>
			<p>Your booking has been cancelled by an administrator.</p>
			<p><strong>Booking Details:</strong></p>
			<ul>
			<li>Ground: {$bk['ground_name']}</li>
			<li>Date: {$bk['play_date']}</li>
			<li>Time: " . substr($bk['start_time'],0,5) . ' - ' . substr($bk['end_time'],0,5) . "</li>
			<li>Amount: ₹" . number_format($bk['total_amount'],2) . "</li>
			</ul>
			<p>If you have any questions about this cancellation, please contact us.</p>
			<p>Regards,<br>" . APP_NAME . "</p>
			";
			Mailer::send($bk['email'], $subject, $body);

			flash('success', 'Booking cancelled and email sent');
		} catch (Exception $e) {
			$pdo->rollBack();
			flash('error', 'Cancel failed: ' . $e->getMessage());
		}
	}
}

if ($me['role'] === 'admin') {
    $stmt = $pdo->prepare('SELECT b.*, u.name AS user_name, g.name AS ground_name, t.start_time, t.end_time, t.hours_per_slot FROM bookings b JOIN users u ON b.user_id=u.id JOIN grounds g ON b.ground_id=g.id JOIN time_slots t ON b.slot_id=t.id WHERE g.admin_id = ? ORDER BY b.created_at DESC');
    $stmt->execute([$me['id']]);
} else {
    $stmt = $pdo->prepare('SELECT b.*, u.name AS user_name, g.name AS ground_name, t.start_time, t.end_time, t.hours_per_slot FROM bookings b JOIN users u ON b.user_id=u.id JOIN grounds g ON b.ground_id=g.id JOIN time_slots t ON b.slot_id=t.id ORDER BY b.created_at DESC');
    $stmt->execute();
}
$bookings = $stmt->fetchAll();

include __DIR__ . '/../partials/header.php';
?>

	<h2 class="text-2xl md:text-3xl font-semibold mb-4">All Bookings</h2>
	
	<!-- Slot Availability Checker -->
	<div class="card mb-6 max-w-2xl">
		<h3 class="text-lg font-semibold mb-3">Check Slot Availability</h3>
		<div class="grid gap-4 grid-cols-1 md:grid-cols-3">
			<div class="field">
				<label for="check_ground">Ground</label>
				<select id="check_ground" onchange="checkAvailability()">
					<option value="">Select Ground</option>
					<?php
					if ($me['role'] === 'admin') {
						$groundsStmt = $pdo->prepare('SELECT id, name FROM grounds WHERE is_active = 1 AND admin_id = ? ORDER BY name');
						$groundsStmt->execute([$me['id']]);
					} else {
						$groundsStmt = $pdo->prepare('SELECT id, name FROM grounds WHERE is_active = 1 ORDER BY name');
						$groundsStmt->execute();
					}
					$grounds = $groundsStmt->fetchAll();
					foreach ($grounds as $ground):
					?>
						<option value="<?php echo $ground['id']; ?>"><?php echo htmlspecialchars($ground['name']); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="field">
				<label for="check_date">Date</label>
				<input type="date" id="check_date" min="<?php echo date('Y-m-d'); ?>" onchange="checkAvailability()">
			</div>
			<div class="field">
				<label>&nbsp;</label>
				<button type="button" class="btn w-full" onclick="checkAvailability()">Check</button>
			</div>
		</div>
		<div id="availability_result" class="mt-3"></div>
	</div>
	
	<div class="card overflow-x-auto">
		<div class="flex justify-between items-center mb-4">
			<h3 class="text-lg font-semibold">All Bookings</h3>
			<div class="flex gap-2">
				<a href="checkin.php" class="btn secondary">Check-in Users</a>
				<a href="offline_bookings.php" class="btn">+ Add Offline Booking</a>
			</div>
		</div>
		<table class="min-w-full">
			<thead>
				<tr>
					<th>User</th><th>Date</th><th>Ground</th><th>Slot</th><th>Hours</th><th>Amount</th><th>Status</th><th>Actions</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($bookings as $b): ?>
				<tr>
					<td class="font-medium"><?php echo htmlspecialchars($b['user_name']); ?></td>
					<td><?php echo htmlspecialchars($b['play_date']); ?></td>
					<td><?php echo htmlspecialchars($b['ground_name']); ?></td>
					<td><?php echo substr($b['start_time'],0,5) . ' - ' . substr($b['end_time'],0,5); ?></td>
					<td><?php echo $b['hours_per_slot']; ?>h</td>
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
						<form method="post" style="display:inline">
							<input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
							<input type="hidden" name="id" value="<?php echo (int)$b['id']; ?>">
							<input type="hidden" name="action" value="reject">
							<button class="btn danger">Reject</button>
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

<script>
function checkAvailability() {
    const groundId = document.getElementById('check_ground').value;
    const date = document.getElementById('check_date').value;
    const resultDiv = document.getElementById('availability_result');
    
    if (!groundId || !date) {
        resultDiv.innerHTML = '<p class="text-slate-500">Please select both ground and date</p>';
        return;
    }
    
    resultDiv.innerHTML = '<p class="text-slate-500">Checking availability...</p>';
    
    fetch('get_available_slots.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `ground_id=${groundId}&play_date=${date}&csrf_token=<?php echo csrf_token(); ?>`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.slots.length > 0) {
                let html = '<div class="bg-green-50 border border-green-200 rounded p-3">';
                html += '<p class="text-green-800 font-medium">Available Slots:</p>';
                html += '<div class="grid grid-cols-2 md:grid-cols-3 gap-2 mt-2">';
                data.slots.forEach(slot => {
                    html += `<div class="text-sm text-green-700">${slot.start_time} - ${slot.end_time} (₹${slot.price})</div>`;
                });
                html += '</div>';
                html += '<div class="mt-3">';
                html += '<a href="offline_bookings.php" class="btn btn-sm">Add Offline Booking</a>';
                html += '</div>';
                html += '</div>';
                resultDiv.innerHTML = html;
            } else {
                resultDiv.innerHTML = '<div class="bg-red-50 border border-red-200 rounded p-3"><p class="text-red-800">No available slots for this date</p></div>';
            }
        } else {
            resultDiv.innerHTML = '<div class="bg-red-50 border border-red-200 rounded p-3"><p class="text-red-800">Error: ' + (data.error || 'Unknown error') + '</p></div>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        resultDiv.innerHTML = '<div class="bg-red-50 border border-red-200 rounded p-3"><p class="text-red-800">Error checking availability</p></div>';
    });
}

// Set today's date as default
document.getElementById('check_date').value = new Date().toISOString().split('T')[0];
</script>
