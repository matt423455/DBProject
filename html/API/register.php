<?php
// register.php
require 'API/config.php';

// Expecting JSON POST data
$inputData = json_decode(file_get_contents("php://input"), true);

$username = trim($inputData['username'] ?? '');
$email    = trim($inputData['email'] ?? '');
$password = trim($inputData['password'] ?? '');
$universityId = intval($inputData['university_id'] ?? 0);

$response = [
    'success' => false,
    'message' => ''
];

// Validate
if (!$username || !$email || !$password || !$universityId) {
    $response['message'] = 'All fields are required.';
    echo json_encode($response);
    exit;
}

// Hash password
$hashedPass = password_hash($password, PASSWORD_BCRYPT);

// Attempt to insert into DB
try {
    // Check if username or email already exists
    $checkStmt = $pdo->prepare("SELECT user_id FROM Users WHERE username = :uname OR email = :email LIMIT 1");
    $checkStmt->execute([':uname' => $username, ':email' => $email]);
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        $response['message'] = 'Username or email already taken.';
        echo json_encode($response);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO Users (username, email, password, role, university_id)
        VALUES (:uname, :email, :pass, 'student', :uni)
    ");
    $stmt->execute([
        ':uname' => $username,
        ':email' => $email,
        ':pass'  => $hashedPass,
        ':uni'   => $universityId
    ]);

    $response['success'] = true;
    $response['message'] = 'Registration complete.';
} catch (PDOException $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);
