<?php
session_start();
require 'config.php';   // now config.php has $conn = new mysqli(...)

$inputData = json_decode(file_get_contents("php://input"), true);

$username = trim($inputData['username'] ?? '');
$password = trim($inputData['password'] ?? '');

$response = [ 'success' => false, 'message' => '' ];

if (!$username || !$password) {
    $response['message'] = 'Username and password required.';
    echo json_encode($response);
    exit;
}

try {
    // Using MySQLi prepared statements:
    $stmt = $conn->prepare("SELECT * FROM Users WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $user   = $result->fetch_assoc();

    if ($user) {
        // Verify hashed password
        if (password_verify($password, $user['password'])) {
            // Store session data
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

} catch (Exception $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);
