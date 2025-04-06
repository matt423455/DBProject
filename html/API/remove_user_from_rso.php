<?php
// API/remove_user_from_rso.php
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

$stmt = $conn->prepare("DELETE FROM RSO_Members WHERE rso_id = ? AND user_id = ?");
$stmt->bind_param("ii", $rso_id, $user_id);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'User removed from RSO successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error removing user from RSO: ' . $conn->error]);
}
$stmt->close();
?>
