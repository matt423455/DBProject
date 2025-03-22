<?php
session_start();
require __DIR__ . '/../config.php'; // Ensure correct path

// Debug: turn on error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$inputData = json_decode(file_get_contents("php://input"), true);

$username = trim($inputData['username'] ?? '');
$password = trim($inputData['password'] ?? '');

$response = [
    'success' => false,
    'message' => ''
];

if (!$username || !$password) {
    $response['message'] = 'Username and password required.';
    echo json_encode($response);
    exit;
}

// Remove the try/catch for debugging
$stmt = $conn->prepare("SELECT * FROM Users WHERE username = ? LIMIT 1");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user) {
    // Verify hashed password
    if (password_verify($password, $user['password'])) {
        $_SESSION['user_id']  = $user['user_id'];
        $_SESSION['role']     = $user['role'];
        $_SESSION['username'] = $user['username'];

        $response['success'] = true;
        $response['message'] = 'Login successful.';
    } else {
        $response['message'] = 'Invalid credentials.';
    }
} else {
    $response['message'] = 'No such user.';
}

echo json_encode($response);
