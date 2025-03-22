<?php
// login.php
require 'config.php';

// Expecting JSON POST data
$inputData = json_decode(file_get_contents("php://input"), true);

$username = trim($inputData['username'] ?? '');
$password = trim($inputData['password'] ?? '');

$response = [
    'success' => false,
    'message' => ''
];

// Quick validation
if (!$username || !$password) {
    $response['message'] = 'Username and password required.';
    echo json_encode($response);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM Users WHERE username = :uname LIMIT 1");
    $stmt->execute([':uname' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Verify hashed password
        if (password_verify($password, $user['password'])) {
            // Store session data
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role']    = $user['role'];
            $_SESSION['username']= $user['username'];

            $response['success'] = true;
            $response['message'] = 'Login successful.';
        } else {
            $response['message'] = 'Invalid credentials.';
        }
    } else {
        $response['message'] = 'No such user.';
    }
} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);
