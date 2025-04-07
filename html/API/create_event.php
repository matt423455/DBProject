<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
  echo json_encode(['success' => false, 'message' => 'User not logged in.']);
  exit;
}

require __DIR__ . '/config.php';

$input = json_decode(file_get_contents('php://input'), true);

// Use the same keys as sent from JavaScript
$name             = trim($input['name'] ?? '');
$event_category   = trim($input['event_category'] ?? '');
$description      = trim($input['description'] ?? '');
$event_date       = trim($input['event_date'] ?? '');
$event_time       = trim($input['event_time'] ?? '');
$location      = trim($input['location'] ?? '');
$contact_phone    = trim($input['contact_phone'] ?? '');
$contact_email    = trim($input['contact_email'] ?? '');
$event_visibility = trim($input['event_visibility'] ?? '');
$rso_id           = trim($input['rso_id'] ?? '');

// Check that all required fields are provided.
if ($name === '' || $event_category === '' || $description === '' || $event_date === '' ||
    $event_time === '' || $location === '' || $contact_phone === '' || $contact_email === '' ||
    $event_visibility === '') {
    echo json_encode(['success' => false, 'message' => 'All event fields are required.']);
    exit;
}

// For RSO events, ensure an rso_id is provided.
if ($event_visibility === 'RSO' && $rso_id === '') {
    echo json_encode(['success' => false, 'message' => 'RSO ID is required for RSO events.']);
    exit;
}

$created_by = $_SESSION['user_id'];
$approved   = 0; // New events are pending approval

// Note the format string "sssssisssiii":
$stmt = $conn->prepare("INSERT INTO Event (name, event_category, description, event_date, event_time, location, contact_phone, contact_email, event_visibility, rso_id, created_by, approved) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssisssiii", $name, $event_category, $description, $event_date, $event_time, $location, $contact_phone, $contact_email, $event_visibility, $rso_id, $created_by, $approved);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(['success' => true, 'message' => 'Event created successfully and is pending approval.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error creating event: ' . $conn->error]);
}
$stmt->close();
