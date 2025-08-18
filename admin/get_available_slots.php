<?php
require_once __DIR__ . '/../config.php';
require_role(['admin','superadmin']);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

verify_csrf();

$ground_id = (int)($_POST['ground_id'] ?? 0);
$play_date = $_POST['play_date'] ?? '';

if ($ground_id <= 0 || empty($play_date)) {
    echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
    exit;
}

try {
    $pdo = get_pdo();
    $me = current_user();
    
    // Ensure admin can only access their own grounds (unless superadmin)
    $groundCheck = $pdo->prepare('SELECT id FROM grounds WHERE id = ? AND is_active = 1 AND (? = "superadmin" OR admin_id = ?)');
    $groundCheck->execute([$ground_id, $me['role'], $me['id']]);
    if (!$groundCheck->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Not authorized to access this ground']);
        exit;
    }
    
    // Get all time slots for the ground
    $slotsStmt = $pdo->prepare('
        SELECT ts.id, ts.start_time, ts.end_time, ts.hours_per_slot, g.price_per_hour,
               (g.price_per_hour * ts.hours_per_slot) as total_price
        FROM time_slots ts
        JOIN grounds g ON ts.ground_id = g.id
        WHERE ts.ground_id = ? AND ts.is_active = 1 AND g.is_active = 1
        ORDER BY ts.start_time
    ');
    $slotsStmt->execute([$ground_id]);
    $allSlots = $slotsStmt->fetchAll();
    
    // Get booked slots for the specific date
    $bookedStmt = $pdo->prepare('
        SELECT slot_id FROM bookings 
        WHERE ground_id = ? AND play_date = ? AND status IN ("pending", "confirmed")
    ');
    $bookedStmt->execute([$ground_id, $play_date]);
    $bookedSlotIds = $bookedStmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Filter out booked slots
    $availableSlots = [];
    foreach ($allSlots as $slot) {
        if (!in_array($slot['id'], $bookedSlotIds)) {
            $availableSlots[] = [
                'id' => $slot['id'],
                'start_time' => substr($slot['start_time'], 0, 5),
                'end_time' => substr($slot['end_time'], 0, 5),
                'hours_per_slot' => $slot['hours_per_slot'],
                'price' => number_format($slot['total_price'], 2)
            ];
        }
    }
    
    echo json_encode([
        'success' => true,
        'slots' => $availableSlots,
        'total_available' => count($availableSlots)
    ]);
    
} catch (Exception $e) {
    error_log('Error in get_available_slots: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Server error occurred']);
}
?>
