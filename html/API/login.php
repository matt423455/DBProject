<?php
// api/login.php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Show errors (for debugging in dev)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/config.php'; // $conn is here

$input = json_decode(file_get_contents('php://input'), true);
$username = $input['username'] ?? '';
$password = $input['password'] ?? '';

$response = ['success' => false, 'message' => ''];

if (!$username || !$password) {
    $response['message'] = 'Username and password required.';
    echo json_encode($response);
    exit;
}

// Prepare statement
$stmt = $conn->prepare("SELECT * FROM Users WHERE username = ? LIMIT 1");
$stmt->bind_param("s", $username);
$stmt->execute();

$result = $stmt->get_result();
$user   = $result->fetch_assoc();

if (!$user) {
    // No user found
    $response['message'] = 'No such user.';
    echo json_encode($response);
    exit;
}

// Check hashed password
if (!password_verify($password, $user['password'])) {
    $response['message'] = 'Invalid credentials.';
    echo json_encode($response);
    exit;
}

// Successful login: store info in session
$_SESSION['user_id']       = $user['user_id'];
$_SESSION['role']          = $user['role'];
$_SESSION['university_id'] = $user['university_id'];

$response['success'] = true;
$response['message'] = 'Login successful.';
echo json_encode($response);
?>
