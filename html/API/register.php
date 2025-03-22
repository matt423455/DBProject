<?php
// api/register.php
header('Content-Type: application/json; charset=utf-8');

// For dev only
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/config.php'; // $conn

$input         = json_decode(file_get_contents("php://input"), true);
$username      = $input['username']      ?? '';
$email         = $input['email']         ?? '';
$password      = $input['password']      ?? '';
$university_id = $input['university_id'] ?? '';

$response = ['success' => false, 'message' => ''];

if (!$username || !$email || !$password || !$university_id) {
    $response['message'] = 'All fields (username, email, password, university_id) are required.';
    echo json_encode($response);
    exit;
}

// Check if username or email already exists
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

// Insert new user
$hashed = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO Users (username, email, password, role, university_id) 
                        VALUES (?, ?, ?, 'student', ?)");
$stmt->bind_param("sssi", $username, $email, $hashed, $university_id);

if ($stmt->execute()) {
    $response['success'] = true;
    $response['message'] = 'Registration successful.';
} else {
    $response['message'] = 'Registration failed: ' . $conn->error;
}
$stmt->close();

echo json_encode($response);
?>
