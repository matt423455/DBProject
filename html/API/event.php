<?php
// event.php
header('Content-Type: application/json; charset=utf-8');
session_start();
require __DIR__ . '/config.php'; // Adjust path if necessary

// Retrieve all approved events, ordered by date (ascending)
$sql = "SELECT e.*, l.name AS location_name 
        FROM Event e 
        LEFT JOIN Location l ON e.location = l.name 
        WHERE e.approved = 1 
        ORDER BY e.event_date ASC";
        
$result = $conn->query($sql);

if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Query failed: ' . $conn->error]);
    exit;
}

$events = $result->fetch_all(MYSQLI_ASSOC);
echo json_encode(['success' => true, 'data' => $events]);
?>
