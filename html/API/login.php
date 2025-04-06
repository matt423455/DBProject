<?php
// api/login.php
session_start();
header('Content-Type: application/json; charset=utf-8');

// (Remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/config.php'; // includes $conn

$input = json_decode(file_get_contents('php://input'), true);
$username = $input['username'] ?? '';
$password = $input['password'] ?? '';

$response = ['success' => false, 'message' => ''];

// Basic validation
if (!$username || !$password) {
    $response['message'] = 'Username and password required.';
    echo json_encode($response);
    exit;
}

// Check if user exists
$stmt = $conn->prepare("SELECT * FROM Users WHERE username = ? LIMIT 1");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user   = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    $response['message'] = 'No such user.';
    echo json_encode($response);
    exit;
}

// Verify hashed password
if (!password_verify($password, $user['password'])) {
    $response['message'] = 'Invalid credentials.';
    echo json_encode($response);
    exit;
}

// If correct, store session data
$_SESSION['username']      = $user['username'];
$_SESSION['user_id']       = $user['user_id'];
$_SESSION['role']          = $user['role'];
$_SESSION['university_id'] = $user['university_id'];

$response['success'] = true;
$response['message'] = 'Login successful.';
echo json_encode($response);
?>
