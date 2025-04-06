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

$stmt = $conn->prepare("SELECT rm.user_id, u.username, rm.role 
                        FROM RSO_Members rm 
                        JOIN Users u ON rm.user_id = u.user_id 
                        WHERE rm.rso_id = ?");
$stmt->bind_param("i", $rso_id);
$stmt->execute();
$result = $stmt->get_result();
$members = [];
while ($row = $result->fetch_assoc()) {
    $members[] = $row;
}
$stmt->close();

echo json_encode(['success' => true, 'members' => $members]);
