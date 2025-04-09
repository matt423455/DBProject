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
    if (filter_var($email, FILTER_VALIDATE_EMAIL) && str_ends_with($email, '@ucf.edu')) {
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
if ($stmt->affected_rows > 0) {
    echo json_encode(['success' => true, 'message' => 'RSO created successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error creating RSO: ' . $conn->error]);
}
$stmt->close();
?>
