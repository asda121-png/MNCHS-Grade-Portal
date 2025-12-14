<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../../PHPMailer-master/src/PHPMailer.php';
require '../../PHPMailer-master/src/SMTP.php';
require '../../PHPMailer-master/src/Exception.php';

function sendStudentAccountEmail($to, $username, $password) {
    $mail = new PHPMailer(true);
    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // Set your SMTP server
        $mail->SMTPAuth   = true; 
        $mail->Username   = 'mnchsgradeportal@gmail.com'; // <-- REPLACE with your Gmail address
        $mail->Password   = 'abcd efgh ijkl mnop'; // <-- REPLACE with your 16-character App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        //Recipients
        $mail->setFrom('mnchsgradeportal@gmail.com', 'MNCHS Grade Portal'); // <-- REPLACE with your Gmail address
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your MNCHS Student Account Credentials';
        $mail->Body    = "<h3>Welcome to MNCHS Grade Portal!</h3>"
            . "<p>Your account has been created. Here are your login credentials:</p>"
            . "<ul>"
            . "<li><strong>Username:</strong> $username</li>"
            . "<li><strong>Password:</strong> $password</li>"
            . "</ul>"
            . "<p>Please log in and change your password as soon as possible.</p>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Student account email could not be sent. Mailer Error: ' . $mail->ErrorInfo);
        return false;
    }
}
