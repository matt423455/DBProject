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
$rso_id = trim($input['rso_id'] ?? '');

if ($rso_id === '') {
  echo json_encode(['success' => false, 'message' => 'RSO ID is required.']);
  exit;
}

// Check if RSO exists and is pending (is_active = 2).
$stmt = $conn->prepare("SELECT * FROM RSO WHERE rso_id = ? AND is_active = 2");
$stmt->bind_param("i", $rso_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
  echo json_encode(['success' => false, 'message' => 'RSO not found or already approved.']);
  exit;
}
$stmt->close();

// Approve RSO by updating is_active to 1.
$stmt = $conn->prepare("UPDATE RSO SET is_active = 1 WHERE rso_id = ?");
$stmt->bind_param("i", $rso_id);
$stmt->execute();
if ($stmt->affected_rows > 0) {
  echo json_encode(['success' => true, 'message' => 'RSO approved successfully.']);
} else {
  echo json_encode(['success' => false, 'message' => 'Error approving RSO: ' . $conn->error]);
}
$stmt->close();
?>
