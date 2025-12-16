<?php
session_start();

// --- LOAD PHPMAILER (Again, to notify you) ---
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

// --- CONFIGURATION ---
$MY_EMAIL = "danreez1307@gmail.com"; // Your email to get the alert
$SMTP_EMAIL = "danreez1307@gmail.com"; 
$SMTP_PASSWORD = "iaxy xkvo fkpk ehwo"; 

if (isset($_POST['verify_btn'])) {
    $user_code = $_POST['otp_input'];

    if ($user_code == $_SESSION['temp_otp']) {
        // --- CODE IS CORRECT! ---
        
        // 1. Send Alert to YOU
        $visitor_email = $_SESSION['temp_email'];
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
            $mail->addAddress($MY_EMAIL); // Send to YOU

            $mail->isHTML(true);
            $mail->Subject = 'Alert: Valid User Accessed Your Journey';
            $mail->Body    = "A verified visitor ($visitor_email) just logged in.";
            $mail->send();
        } catch (Exception $e) {}

        // 2. Grant Access
        $_SESSION['access_granted'] = true;
        $_SESSION['visitor'] = $visitor_email;
        
        // Clear temp session
        unset($_SESSION['temp_otp']);
        unset($_SESSION['temp_email']);

        header("Location: timeline.php");
        exit();

    } else {
        $error = "Invalid Code. Try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Email</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f4f4f4; height: 100vh; display: flex; justify-content: center; align-items: center; }
        .card { background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); text-align: center; }
        input { padding: 10px; font-size: 18px; letter-spacing: 5px; text-align: center; width: 100%; margin: 20px 0; border: 1px solid #ddd; border-radius: 8px; }
        button { background: #333; color: white; padding: 12px 20px; border: none; border-radius: 8px; cursor: pointer; width: 100%; }
        button:hover { background: #555; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Verification</h2>
        <p>We sent a code to your email.</p>
        <form method="POST">
            <input type="text" name="otp_input" placeholder="Enter 6-digit Code" required>
            <button type="submit" name="verify_btn">Verify</button>
            <?php if(isset($error)) echo "<p style='color:red; margin-top:10px;'>$error</p>"; ?>
        </form>
    </div>
</body>
</html>