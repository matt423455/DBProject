<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
  echo json_encode(['success' => false, 'message' => 'User not logged in.']);
  exit;
}

require __DIR__ . '/config.php';

$input = json_decode(file_get_contents('php://input'), true);
$rso_id     = trim($input['rso_id'] ?? '');
$title      = trim($input['title'] ?? '');
$details    = trim($input['details'] ?? '');
$event_date = trim($input['event_date'] ?? '');
$event_time = trim($input['event_time'] ?? '');

if ($rso_id === '' || $title === '' || $details === '' || $event_date === '' || $event_time === '') {
  echo json_encode(['success' => false, 'message' => 'All event fields are required.']);
  exit;
}

$created_by = $_SESSION['user_id'];
$stmt = $conn->prepare("INSERT INTO Event (rso_id, title, details, event_date, event_time, created_by) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("issssi", $rso_id, $title, $details, $event_date, $event_time, $created_by);
$stmt->execute();
if ($stmt->affected_rows > 0) {
  echo json_encode(['success' => true, 'message' => 'Event created successfully.']);
} else {
  echo json_encode(['success' => false, 'message' => 'Error creating event: ' . $conn->error]);
}
$stmt->close();
