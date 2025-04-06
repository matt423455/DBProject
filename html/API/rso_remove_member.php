<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
  echo json_encode(['success' => false, 'message' => 'User not logged in.']);
  exit;
}

require __DIR__ . '/config.php';

$input = json_decode(file_get_contents('php://input'), true);
$rso_id = trim($input['rso_id'] ?? '');
$user_id = trim($input['user_id'] ?? '');

if ($rso_id === '' || $user_id === '') {
  echo json_encode(['success' => false, 'message' => 'RSO ID and User ID are required.']);
  exit;
}

$stmt = $conn->prepare("DELETE FROM RSO_Members WHERE rso_id = ? AND user_id = ?");
$stmt->bind_param("ii", $rso_id, $user_id);
$stmt->execute();
if ($stmt->affected_rows > 0) {
  echo json_encode(['success' => true, 'message' => 'Member removed successfully.']);
} else {
  echo json_encode(['success' => false, 'message' => 'Error removing member or member not found.']);
}
$stmt->close();
