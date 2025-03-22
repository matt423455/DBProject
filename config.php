<?php
// Prevent direct access to this file
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    http_response_code(403);
    exit("Access denied.");
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "myDB";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
