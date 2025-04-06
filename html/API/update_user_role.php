<?php
// API/update_user_role.php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Ensure only a super_admin can perform this action
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'super_admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}

require __DIR__ . '/config.php'; // This file should establish $conn

$input = json_decode(file_get_contents('php://input'), true);
$user_id = $input['user_id'] ?? null;
$new_role = $input['new_role'] ?? '';

if (!$user_id || !$new_role) {
    echo json_encode(['success' => false, 'message' => 'User ID and new role are required.']);
    exit;
}

// Validate new role (allowed values: 'admin' or 'super_admin')
$allowed_roles = ['admin', 'super_admin'];
if (!in_array($new_role, $allowed_roles)) {
    echo json_encode(['success' => false, 'message' => 'Invalid role specified.']);
    exit;
}

$stmt = $conn->prepare("UPDATE Users SET role = ? WHERE user_id = ?");
$stmt->bind_param("si", $new_role, $user_id);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'User role updated successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error updating user role: ' . $conn->error]);
}
$stmt->close();
?>
