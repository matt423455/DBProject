<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Allow both admins and super_admins.
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'super_admin'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}

require __DIR__ . '/config.php';

$input = json_decode(file_get_contents('php://input'), true);
$event_id = trim($input['event_id'] ?? '');

if ($event_id === '') {
    echo json_encode(['success' => false, 'message' => 'Event ID is required.']);
    exit;
}

// Check if event exists.
$stmt = $conn->prepare("SELECT * FROM Event WHERE event_id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Event not found or already deleted.']);
    exit;
}
$stmt->close();

// Delete event.
$stmt = $conn->prepare("DELETE FROM Event WHERE event_id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
if ($stmt->affected_rows > 0) {
    echo json_encode(['success' => true, 'message' => 'Event deleted successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error deleting event: ' . $conn->error]);
}
$stmt->close();
?>
