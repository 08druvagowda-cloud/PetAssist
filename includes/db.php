<?php
/**
 * PetAssist — Database Connection
 * Uses MySQLi for secure database operations
 */

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'petassist';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die('<div style="text-align:center;padding:50px;font-family:Inter,sans-serif;">
        <h2 style="color:#FF6B6B;">Database Connection Failed</h2>
        <p>Error: ' . $conn->connect_error . '</p>
        <p>Please check your MySQL server and database configuration.</p>
    </div>');
}

$conn->set_charset("utf8mb4");
?>
