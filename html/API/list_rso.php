<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
  echo json_encode(['success' => false, 'message' => 'User not logged in.']);
  exit;
}

require __DIR__ . '/config.php';

// SQL query that joins the RSO table with RSO_Members to count members.
// It groups by rso_id and filters out RSOs with fewer than 5 members.
$query = "SELECT 
            r.rso_id, 
            r.name, 
            r.description, 
            r.university_id, 
            r.created_by, 
            r.is_active,
            COUNT(m.user_id) AS memberCount
          FROM RSO r
          LEFT JOIN RSO_Members m ON r.rso_id = m.rso_id
          GROUP BY r.rso_id
          HAVING memberCount >= 5";

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
