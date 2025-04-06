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

if ($rso_id === '') {
  echo json_encode(['success' => false, 'message' => 'RSO ID is required.']);
  exit;
}

// Check that the RSO exists and is approved (is_active = 1).
$stmt = $conn->prepare("SELECT * FROM RSO WHERE rso_id = ? AND (is_active = 1 OR is_active = 0)");
$stmt->bind_param("i", $rso_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
  echo json_encode(['success' => false, 'message' => 'RSO not found or not approved.']);
  exit;
}
$stmt->close();

$user_id = $_SESSION['user_id'];
// Check if already a member.
$stmt = $conn->prepare("SELECT * FROM RSO_Members WHERE rso_id = ? AND user_id = ?");
$stmt->bind_param("ii", $rso_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
  echo json_encode(['success' => false, 'message' => 'You are already a member of this RSO.']);
  exit;
}
$stmt->close();

// Insert membership.
$stmt = $conn->prepare("INSERT INTO RSO_Members (rso_id, user_id) VALUES (?, ?)");
$stmt->bind_param("ii", $rso_id, $user_id);
$stmt->execute();
if ($stmt->affected_rows > 0) {
  echo json_encode(['success' => true, 'message' => 'Joined the RSO successfully.']);
} else {
  echo json_encode(['success' => false, 'message' => 'Error joining RSO: ' . $conn->error]);
}
$stmt->close();
?>
