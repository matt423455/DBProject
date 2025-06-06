<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Only allow super_admins.
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'super_admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}

require __DIR__ . '/config.php';

$input = json_decode(file_get_contents('php://input'), true);
$name = trim($input['name'] ?? '');
$description = trim($input['description'] ?? '');
$university_id = trim($input['university_id'] ?? '');
$members = $input['members'] ?? [];  // expecting an array of emails

if ($name === '' || $description === '' || $university_id === '') {
    echo json_encode(['success' => false, 'message' => 'Name, description, and university ID are required.']);
    exit;
}

// Validate member emails
$valid_members = [];
foreach ($members as $email) {
    $email = trim($email);
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $valid_members[] = $email;
    }
}

if (count($valid_members) < 5) {
    echo json_encode(['success' => false, 'message' => 'At least 5 valid @ucf.edu emails are required to activate an RSO.']);
    exit;
}

// Check if RSO name already exists
$stmt = $conn->prepare("SELECT * FROM RSO WHERE name = ?");
$stmt->bind_param("s", $name);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'RSO already exists.']);
    exit;
}
$stmt->close();

$created_by = $_SESSION['user_id'];

// Insert new RSO
$stmt = $conn->prepare("INSERT INTO RSO (name, description, university_id, created_by, is_active) VALUES (?, ?, ?, ?, 1)");
$stmt->bind_param("ssii", $name, $description, $university_id, $created_by);
$stmt->execute();
if ($stmt->affected_rows <= 0) {
    echo json_encode(['success' => false, 'message' => 'Error creating RSO: ' . $conn->error]);
    exit;
}
$rso_id = $stmt->insert_id;
$stmt->close();

// For each valid email, ensure that the user exists in the Users table and then add them to RSO_Members.
foreach ($valid_members as $email) {
    // Check if a user with this email already exists.
    $stmt = $conn->prepare("SELECT user_id FROM Users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        // Retrieve the existing user's ID.
        $stmt->bind_result($user_id);
        $stmt->fetch();
        $stmt->close();
    } else {
        $stmt->close();
        // Create a new user.
        $username = uniqid('user_'); // Generate a random username.
        $password = 'test';      // Plain text password as per instructions.
        $hashedPass = password_hash($password, PASSWORD_DEFAULT);
        $role = 'student';
        $user_university_id = 1;       // Hardcoded university id as instructed.
        
        $stmt = $conn->prepare("INSERT INTO Users (username, email, password, role, university_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $username, $email, $hashedPass, $role, $user_university_id);
        $stmt->execute();
        if ($stmt->affected_rows <= 0) {
            // If the user insert fails, skip adding this member.
            $stmt->close();
            continue;
        }
        $user_id = $stmt->insert_id;
        $stmt->close();
    }
    
    // Insert the user into the RSO_Members table.
    $rso_role = 'member';
    $stmt = $conn->prepare("INSERT INTO RSO_Members (rso_id, user_id, role) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $rso_id, $user_id, $rso_role);
    $stmt->execute();
    $stmt->close();
}

echo json_encode(['success' => true, 'message' => 'RSO created successfully with members.']);
$conn->close();
?>
