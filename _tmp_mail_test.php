<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/mailer.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = MAIL_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = MAIL_USERNAME;
    $mail->Password = MAIL_PASSWORD;
    $mail->SMTPSecure = MAIL_ENCRYPTION;
    $mail->Port = MAIL_PORT;

    $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
    $mail->addAddress(MAIL_FROM);
    $mail->isHTML(true);
    $mail->Subject = 'HR2 OTP Test';
    $mail->Body = '<p>OTP test email</p>';
    $mail->AltBody = 'OTP test email';

    $mail->send();
    echo "OK\n";
} catch (Exception $e) {
    echo "ERR: " . $mail->ErrorInfo . "\n";
}
?>
