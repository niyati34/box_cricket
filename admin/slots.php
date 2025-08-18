<?php require_once __DIR__ . '/../config.php';
require_role(['admin','superadmin']);
$pdo = get_pdo();
$me = current_user();

$ground_id = (int)($_GET['ground_id'] ?? 0);
if ($ground_id <= 0) { header('Location: grounds.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	verify_csrf();
	$action = $_POST['action'] ?? '';
	
	if ($action === 'generate_slots') {
		$hours_per_slot = (float)($_POST['hours_per_slot'] ?? 1.0);
		$start_time = $_POST['start_time'] ?? '06:00';
		$end_time = $_POST['end_time'] ?? '22:00';
		
		// Validate inputs
		if ($hours_per_slot <= 0 || $hours_per_slot > 24) {
			flash('error', 'Invalid hours per slot. Must be between 0.5 and 24 hours.');
		} elseif ($start_time == $end_time) {
			flash('error', 'Start time and end time cannot be the same.');
		} else {
			// Clear existing slots for this ground
			$stmt = $pdo->prepare('DELETE FROM time_slots WHERE ground_id = ?');
			$stmt->execute([$ground_id]);
			
			// Generate slots based on hours
			$current_time = strtotime($start_time);
			$end_timestamp = strtotime($end_time);
			
			// Handle overnight slots (end time < start time means next day)
			if ($end_timestamp < $current_time) {
				$end_timestamp += 86400; // Add 24 hours (86400 seconds)
			}
			
			$slot_count = 0;
			
			// Debug info
			$debug_info = "Generating slots from " . date('H:i', $current_time) . " to " . date('H:i', $end_timestamp) . " with " . $hours_per_slot . " hour intervals";
			if ($end_timestamp > strtotime($start_time) + 86400) {
				$debug_info .= " (overnight period)";
			}
			
			while ($current_time < $end_timestamp) {
				$slot_start = date('H:i:s', $current_time);
				$slot_end_timestamp = $current_time + ($hours_per_slot * 3600); // Calculate end time
				
				// Only create slot if it doesn't exceed the end time
				if ($slot_end_timestamp <= $end_timestamp) {
					$slot_end = date('H:i:s', $slot_end_timestamp);
					
					$stmt = $pdo->prepare('INSERT INTO time_slots (ground_id, start_time, end_time, hours_per_slot, is_active) VALUES (?, ?, ?, ?, 1)');
					$stmt->execute([$ground_id, $slot_start, $slot_end, $hours_per_slot]);
					$slot_count++;
				}
				
				// Move to next slot start time
				$current_time = $slot_end_timestamp;
			}
			
			if ($slot_count > 0) {
				flash('success', $slot_count . ' slots generated successfully. ' . $debug_info);
			} else {
				flash('error', 'No slots could be generated. Please check your time range and hours per slot.');
			}
		}
	}
	
	if ($action === 'toggle') {
		$id = (int)($_POST['id'] ?? 0);
		$stmt = $pdo->prepare('UPDATE time_slots SET is_active = NOT is_active WHERE id = ? AND ground_id = ?');
		$stmt->execute([$id, $ground_id]);
		flash('success', 'Slot updated');
	}
	
	if ($action === 'delete') {
		$id = (int)($_POST['id'] ?? 0);
		$stmt = $pdo->prepare('DELETE FROM time_slots WHERE id = ? AND ground_id = ?');
		$stmt->execute([$id, $ground_id]);
		flash('success', 'Slot deleted');
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
	<div class="grid gap-6 grid-cols-1 lg:grid-cols-2">
		<div class="card">
			<h3 class="text-lg font-semibold mb-4">Generate Slots Automatically</h3>
			<form method="post" class="space-y-4">
				<input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
				<input type="hidden" name="action" value="generate_slots">
				
				<div class="field">
					<label>Hours per Slot</label>
					<input type="number" name="hours_per_slot" min="0.5" max="24" step="0.5" value="1.0" required class="w-full">
					<p class="text-sm text-slate-600">Set how many hours each slot should be (e.g., 1.0 = 1 hour, 1.5 = 1.5 hours)</p>
				</div>
				
				<div class="field">
					<label>Start Time</label>
					<input type="time" name="start_time" value="06:00" required class="w-full">
				</div>
				
				<div class="field">
					<label>End Time</label>
					<input type="time" name="end_time" value="02:00" required class="w-full">
					<p class="text-sm text-slate-600">For overnight slots, set end time to next day (e.g., 02:00 for 2 AM next day)</p>
				</div>
				
				<div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
					<p class="text-sm text-blue-800">
						💡 <strong>Overnight Slots Example:</strong> To create slots from 6 AM today to 2 AM tomorrow:<br>
						• Start Time: 06:00<br>
						• End Time: 02:00<br>
						• Hours per Slot: 1.0<br>
						• This will create: 06:00-07:00, 07:00-08:00, ..., 01:00-02:00 (next day)
					</p>
				</div>
				
				<div class="bg-amber-50 border border-amber-200 rounded-lg p-3">
					<p class="text-sm text-amber-800">
						⚠️ <strong>Warning:</strong> This will replace all existing slots for this ground. 
						You can then activate/deactivate individual slots as needed.
					</p>
				</div>
				
				<div class="flex gap-3">
					<button type="button" onclick="previewSlots()" class="btn secondary flex-1">👁️ Preview Slots</button>
					<button class="btn flex-1" type="submit">🔄 Generate Slots</button>
				</div>
			</form>
		</div>
		
		<div class="card overflow-x-auto">
			<h3 class="text-lg font-semibold mb-4">Manage Generated Slots</h3>
			<?php if (empty($slots)): ?>
				<div class="text-center py-8 text-slate-500">
					<div class="text-4xl mb-2">⏰</div>
					<p>No slots generated yet. Use the form on the left to generate slots.</p>
				</div>
			<?php else: ?>
				<table class="min-w-full">
					<thead>
						<tr>
							<th>Start</th>
							<th>End</th>
							<th>Hours</th>
							<th>Active</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($slots as $s): ?>
						<tr class="<?php echo $s['is_active'] ? '' : 'bg-slate-50'; ?>">
							<td class="font-medium"><?php echo substr($s['start_time'],0,5); ?></td>
							<td><?php echo substr($s['end_time'],0,5); ?></td>
							<td><?php echo $s['hours_per_slot']; ?>h</td>
							<td>
								<span class="px-2 py-1 text-xs rounded-full <?php echo $s['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
									<?php echo $s['is_active'] ? 'Active' : 'Inactive'; ?>
								</span>
							</td>
							<td>
								<form method="post" style="display:inline">
									<input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
									<input type="hidden" name="action" value="toggle">
									<input type="hidden" name="id" value="<?php echo (int)$s['id']; ?>">
									<button class="btn secondary text-sm">
										<?php echo $s['is_active'] ? 'Deactivate' : 'Activate'; ?>
									</button>
								</form>
								<form method="post" style="display:inline">
									<input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
									<input type="hidden" name="action" value="delete">
									<input type="hidden" name="id" value="<?php echo (int)$s['id']; ?>">
									<button class="btn danger text-sm" onclick="return confirm('Delete this slot?')">Delete</button>
								</form>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
	</div>

	<script>
	function previewSlots() {
		const hoursPerSlot = parseFloat(document.querySelector('input[name="hours_per_slot"]').value);
		const startTime = document.querySelector('input[name="start_time"]').value;
		const endTime = document.querySelector('input[name="end_time"]').value;
		
		if (!hoursPerSlot || !startTime || !endTime) {
			alert('Please fill in all fields first');
			return;
		}
		
		if (hoursPerSlot <= 0 || hoursPerSlot > 24) {
			alert('Invalid hours per slot. Must be between 0.5 and 24 hours.');
			return;
		}
		
		// Calculate slots
		const startTimestamp = new Date(`2000-01-01T${startTime}:00`);
		let endTimestamp = new Date(`2000-01-01T${endTime}:00`);
		
		// Handle overnight slots (end time < start time means next day)
		if (endTimestamp < startTimestamp) {
			endTimestamp.setDate(endTimestamp.getDate() + 1);
		}
		
		const hoursInMs = hoursPerSlot * 60 * 60 * 1000;
		
		let currentTime = startTimestamp;
		let slots = [];
		
		while (currentTime < endTimestamp) {
			const slotStart = currentTime.toTimeString().slice(0, 5);
			const slotEnd = new Date(currentTime.getTime() + hoursInMs);
			
			if (slotEnd <= endTimestamp) {
				// Format slot end time, showing date if it's next day
				let slotEndStr = slotEnd.toTimeString().slice(0, 5);
				if (slotEnd.getDate() !== startTimestamp.getDate()) {
					slotEndStr += ' (next day)';
				}
				slots.push(`${slotStart} - ${slotEndStr} (${hoursPerSlot}h)`);
			}
			
			currentTime = slotEnd;
		}
		
		if (slots.length > 0) {
			const message = `Preview of ${slots.length} slots that will be generated:\n\n${slots.join('\n')}`;
			alert(message);
		} else {
			alert('No slots could be generated with the given parameters.');
		}
	}
	</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
