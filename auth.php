<?php
session_start();

// --- LOAD PHPMAILER ---
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

// --- CONFIGURATION ---
$MY_BIRTHDAY = "2005-07-13"; 
$MY_EMAIL = "danreez1307@gmail.com"; // THIS IS THE CREATOR EMAIL

// --- GMAIL SMTP SETTINGS ---
$SMTP_EMAIL = "danreez1307@gmail.com"; 
$SMTP_PASSWORD = "iaxy xkvo fkpk ehwo"; 

// Get data
$visitor_email = $_POST['visitor_email'];
$passcode = $_POST['passcode'];

// 1. Check Birthday
if ($passcode === $MY_BIRTHDAY) {

    // --- CREATOR BYPASS LOGIC (NEW) ---
    // If the email entered matches YOUR email, skip all verifications.
    if ($visitor_email === $MY_EMAIL) {
        $_SESSION['access_granted'] = true;
        $_SESSION['visitor'] = "The Creator"; // Special tag for you
        
        header("Location: timeline.php");
        exit();
    }
    // ----------------------------------

    // 2. Generate 6-Digit OTP for EVERYONE ELSE
    $otp_code = rand(100000, 999999);
    $_SESSION['temp_otp'] = $otp_code;
    $_SESSION['temp_email'] = $visitor_email;

    // 3. Send OTP via Email
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $SMTP_EMAIL;
        $mail->Password   = $SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom($SMTP_EMAIL, 'My Journey Archive');
        $mail->addAddress($visitor_email);

        $mail->isHTML(true);
        $mail->Subject = 'Your Access Code';
        $mail->Body    = "Your access code is: <b>$otp_code</b>";

        $mail->send();
        
        header("Location: verify.php");
        exit();

    } catch (Exception $e) {
        echo "Error sending email. Please try again.";
    }

} else {
    echo "<script>alert('Incorrect birthday.'); window.location.href='index.php';</script>";
}
?>