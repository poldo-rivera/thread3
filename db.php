<?php
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'threads_clone4';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to handle emoji and special characters
$conn->set_charset("utf8mb4");