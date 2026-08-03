<?php

require 'vendor/autoload.php';
require 'admin/config/db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

$phaseInput = isset($_REQUEST['phase']) ? trim($_REQUEST['phase']) : 'Phase 1';
$phaseMap = [
    'phase1' => 'Phase 1',
    'p1' => 'Phase 1',
    'phase2' => 'Phase 2',
    'p2' => 'Phase 2',
    'phase3' => 'Phase 3',
    'p3' => 'Phase 3',
    'phase4' => 'Phase 4',
    'p4' => 'Phase 4',
    'phase5' => 'Phase 5',
    'p5' => 'Phase 5',
];
$phase = $phaseMap[strtolower($phaseInput)] ?? $phaseInput;

try {
    $gmailUsername = getenv('GMAIL_USERNAME') ?: 'jayrseedentario@gmail.com';
    $gmailAppPassword = getenv('GMAIL_APP_PASSWORD') ?: 'lvtjgvtcqpffpdck';

    // SMTP Configuration
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = $gmailUsername;
    $mail->Password   = $gmailAppPassword;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->SMTPAutoTLS = true;

    // Sender
    $mail->setFrom($gmailUsername, 'Flood Alert System');
    $mail->addReplyTo($gmailUsername, 'Flood Alert System');

    $stmt = $conn->prepare("SELECT email FROM subscribers WHERE phase = ? ORDER BY id");
    $stmt->bind_param('s', $phase);
    $stmt->execute();
    $result = $stmt->get_result();

    $recipients = [];
    while ($row = $result->fetch_assoc()) {
        $recipients[] = $row['email'];
    }

    if (empty($recipients)) {
        echo "No subscribers found for $phase.";
        exit;
    }

    foreach ($recipients as $email) {
        $mail->clearAddresses();
        $mail->clearReplyTos();
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = "Possible Flood Alert - $phase";
        $mail->Body    = "<h2>Possible Flood Warning</h2><p>A possible flood event has been detected in the $phase area. Please stay alert, prepare an emergency kit, and follow local authorities for updates.</p><p><strong>Stay safe.</strong></p>";

        $mail->send();
    }

    $redirectMessage = 'Alert sent successfully to ' . count($recipients) . ' subscriber(s) in ' . $phase . '.';
    header('Location: admin/index.php?status=success&message=' . urlencode($redirectMessage));
    exit;

} catch (Exception $e) {
    $errorMessage = $e->getMessage();
    if (!empty($mail->ErrorInfo)) {
        $errorMessage .= ' SMTP Error: ' . $mail->ErrorInfo;
    }
    header('Location: admin/index.php?status=error&message=' . urlencode($errorMessage));
    exit;
}