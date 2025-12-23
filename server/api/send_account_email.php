<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// These paths must be correct relative to the file that includes this one.
require_once __DIR__ . '/../../PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/../../PHPMailer-master/src/SMTP.php';
require_once __DIR__ . '/../../PHPMailer-master/src/Exception.php';

/**
 * Sends an account creation email with login credentials.
 *
 * @param string $to The recipient's email address.
 * @param string $firstName The recipient's first name.
 * @param string $username The new username.
 * @param string $password The new temporary password.
 * @param string $userType The type of user (e.g., 'Student', 'Teacher').
 * @return bool True on success, false on failure.
 */
function sendAccountEmail(string $to, string $firstName, string $username, string $password, string $userType = 'User'): bool {
    $mail = new PHPMailer(true);
    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // Your SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username   = 'labradoriiichristian@gmail.com'; // Your Gmail address
        $mail->Password   = 'pdha rcub sobo tpws'; // Your 16-character App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        //Recipients
        $mail->setFrom('labradoriiichristian@gmail.com', 'MNCHS Grade Portal');
        $mail->addAddress($to, $firstName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = "Your MNCHS {$userType} Account Credentials";
        $mail->Body    = "<h3>Welcome to the MNCHS Grade Portal, {$firstName}!</h3><p>Your {$userType} account has been created. Here are your login credentials:</p><ul><li><strong>Username:</strong> {$username}</li><li><strong>Password:</strong> {$password}</li></ul><p>Please log in and change your password as soon as possible for security.</p><p>Thank you!</p>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Account email could not be sent to {$to}. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}