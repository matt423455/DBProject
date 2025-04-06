<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Only allow if the user is logged in.
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

require __DIR__ . '/config.php';

$query = "SELECT event_id, name, event_category, description, event_date, event_time, location_id, contact_phone, contact_email, event_visibility, rso_id, created_by, approved FROM Event";
$result = $conn->query($query);

$events = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
    echo json_encode(['success' => true, 'data' => $events]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error fetching events: ' . $conn->error]);
}
