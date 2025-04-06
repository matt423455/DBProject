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
$user_id = trim($input['user_id'] ?? '');
$new_role = trim($input['new_role'] ?? '');

if ($user_id === '' || $new_role === '') {
    echo json_encode(['success' => false, 'message' => 'User ID and new role are required.']);
    exit;
}

$allowed_roles = ['admin', 'super_admin'];
if (!in_array($new_role, $allowed_roles)) {
    echo json_encode(['success' => false, 'message' => 'Invalid role specified.']);
    exit;
}

// Check current role.
$stmt = $conn->prepare("SELECT role FROM Users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'User not found.']);
    exit;
}
$row = $result->fetch_assoc();
if ($row['role'] === $new_role) {
    echo json_encode(['success' => false, 'message' => 'User is already ' . $new_role . '.']);
    exit;
}
$stmt->close();

// Update role.
$stmt = $conn->prepare("UPDATE Users SET role = ? WHERE user_id = ?");
$stmt->bind_param("si", $new_role, $user_id);
$stmt->execute();
if ($stmt->affected_rows > 0) {
    echo json_encode(['success' => true, 'message' => 'User role updated successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'No changes made or error updating user role: ' . $conn->error]);
}
$stmt->close();
?>
