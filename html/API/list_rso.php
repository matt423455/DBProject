<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
  echo json_encode(['success' => false, 'message' => 'User not logged in.']);
  exit;
}

require __DIR__ . '/config.php';

$query = "SELECT rso_id, name, description, university_id, created_by, is_active FROM RSO";
$result = $conn->query($query);

$rsos = [];
if ($result) {
  while ($row = $result->fetch_assoc()) {
    $rsos[] = $row;
  }
  echo json_encode(['success' => true, 'data' => $rsos]);
} else {
  echo json_encode(['success' => false, 'message' => 'Error fetching RSOs: ' . $conn->error]);
}
?>
