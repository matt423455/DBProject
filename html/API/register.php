<?php
// api/register.php
header('Content-Type: application/json; charset=utf-8');

// (Remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/config.php'; // includes $conn

$input         = json_decode(file_get_contents("php://input"), true);
$username      = $input['username']      ?? '';
$email         = $input['email']         ?? '';
$password      = $input['password']      ?? '';
$university_id = $input['university_id'] ?? '';

$response = ['success' => false, 'message' => ''];

// Basic validation
if (!$username || !$email || !$password) {
    $response['message'] = 'username, email, and password are required.';
    echo json_encode($response);
    exit;
}

// Check if username or email already taken
$stmtCheck = $conn->prepare("SELECT user_id FROM Users WHERE username = ? OR email = ?");
$stmtCheck->bind_param("ss", $username, $email);
$stmtCheck->execute();
$resCheck = $stmtCheck->get_result();
if ($resCheck->num_rows > 0) {
    $response['message'] = 'Username or Email already in use.';
    echo json_encode($response);
    exit;
}
$stmtCheck->close();

// Hash the password
$hashedPass = password_hash($password, PASSWORD_DEFAULT);

// Insert new user
$stmt = $conn->prepare("INSERT INTO Users (username, email, password, role, university_id)
                        VALUES (?, ?, ?, 'student', ?)");
$stmt->bind_param("sssi", $username, $email, $hashedPass, $university_id);

if ($stmt->execute()) {
    $response['success'] = true;
    $response['message'] = 'Registration successful.';
} else {
    $response['message'] = 'Registration failed: ' . $conn->error;
}
$stmt->close();

echo json_encode($response);
?>
