<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/Mailer.php';

// Test email functionality
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_type = $_POST['email_type'] ?? '';
    $to_email = $_POST['to_email'] ?? '';
    
    if ($email_type && $to_email) {
        $success = false;
        
        switch ($email_type) {
            case 'registration':
                $key = '7Sw1JyR1PjilFtmgV6Q5sMDF';
                $qrUrl = BASE_URL . '/verify_qr.php?token=' . urlencode($key);
                
                $subject = 'Thanks for registering';
                $html = '
                <p>Hi Dev Tanna,</p>
                <p>Thank you for registering. Your unique QR key is below. Please keep it safe and present it when collecting swags.</p>
                {{QR}}
                <p style="margin-top:12px;color:#555">If the image doesn\'t load, your key is:<br>
                <strong style="font-family:monospace">'.$key.'</strong></p>
                <p>— GSA MU</p>
                ';
                
                $success = Mailer::send($to_email, $subject, $html, $qrUrl);
                break;
                
            case 'booking_confirmed':
                $qrUrl = BASE_URL . '/verify_qr.php?token=' . urlencode('test123');
                
                $subject = 'Booking Confirmed - ' . APP_NAME;
                $html = '
                <p>Hello Test User,</p>
                <p>Your booking is confirmed.</p>
                <p><strong>Booking Details:</strong></p>
                <ul>
                <li>Ground: Test Ground</li>
                <li>Date: 2024-01-15</li>
                <li>Time: 14:00 - 16:00</li>
                <li>Amount: ₹500.00</li>
                </ul>
                <p>Show this QR at entry:</p>
                {{QR}}
                <p>Regards,<br>' . APP_NAME . '</p>
                ';
                
                $success = Mailer::send($to_email, $subject, $html, $qrUrl);
                break;
                
            case 'booking_cancelled':
                $subject = 'Booking Cancelled - ' . APP_NAME;
                $html = '
                <p>Hello Test User,</p>
                <p>Your booking has been cancelled successfully.</p>
                <p><strong>Booking Details:</strong></p>
                <ul>
                <li>Ground: Test Ground</li>
                <li>Date: 2024-01-15</li>
                <li>Time: 14:00 - 16:00</li>
                <li>Amount: ₹500.00</li>
                </ul>
                <p>If you have any questions, please contact us.</p>
                <p>Regards,<br>' . APP_NAME . '</p>
                ';
                
                $success = Mailer::send($to_email, $subject, $html);
                break;
                
            case 'booking_rejected':
                $subject = 'Booking Request Rejected - ' . APP_NAME;
                $html = '
                <p>Hello Test User,</p>
                <p>We regret to inform you that your booking request has been rejected.</p>
                <p><strong>Booking Details:</strong></p>
                <ul>
                <li>Ground: Test Ground</li>
                <li>Date: 2024-01-15</li>
                <li>Time: 14:00 - 16:00</li>
                <li>Amount: ₹500.00</li>
                </ul>
                <p>If you have any questions about this decision, please contact us.</p>
                <p>Regards,<br>' . APP_NAME . '</p>
                ';
                
                $success = Mailer::send($to_email, $subject, $html);
                break;
        }
        
        if ($success) {
            echo '<div style="color: green; margin: 10px 0;">✓ Email sent successfully!</div>';
        } else {
            echo '<div style="color: red; margin: 10px 0;">✗ Failed to send email</div>';
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Test Email System</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .form-group { margin: 15px 0; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select { padding: 8px; width: 300px; }
        button { padding: 10px 20px; background: #007cba; color: white; border: none; cursor: pointer; }
        button:hover { background: #005a87; }
        .info { background: #f0f8ff; padding: 15px; border-radius: 5px; margin: 20px 0; }
    </style>
</head>
<body>
    <h1>Test Email System</h1>
    
    <div class="info">
        <h3>What this tests:</h3>
        <ul>
            <li><strong>Registration Email:</strong> Welcome email with QR code (like your screenshot)</li>
            <li><strong>Booking Confirmed:</strong> Confirmation email with QR code</li>
            <li><strong>Booking Cancelled:</strong> User cancellation notification</li>
            <li><strong>Booking Rejected:</strong> Admin rejection notification</li>
        </ul>
    </div>
    
    <form method="post">
        <div class="form-group">
            <label for="email_type">Email Type:</label>
            <select name="email_type" id="email_type" required>
                <option value="">Select email type...</option>
                <option value="registration">Registration Email (with QR)</option>
                <option value="booking_confirmed">Booking Confirmed (with QR)</option>
                <option value="booking_cancelled">Booking Cancelled</option>
                <option value="booking_rejected">Booking Rejected</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="to_email">Send to Email:</label>
            <input type="email" name="to_email" id="to_email" placeholder="your@email.com" required>
        </div>
        
        <button type="submit">Send Test Email</button>
    </form>
    
    <div class="info">
        <h3>Email Features Implemented:</h3>
        <ul>
            <li>✓ User cancels booking → User gets email + Admin gets notification</li>
            <li>✓ Admin cancels booking → User gets cancellation email</li>
            <li>✓ Admin rejects pending booking → User gets rejection email</li>
            <li>✓ Admin approves booking → User gets confirmation with QR</li>
            <li>✓ QR codes embedded in emails with fallback text</li>
            <li>✓ Professional HTML email templates</li>
        </ul>
    </div>
</body>
</html>
