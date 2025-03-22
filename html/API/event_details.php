<?php
// event_detail.php
header('Content-Type: application/json; charset=utf-8');
session_start();
require __DIR__ . '/config.php'; // Adjust path if needed

$event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;
if (!$event_id) {
    echo json_encode(['success' => false, 'message' => 'No event ID provided.']);
    exit;
}

// Prepare statement to fetch event details with location info
$stmt = $conn->prepare("
    SELECT e.*, l.name AS location_name 
    FROM Event e 
    LEFT JOIN Location l ON e.location_id = l.location_id 
    WHERE e.event_id = ? AND e.approved = 1
    LIMIT 1
");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();
$stmt->close();

if (!$event) {
    echo json_encode(['success' => false, 'message' => 'Event not found.']);
    exit;
}

echo json_encode(['success' => true, 'data' => $event]);
?>
