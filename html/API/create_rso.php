<?php
// API/create_rso.php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Ensure only a super_admin can perform this action
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'super_admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}

require __DIR__ . '/config.php';

$input = json_decode(file_get_contents('php://input'), true);
$name = $input['name'] ?? '';
$description = $input['description'] ?? '';
$university_id = $input['university_id'] ?? 0;

if (!$name || !$description || !$university_id) {
    echo json_encode(['success' => false, 'message' => 'Name, description, and university ID are required.']);
    exit;
}

$created_by = $_SESSION['user_id'];

// Insert the new RSO record
$stmt = $conn->prepare("INSERT INTO RSO (name, description, university_id, created_by, is_active) VALUES (?, ?, ?, ?, 1)");
$stmt->bind_param("ssii", $name, $description, $university_id, $created_by);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'RSO created successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error creating RSO: ' . $conn->error]);
}
$stmt->close();
?>
