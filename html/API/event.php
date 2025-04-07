<?php
// event.php
header('Content-Type: application/json; charset=utf-8');
session_start();
require __DIR__ . '/config.php'; // Adjust path if necessary

// Retrieve all approved events, ordered by date (ascending)
// No JOIN on the Location table
$sql = "SELECT * FROM Event WHERE approved = 1 ORDER BY event_date ASC";

$result = $conn->query($sql);

if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Query failed: ' . $conn->error]);
    exit;
}

$events = $result->fetch_all(MYSQLI_ASSOC);
echo json_encode(['success' => true, 'data' => $events]);
?>
