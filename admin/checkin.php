<?php require_once __DIR__ . '/../config.php';
require_role(['admin','superadmin']);
$pdo = get_pdo();
$me = current_user();

$message = '';
$error = '';
$search_results = [];
$search_performed = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    
    if (isset($_POST['action']) && $_POST['action'] === 'checkin') {
        $booking_id = (int)($_POST['booking_id'] ?? 0);
        
        if ($booking_id > 0) {
            try {
                $pdo->beginTransaction();
                
                // Get booking details and ensure it belongs to admin's ground
                $bookingStmt = $pdo->prepare('
                    SELECT b.*, u.name AS user_name, g.name AS ground_name, g.admin_id, t.start_time, t.end_time 
                    FROM bookings b 
                    JOIN users u ON b.user_id = u.id 
                    JOIN grounds g ON b.ground_id = g.id 
                    JOIN time_slots t ON b.slot_id = t.id 
                    WHERE b.id = ? AND b.status = "confirmed" AND b.checked_in_at IS NULL
                    AND (? = "superadmin" OR g.admin_id = ?)
                ');
                $bookingStmt->execute([$booking_id, $me['role'], $me['id']]);
                $booking = $bookingStmt->fetch();
                
                if (!$booking) {
                    throw new Exception('Invalid booking or already checked in');
                }
                
                // Check if it's the correct date
                $today = date('Y-m-d');
                if ($booking['play_date'] !== $today) {
                    throw new Exception('Cannot check in for date: ' . $booking['play_date'] . '. Today is: ' . $today);
                }
                
                // Update check-in time
                $updateStmt = $pdo->prepare('UPDATE bookings SET checked_in_at = NOW() WHERE id = ?');
                $updateStmt->execute([$booking_id]);
                
                $pdo->commit();
                
                $message = "Successfully checked in {$booking['user_name']} for {$booking['ground_name']} at " . 
                          substr($booking['start_time'], 0, 5) . ' - ' . substr($booking['end_time'], 0, 5);
                
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = $e->getMessage();
            }
        }
    } elseif (isset($_POST['search_name'])) {
        $search_name = trim($_POST['search_name']);
        
        if (!empty($search_name)) {
            $search_performed = true;
            
            // Search for bookings by user name
            $searchWhere = $me['role'] === 'superadmin' ? '' : 'AND g.admin_id = ?';
            $searchStmt = $pdo->prepare('
                SELECT b.*, u.name AS user_name, u.phone, g.name AS ground_name, t.start_time, t.end_time, t.hours_per_slot
                FROM bookings b 
                JOIN users u ON b.user_id = u.id 
                JOIN grounds g ON b.ground_id = g.id 
                JOIN time_slots t ON b.slot_id = t.id 
                WHERE b.status = "confirmed" 
                AND (u.name LIKE ? OR u.phone LIKE ?)
                ' . $searchWhere . '
                ORDER BY b.play_date DESC, t.start_time ASC
                LIMIT 20
            ');
            
            $search_term = '%' . $search_name . '%';
            if ($me['role'] === 'superadmin') {
                $searchStmt->execute([$search_term, $search_term]);
            } else {
                $searchStmt->execute([$search_term, $search_term, $me['id']]);
            }
            
            $search_results = $searchStmt->fetchAll();
        }
    }
}

include __DIR__ . '/../partials/header.php';
?>

<h2 class="text-2xl md:text-3xl font-semibold mb-4">Check-in Users</h2>

<?php if ($message): ?>
    <div class="alert success mb-4"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert error mb-4"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<!-- Search Form -->
<div class="card max-w-2xl mb-6">
    <h3 class="text-lg font-semibold mb-3">Search by Name or Phone</h3>
    <form method="post" class="space-y-4">
        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
        
        <div class="grid gap-4 grid-cols-1 md:grid-cols-2">
            <div class="field">
                <label for="search_name">Search Name/Phone</label>
                <input type="text" id="search_name" name="search_name" 
                       value="<?php echo htmlspecialchars($_POST['search_name'] ?? ''); ?>"
                       placeholder="Enter customer name or phone number" required>
            </div>
            <div class="field">
                <label>&nbsp;</label>
                <button type="submit" class="btn w-full">Search</button>
            </div>
        </div>
    </form>
</div>

<!-- Search Results -->
<?php if ($search_performed): ?>
    <div class="card">
        <h3 class="text-lg font-semibold mb-4">
            Search Results for "<?php echo htmlspecialchars($_POST['search_name']); ?>"
            (<?php echo count($search_results); ?> found)
        </h3>
        
        <?php if (empty($search_results)): ?>
            <p class="text-slate-500">No bookings found for this search term.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Phone</th>
                            <th>Ground</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($search_results as $booking): ?>
                            <tr>
                                <td class="font-medium"><?php echo htmlspecialchars($booking['user_name']); ?></td>
                                <td><?php echo htmlspecialchars($booking['phone']); ?></td>
                                <td><?php echo htmlspecialchars($booking['ground_name']); ?></td>
                                <td>
                                    <?php 
                                    $play_date = $booking['play_date'];
                                    $today = date('Y-m-d');
                                    $date_class = $play_date === $today ? 'text-green-600 font-semibold' : '';
                                    echo '<span class="' . $date_class . '">' . htmlspecialchars($play_date) . '</span>';
                                    if ($play_date === $today) echo ' (Today)';
                                    ?>
                                </td>
                                <td><?php echo substr($booking['start_time'], 0, 5) . ' - ' . substr($booking['end_time'], 0, 5); ?></td>
                                <td>
                                    <?php if ($booking['checked_in_at']): ?>
                                        <span class="badge success">Checked In</span>
                                        <br><small class="text-slate-500">
                                            <?php echo date('M j, g:i A', strtotime($booking['checked_in_at'])); ?>
                                        </small>
                                    <?php else: ?>
                                        <span class="badge pending">Not Checked In</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!$booking['checked_in_at'] && $booking['play_date'] === date('Y-m-d')): ?>
                                        <form method="post" style="display:inline">
                                            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                                            <input type="hidden" name="action" value="checkin">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                            <button type="submit" class="btn btn-sm">Check In</button>
                                        </form>
                                    <?php elseif ($booking['checked_in_at']): ?>
                                        <span class="text-green-600 text-sm">✓ Done</span>
                                    <?php elseif ($booking['play_date'] !== date('Y-m-d')): ?>
                                        <span class="text-slate-400 text-sm">Not today</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<!-- Quick Check-in for Today's Bookings -->
<div class="card mt-6">
    <h3 class="text-lg font-semibold mb-4">Today's Bookings - Quick Check-in</h3>
    
    <?php
    $today = date('Y-m-d');
    $todayWhere = $me['role'] === 'superadmin' ? '' : 'AND g.admin_id = ?';
    if ($me['role'] === 'superadmin') {
        $todayStmt = $pdo->prepare('
            SELECT b.*, u.name AS user_name, u.phone, g.name AS ground_name, t.start_time, t.end_time, t.hours_per_slot
            FROM bookings b 
            JOIN users u ON b.user_id = u.id 
            JOIN grounds g ON b.ground_id = g.id 
            JOIN time_slots t ON b.slot_id = t.id 
            WHERE b.status = "confirmed" AND b.play_date = ?
            ORDER BY t.start_time ASC
        ');
        $todayStmt->execute([$today]);
    } else {
        $todayStmt = $pdo->prepare('
            SELECT b.*, u.name AS user_name, u.phone, g.name AS ground_name, t.start_time, t.end_time, t.hours_per_slot
            FROM bookings b 
            JOIN users u ON b.user_id = u.id 
            JOIN grounds g ON b.ground_id = g.id 
            JOIN time_slots t ON b.slot_id = t.id 
            WHERE b.status = "confirmed" AND b.play_date = ? AND g.admin_id = ?
            ORDER BY t.start_time ASC
        ');
        $todayStmt->execute([$today, $me['id']]);
    }
    
    $todayBookings = $todayStmt->fetchAll();
    ?>
    
    <?php if (empty($todayBookings)): ?>
        <p class="text-slate-500">No confirmed bookings for today.</p>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Ground</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($todayBookings as $booking): ?>
                        <tr>
                            <td class="font-medium"><?php echo htmlspecialchars($booking['user_name']); ?></td>
                            <td><?php echo htmlspecialchars($booking['phone']); ?></td>
                            <td><?php echo htmlspecialchars($booking['ground_name']); ?></td>
                            <td><?php echo substr($booking['start_time'], 0, 5) . ' - ' . substr($booking['end_time'], 0, 5); ?></td>
                            <td>
                                <?php if ($booking['checked_in_at']): ?>
                                    <span class="badge success">Checked In</span>
                                    <br><small class="text-slate-500">
                                        <?php echo date('g:i A', strtotime($booking['checked_in_at'])); ?>
                                    </small>
                                <?php else: ?>
                                    <span class="badge pending">Not Checked In</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!$booking['checked_in_at']): ?>
                                    <form method="post" style="display:inline">
                                        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                                        <input type="hidden" name="action" value="checkin">
                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                        <button type="submit" class="btn btn-sm">Check In</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-green-600 text-sm">✓ Done</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Recent Check-ins -->
<div class="card mt-6">
    <h3 class="text-lg font-semibold mb-4">Recent Check-ins (Last 24 Hours)</h3>
    
    <?php
    if ($me['role'] === 'superadmin') {
        $recentStmt = $pdo->prepare('
            SELECT b.*, u.name AS user_name, g.name AS ground_name, t.start_time, t.end_time
            FROM bookings b 
            JOIN users u ON b.user_id = u.id 
            JOIN grounds g ON b.ground_id = g.id 
            JOIN time_slots t ON b.slot_id = t.id 
            WHERE b.checked_in_at IS NOT NULL 
            AND b.checked_in_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            ORDER BY b.checked_in_at DESC
            LIMIT 10
        ');
        $recentStmt->execute();
    } else {
        $recentStmt = $pdo->prepare('
            SELECT b.*, u.name AS user_name, g.name AS ground_name, t.start_time, t.end_time
            FROM bookings b 
            JOIN users u ON b.user_id = u.id 
            JOIN grounds g ON b.ground_id = g.id 
            JOIN time_slots t ON b.slot_id = t.id 
            WHERE b.checked_in_at IS NOT NULL 
            AND b.checked_in_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            AND g.admin_id = ?
            ORDER BY b.checked_in_at DESC
            LIMIT 10
        ');
        $recentStmt->execute([$me['id']]);
    }
    
    $recentCheckins = $recentStmt->fetchAll();
    ?>
    
    <?php if (empty($recentCheckins)): ?>
        <p class="text-slate-500">No recent check-ins.</p>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Ground</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Checked In At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentCheckins as $checkin): ?>
                        <tr>
                            <td class="font-medium"><?php echo htmlspecialchars($checkin['user_name']); ?></td>
                            <td><?php echo htmlspecialchars($checkin['ground_name']); ?></td>
                            <td><?php echo htmlspecialchars($checkin['play_date']); ?></td>
                            <td><?php echo substr($checkin['start_time'], 0, 5) . ' - ' . substr($checkin['end_time'], 0, 5); ?></td>
                            <td>
                                <span class="text-green-600">
                                    <?php echo date('M j, g:i A', strtotime($checkin['checked_in_at'])); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
