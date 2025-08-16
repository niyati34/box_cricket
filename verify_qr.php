<?php require_once __DIR__ . '/config.php';
require_role(['admin','superadmin']);
$pdo = get_pdo();
$token = $_GET['token'] ?? '';
$manual = isset($_GET['manual']);

// If token is present and not in manual mode, auto-verify and check-in
if ($token !== '' && !$manual) {
	$stmt = $pdo->prepare('SELECT b.*, u.name AS user_name, g.name AS ground_name, t.start_time, t.end_time FROM bookings b JOIN users u ON b.user_id=u.id JOIN grounds g ON b.ground_id=g.id JOIN time_slots t ON b.slot_id=t.id WHERE b.qr_token = ? AND b.status = "confirmed"');
	$stmt->execute([$token]);
	$bk = $stmt->fetch();
	if ($bk) {
		$upd = $pdo->prepare('UPDATE bookings SET checked_in_at = NOW() WHERE id = ? AND checked_in_at IS NULL');
		$upd->execute([$bk['id']]);
		flash('success', 'QR valid. Check-in recorded for ' . htmlspecialchars($bk['user_name']));
		header('Location: ' . BASE_URL . '/admin/bookings.php');
		exit;
	} else {
		flash('error', 'Invalid or expired QR');
		header('Location: ' . BASE_URL . '/admin/bookings.php');
		exit;
}
}

include __DIR__ . '/partials/header.php';
?>
	<div class="card max-w-3xl mx-auto">
		<h2 class="text-2xl font-semibold mb-4">Scan/Verify QR</h2>
		<p class="mb-3">Use ScanApp to scan the user's QR. On success, you'll be returned here and the token will auto-fill below.</p>

		<div class="flex gap-3 flex-wrap items-center mb-4">
			<?php $ret = BASE_URL . '/verify_qr.php?manual=1&token={CODE}'; ?>
			<a class="btn" href="<?php echo 'https://scanapp.org/scan?ret=' . urlencode($ret); ?>">Open ScanApp</a>
			<span class="text-sm text-slate-500">Requires the ScanApp mobile app.</span>
		</div>

		<form method="get" class="space-y-3" id="manual-form">
			<div class="field">
				<label>Token</label>
				<input type="text" id="token-field" name="token" placeholder="Paste or scan token" value="<?php echo $manual && $token !== '' ? htmlspecialchars($token) : ''; ?>" />
			</div>
			<button class="btn">Verify</button>
		</form>
	</div>

	<script>
	(function(){
		// If ScanApp returned with token and manual=1, optionally auto-submit
		const params = new URLSearchParams(window.location.search);
		if (params.get('manual') === '1' && params.get('token')) {
			// Auto-fill already done from PHP; uncomment to auto-submit:
			// document.getElementById('manual-form').submit();
		}
	})();
	</script>

<?php include __DIR__ . '/partials/footer.php'; ?>

