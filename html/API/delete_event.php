<?php
// API/delete_event.php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Ensure only a super_admin can perform this action
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'super_admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}

require __DIR__ . '/config.php';

$input = json_decode(file_get_contents('php://input'), true);
$event_id = $input['event_id'] ?? null;

if (!$event_id) {
    echo json_encode(['success' => false, 'message' => 'Event ID is required.']);
    exit;
}

$stmt = $conn->prepare("DELETE FROM Event WHERE event_id = ?");
$stmt->bind_param("i", $event_id);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Event deleted successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error deleting event: ' . $conn->error]);
}
$stmt->close();
?>
