<?php require_once __DIR__ . '/../config.php';
require_role(['admin','superadmin']);
$pdo = get_pdo();
$me = current_user();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    
    $customer_name = trim($_POST['customer_name'] ?? '');
    $customer_phone = trim($_POST['customer_phone'] ?? '');
    $customer_email = trim($_POST['customer_email'] ?? '');
    $ground_id = (int)($_POST['ground_id'] ?? 0);
    $play_date = $_POST['play_date'] ?? '';
    $slot_id = (int)($_POST['slot_id'] ?? 0);
    $amount = (float)($_POST['amount'] ?? 0);
    
    // Validation
    if (empty($customer_name) || empty($customer_phone) || $ground_id <= 0 || empty($play_date) || $slot_id <= 0 || $amount <= 0) {
        $error = 'All fields are required and must be valid.';
    } else {
        try {
            $pdo->beginTransaction();
            
            // Check if slot is available for the selected date
            $checkStmt = $pdo->prepare('SELECT COUNT(*) FROM bookings WHERE ground_id = ? AND slot_id = ? AND play_date = ? AND status IN ("pending", "confirmed")');
            $checkStmt->execute([$ground_id, $slot_id, $play_date]);
            $existingBookings = $checkStmt->fetchColumn();
            
            if ($existingBookings > 0) {
                throw new Exception('This slot is already booked for the selected date. Please choose a different date or time slot.');
            }
            
            // Get slot details for validation
            $slotStmt = $pdo->prepare('SELECT * FROM time_slots WHERE id = ? AND ground_id = ? AND is_active = 1');
            $slotStmt->execute([$slot_id, $ground_id]);
            $slot = $slotStmt->fetch();
            
            if (!$slot) {
                throw new Exception('Invalid time slot selected.');
            }
            
            // Get ground details
            $groundStmt = $pdo->prepare('SELECT * FROM grounds WHERE id = ? AND is_active = 1');
            $groundStmt->execute([$ground_id]);
            $ground = $groundStmt->fetch();
            
            if (!$ground) {
                throw new Exception('Invalid ground selected.');
            }
            
            // Create offline user if email provided, otherwise use a placeholder
            $user_id = null;
            if (!empty($customer_email)) {
                // Check if user already exists
                $userStmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
                $userStmt->execute([$customer_email]);
                $existingUser = $userStmt->fetch();
                
                if ($existingUser) {
                    $user_id = $existingUser['id'];
                } else {
                    // Create new user for offline booking
                    $userStmt = $pdo->prepare('INSERT INTO users (name, email, phone, password_hash, role) VALUES (?, ?, ?, ?, "user")');
                    $dummyPassword = password_hash(bin2hex(random_bytes(8)), PASSWORD_BCRYPT);
                    $userStmt->execute([$customer_name, $customer_email, $customer_phone, $dummyPassword]);
                    $user_id = $pdo->lastInsertId();
                }
            } else {
                // Create offline user without email
                $userStmt = $pdo->prepare('INSERT INTO users (name, email, phone, password_hash, role) VALUES (?, ?, ?, ?, "user")');
                $offlineEmail = 'offline_' . time() . '@' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
                $dummyPassword = password_hash(bin2hex(random_bytes(8)), PASSWORD_BCRYPT);
                $userStmt->execute([$customer_name, $offlineEmail, $customer_phone, $dummyPassword]);
                $user_id = $pdo->lastInsertId();
            }
            
            // Generate QR token
            $qr_token = bin2hex(random_bytes(16));
            
            // Create the booking
            $bookingStmt = $pdo->prepare('INSERT INTO bookings (user_id, ground_id, slot_id, play_date, total_amount, status, qr_token) VALUES (?, ?, ?, ?, ?, "confirmed", ?)');
            $bookingStmt->execute([$user_id, $ground_id, $slot_id, $play_date, $amount, $qr_token]);
            $booking_id = $pdo->lastInsertId();
            
            $pdo->commit();
            
            // Send confirmation email if email was provided
            if (!empty($customer_email)) {
                require_once __DIR__ . '/../lib/Mailer.php';
                $qrUrl = BASE_URL . '/verify_qr.php?token=' . urlencode($qr_token);
                
                // Generate PNG binary for QR
                $qrPngBinary = null;
                try {
                    $png = @file_get_contents('https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=' . urlencode($qrUrl) . '&choe=UTF-8');
                    if ($png !== false) { $qrPngBinary = $png; }
                } catch (Exception $e) { /* ignore */ }
                
                $subject = 'Offline Booking Confirmed - ' . APP_NAME;
                $body = "
                <p>Hello {$customer_name},</p>
                <p>Your offline booking has been confirmed.</p>
                <p><strong>Booking Details:</strong></p>
                <ul>
                <li>Ground: {$ground['name']}</li>
                <li>Date: {$play_date}</li>
                <li>Time: " . substr($slot['start_time'],0,5) . ' - ' . substr($slot['end_time'],0,5) . "</li>
                <li>Amount: ₹" . number_format($amount,2) . "</li>
                </ul>
                <p>Show this QR at entry:</p>
                {{QR}}
                <p>Regards,<br>" . APP_NAME . "</p>
                ";
                
                Mailer::send($customer_email, $subject, $body, $qrUrl, $qrPngBinary);
            }
            
            $message = "Offline booking created successfully! Booking ID: {$booking_id}";
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = $e->getMessage();
        }
    }
}

// Get grounds managed by this admin
if ($me['role'] === 'superadmin') {
    $groundsStmt = $pdo->prepare('SELECT id, name, location FROM grounds WHERE is_active = 1 ORDER BY name');
    $groundsStmt->execute();
} else {
    $groundsStmt = $pdo->prepare('SELECT id, name, location FROM grounds WHERE is_active = 1 AND admin_id = ? ORDER BY name');
    $groundsStmt->execute([$me['id']]);
}
$grounds = $groundsStmt->fetchAll();

// Get time slots for selected ground (will be populated via AJAX)
$timeSlots = [];

include __DIR__ . '/../partials/header.php';
?>

<h2 class="text-2xl md:text-3xl font-semibold mb-4">Add Offline Booking</h2>

<?php if ($message): ?>
    <div class="alert success mb-4"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert error mb-4"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="card max-w-2xl">
    <form method="post" class="space-y-4">
        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
        
        <div class="grid gap-4 grid-cols-1 md:grid-cols-2">
            <div class="field">
                <label for="customer_name">Customer Name *</label>
                <input type="text" id="customer_name" name="customer_name" required 
                       value="<?php echo htmlspecialchars($_POST['customer_name'] ?? ''); ?>">
            </div>
            
            <div class="field">
                <label for="customer_phone">Customer Phone *</label>
                <input type="tel" id="customer_phone" name="customer_phone" required 
                       value="<?php echo htmlspecialchars($_POST['customer_phone'] ?? ''); ?>">
            </div>
        </div>
        
        <div class="field">
            <label for="customer_email">Customer Email</label>
            <input type="email" id="customer_email" name="customer_email" 
                   value="<?php echo htmlspecialchars($_POST['customer_email'] ?? ''); ?>"
                   placeholder="Enter Customer Email" required>
            <small class="text-slate-600">Customer will receive confirmation email</small>
        </div>
        
        <div class="grid gap-4 grid-cols-1 md:grid-cols-2">
            <div class="field">
                <label for="ground_id">Ground *</label>
                <select id="ground_id" name="ground_id" required onchange="loadTimeSlots()">
                    <option value="">Select Ground</option>
                    <?php foreach ($grounds as $ground): ?>
                        <option value="<?php echo $ground['id']; ?>" 
                                <?php echo ($_POST['ground_id'] ?? '') == $ground['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($ground['name']); ?> 
                            (<?php echo htmlspecialchars($ground['location']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="field">
                <label for="play_date">Play Date *</label>
                <input type="date" id="play_date" name="play_date" required 
                       min="<?php echo date('Y-m-d'); ?>"
                       value="<?php echo htmlspecialchars($_POST['play_date'] ?? ''); ?>">
            </div>
        </div>
        
        <div class="field">
            <label for="slot_id">Time Slot *</label>
            <select id="slot_id" name="slot_id" required>
                <option value="">Select Ground and Date first</option>
            </select>
        </div>
        
        <div class="field">
            <label for="amount">Amount (₹) *</label>
            <input type="number" id="amount" name="amount" step="0.01" min="0" required 
                   value="<?php echo htmlspecialchars($_POST['amount'] ?? ''); ?>">
        </div>
        
        <button type="submit" class="btn w-full">Create Offline Booking</button>
    </form>
</div>


<script>
function loadTimeSlots() {
    const groundId = document.getElementById('ground_id').value;
    const playDate = document.getElementById('play_date').value;
    const slotSelect = document.getElementById('slot_id');
    
    if (!groundId || !playDate) {
        slotSelect.innerHTML = '<option value="">Select Ground and Date first</option>';
        return;
    }
    
    // Clear current options
    slotSelect.innerHTML = '<option value="">Loading...</option>';
    
    // Fetch available time slots
    fetch('get_available_slots.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `ground_id=${groundId}&play_date=${playDate}&csrf_token=<?php echo csrf_token(); ?>`
    })
    .then(response => response.json())
    .then(data => {
        slotSelect.innerHTML = '<option value="">Select Time Slot</option>';
        
        if (data.success && data.slots.length > 0) {
            data.slots.forEach(slot => {
                const option = document.createElement('option');
                option.value = slot.id;
                option.textContent = `${slot.start_time} - ${slot.end_time} (${slot.hours_per_slot}h) - ₹${slot.price}`;
                slotSelect.appendChild(option);
            });
        } else {
            slotSelect.innerHTML = '<option value="">No available slots for this date</option>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        slotSelect.innerHTML = '<option value="">Error loading slots</option>';
    });
}

// Auto-load slots when date changes
document.getElementById('play_date').addEventListener('change', loadTimeSlots);
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
