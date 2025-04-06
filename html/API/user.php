<?php
// API/user.php
session_start();
header('Content-Type: application/json; charset=utf-8');

// (Remove in production) Display errors for debugging purposes.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if user is logged in via session
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in.'
    ]);
    exit;
}

require __DIR__ . '/config.php'; // This should establish $conn with your database

$user_id = $_SESSION['user_id'];

// Prepare a query to fetch user details
$stmt = $conn->prepare("SELECT user_id, username, email, role, university_id FROM Users WHERE user_id = ? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    echo json_encode([
        'success' => false,
        'message' => 'User not found.'
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'user' => $user
]);
?>
