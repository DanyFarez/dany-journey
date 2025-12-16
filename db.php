<?php
$servername = "sql202.infinityfree.com";
$username = "if0_40685957"; // Default XAMPP username
$password = "050713050039"; // Default XAMPP password
$dbname = "if0_40685957_my_journey";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>