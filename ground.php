<?php require_once __DIR__ . '/config.php';
$pdo = get_pdo();

$ground_id = (int)($_GET['id'] ?? 0);
$date = $_GET['date'] ?? date('Y-m-d');
// validate date to prevent incorrect filtering
$dt = DateTime::createFromFormat('Y-m-d', $date);
if (!$dt || $dt->format('Y-m-d') !== $date) { $date = date('Y-m-d'); }
if ($ground_id <= 0) { header('Location: grounds.php'); exit; }

$stmt = $pdo->prepare('SELECT * FROM grounds WHERE id = ? AND is_active = 1');
$stmt->execute([$ground_id]);
$ground = $stmt->fetch();
if (!$ground) { header('Location: grounds.php'); exit; }

$slots = $pdo->prepare('SELECT * FROM time_slots WHERE ground_id = ? AND is_active = 1 ORDER BY start_time');
$slots->execute([$ground_id]);
$slots = $slots->fetchAll();

$bookedStmt = $pdo->prepare('SELECT slot_id FROM bookings WHERE ground_id = ? AND play_date = ? AND status IN ("pending","confirmed")');
$bookedStmt->execute([$ground_id, $date]);
$booked = array_column($bookedStmt->fetchAll(), 'slot_id');

$today = date('Y-m-d');
$nowTime = date('H:i:s');
$isToday = ($date === $today);

include __DIR__ . '/partials/header.php';
?>

	<div class="max-w-6xl mx-auto">
		<!-- Ground Header -->
		<div class="card mb-8 text-center">
			<div class="w-full h-64 bg-gradient-to-br from-emerald-100 to-emerald-200 rounded-lg flex items-center justify-center mb-6">
				<div class="text-8xl">🏟️</div>
			</div>
			<h1 class="text-3xl md:text-4xl font-bold text-slate-800 mb-4"><?php echo htmlspecialchars($ground['name']); ?></h1>
			<?php if (!empty($ground['description'])): ?>
				<p class="text-slate-600 text-lg mb-4 max-w-2xl mx-auto"><?php echo htmlspecialchars($ground['description']); ?></p>
			<?php endif; ?>
			<div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-md mx-auto">
				<div class="flex items-center justify-center p-3 bg-slate-50 rounded-lg">
					<span class="mr-2 text-xl">📍</span>
					<span class="font-semibold text-slate-700"><?php echo htmlspecialchars($ground['location'] ?? 'Location not specified'); ?></span>
				</div>
				<div class="flex items-center justify-center p-3 bg-emerald-50 rounded-lg">
					<span class="mr-2 text-xl">💰</span>
					<span class="font-semibold text-emerald-700">₹<?php echo number_format($ground['price_per_hour'], 2); ?>/hour</span>
				</div>
			</div>
		</div>

		<!-- Booking Form -->
		<div class="card">
			<h2 class="text-2xl font-bold text-slate-800 mb-6 text-center">Book Your Slot</h2>
			<form method="post" action="make_booking.php" class="space-y-6">
				<input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
				<input type="hidden" name="ground_id" value="<?php echo (int)$ground['id']; ?>">
				
				<div class="max-w-xs mx-auto">
					<label for="date" class="block text-slate-700 font-semibold mb-2 text-center">📅 Select Date</label>
					<input type="date" id="date" name="play_date" value="<?php echo htmlspecialchars($date); ?>" min="<?php echo $today; ?>" class="w-full border-2 border-slate-200 focus:border-emerald-500 text-center">
					<p class="text-sm text-slate-500 mt-1 text-center">Availability updates when you change the date</p>
				</div>

				<?php if (empty($slots)): ?>
					<div class="text-center py-8">
						<div class="text-4xl mb-2">⏰</div>
						<p class="text-slate-600">No time slots available for this ground.</p>
					</div>
				<?php else: ?>
					<div class="space-y-4">
						<h3 class="text-lg font-semibold text-slate-700 text-center mb-4">Available Time Slots</h3>
						<div class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
							<?php foreach ($slots as $s): $sid = (int)$s['id']; $isPastTime = $isToday && ($s['start_time'] <= $nowTime); $disabled = in_array($sid, $booked, true) || $isPastTime; ?>
								<div class="relative">
									<?php if ($disabled): ?>
										<div class="card bg-slate-100 border-slate-200 cursor-not-allowed">
											<div class="text-center">
												<div class="text-2xl mb-2">🔒</div>
												<p class="font-medium text-slate-600"><?php echo substr($s['start_time'],0,5); ?> - <?php echo substr($s['end_time'],0,5); ?></p>
												<span class="inline-block <?php echo $isPastTime ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800'; ?> text-xs px-2 py-1 rounded-full mt-2"><?php echo $isPastTime ? 'Past' : 'Booked'; ?></span>
											</div>
										</div>
									<?php else: ?>
										<label class="card slot-option cursor-pointer hover:shadow-lg transition-all duration-200 hover:scale-105 border-2 border-transparent hover:border-emerald-200">
											<input type="radio" name="slot_id" value="<?php echo $sid; ?>" class="sr-only">
											<div class="text-center">
												<div class="text-2xl mb-2">⏰</div>
												<p class="font-medium text-slate-700"><?php echo substr($s['start_time'],0,5); ?> - <?php echo substr($s['end_time'],0,5); ?></p>
												<span class="inline-block bg-emerald-100 text-emerald-800 text-xs px-2 py-1 rounded-full mt-2">Available</span>
											</div>
										</label>
									<?php endif; ?>
								</div>
							<?php endforeach; ?>
						</div>
					</div>

					<div class="text-center pt-6">
						<button class="btn text-lg px-8 py-4" type="submit">
							🎯 Book This Slot
						</button>
					</div>
				<?php endif; ?>
			</form>
		</div>
	</div>

	<script>
	(function(){
		var dateInput = document.getElementById('date');
		if (dateInput) {
			dateInput.addEventListener('change', function(){
				var params = new URLSearchParams(window.location.search);
				params.set('id', '<?php echo (int)$ground['id']; ?>');
				params.set('date', this.value);
				window.location.href = 'ground.php?' + params.toString();
			});
		}
	})();
	</script>

<?php include __DIR__ . '/partials/footer.php'; ?>
