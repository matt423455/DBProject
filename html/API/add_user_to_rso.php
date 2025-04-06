<?php
// API/add_user_to_rso.php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Only allow super_admins
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'super_admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}

require __DIR__ . '/config.php';

$input = json_decode(file_get_contents('php://input'), true);
$rso_id = $input['rso_id'] ?? null;
$user_id = $input['user_id'] ?? null;

if (!$rso_id || !$user_id) {
    echo json_encode(['success' => false, 'message' => 'RSO ID and User ID are required.']);
    exit;
}

// Insert the user into the RSO_Members table
$stmt = $conn->prepare("INSERT INTO RSO_Members (rso_id, user_id) VALUES (?, ?)");
$stmt->bind_param("ii", $rso_id, $user_id);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'User added to RSO successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error adding user to RSO: ' . $conn->error]);
}
$stmt->close();
?>
