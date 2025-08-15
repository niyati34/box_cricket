<?php
require_once __DIR__ . '/../config.php';

class Mailer {
	public static function send(string $to, string $subject, string $htmlBody, string $qrUrl = '', ?string $qrPngBinary = null): bool {
		try {
			if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
				return self::sendWithPHPMailer($to, $subject, $htmlBody, $qrUrl, $qrPngBinary);
			}
			return self::sendWithMail($to, $subject, $htmlBody, $qrUrl);
		} catch (\Throwable $e) {
			error_log('Mailer error: ' . $e->getMessage());
			return false;
		}
	}

	private static function sendWithPHPMailer(string $to, string $subject, string $htmlBody, string $qrUrl = '', ?string $qrPngBinary = null): bool {
		$mail = new \PHPMailer\PHPMailer\PHPMailer(true);
		
		try {
			$mail->isSMTP();
			$mail->Host = 'smtp.gmail.com';
			$mail->Port = 587;
			$mail->SMTPAuth = true;
			$mail->Username = 'boxpro.rajkot@gmail.com';
			$mail->Password = 'ruld mqlu mwkz bumg';
			$mail->SMTPSecure = 'tls';
			$mail->CharSet = 'UTF-8';
			$mail->SMTPDebug = 0;
			$mail->Debugoutput = 'error_log';
			
			$mail->setFrom($mail->Username, APP_NAME);
			$mail->addAddress($to);
			
			$mail->isHTML(true);
			$mail->Subject = $subject;
			
			$qrImg = '';
			if ($qrPngBinary) {
				$cid = 'qrimg' . bin2hex(random_bytes(4));
				$mail->addStringEmbeddedImage($qrPngBinary, $cid, 'qr.png', 'base64', 'image/png');
				$mail->addStringAttachment($qrPngBinary, 'booking-qr.png', 'base64', 'image/png');
				$qrImg = '<p><strong>Your Entry QR:</strong></p><img alt="QR Code" src="cid:' . $cid . '" style="width:180px;height:180px;" />';
			} elseif ($qrUrl) {
				$qrPngUrl = 'https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=' . urlencode($qrUrl) . '&choe=UTF-8';
				$png = @file_get_contents($qrPngUrl);
				if ($png !== false) {
					$cid = 'qrimg' . bin2hex(random_bytes(4));
					$mail->addStringEmbeddedImage($png, $cid, 'qr.png', 'base64', 'image/png');
					$mail->addStringAttachment($png, 'booking-qr.png', 'base64', 'image/png');
					$qrImg = '<p><strong>Your Entry QR:</strong></p><img alt="QR Code" src="cid:' . $cid . '" style="width:180px;height:180px;" />';
				} else {
					$qrImg = '<p><strong>Your Entry QR (open link):</strong><br><a href="' . htmlspecialchars($qrUrl) . '">' . htmlspecialchars($qrUrl) . '</a></p>';
				}
			}
			
			// Allow callers to control QR placement using a {{QR}} placeholder; otherwise append at the end
			$composed = $htmlBody;
			if (strpos($composed, '{{QR}}') !== false) {
				$composed = str_replace('{{QR}}', $qrImg, $composed);
			} else {
				$composed .= $qrImg;
			}
			$body = '<div style="font-family:Inter,Arial,sans-serif;line-height:1.5">' . $composed . '</div>';
			$mail->Body = $body;
			$mail->AltBody = strip_tags($htmlBody . ($qrUrl ? "\nQR: $qrUrl" : ''));
			
			$mail->send();
			return true;
		} catch (\Throwable $e) {
			error_log('PHPMailer error: ' . $mail->ErrorInfo);
			return false;
		}
	}

	private static function sendWithMail(string $to, string $subject, string $htmlBody, string $qrUrl = ''): bool {
		$headers = "MIME-Version: 1.0\r\n" .
			"Content-type:text/html;charset=UTF-8\r\n" .
			'From: ' . APP_NAME . " <no-reply@" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ">\r\n";

		$qrImg = '';
		if ($qrUrl) {
			$qrImgSrc = 'https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=' . urlencode($qrUrl) . '&choe=UTF-8';
			$qrImg = '<p><strong>Your Entry QR:</strong></p><img alt="QR Code" src="' . htmlspecialchars($qrImgSrc) . '" style="width:180px;height:180px;" />';
		}

		// Allow callers to control QR placement using a {{QR}} placeholder; otherwise append at the end
		$composed = $htmlBody;
		if (strpos($composed, '{{QR}}') !== false) {
			$composed = str_replace('{{QR}}', $qrImg, $composed);
		} else {
			$composed .= $qrImg;
		}
		$body = '<div style="font-family:Inter,Arial,sans-serif;line-height:1.5">' . $composed . '</div>';
		return @mail($to, $subject, $body, $headers);
	}
}
