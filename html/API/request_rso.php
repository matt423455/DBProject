<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Disable error display (for production)
ini_set('display_errors', 0);
error_reporting(0);

if (!isset($_SESSION['user_id'])) {
  echo json_encode(['success' => false, 'message' => 'User not logged in.']);
  exit;
}

require __DIR__ . '/config.php';

// Read the raw input.
$rawInput = file_get_contents('php://input');
error_log("Raw input in request_rso.php: " . $rawInput);

// Try to decode JSON.
$input = json_decode($rawInput, true);
if (!is_array($input) || empty($input)) {
  error_log("JSON decoding failed; falling back to \$_POST.");
  $input = $_POST;
}
error_log("Decoded input: " . var_export($input, true));

// Trim and extract the fields.
$name = trim($input['name'] ?? '');
$description = trim($input['description'] ?? '');
$university_id = trim($input['university_id'] ?? '');

if ($name === '' || $description === '' || $university_id === '') {
  echo json_encode(['success' => false, 'message' => 'Name, description, and university ID are required.']);
  exit;
}

// Check if an RSO with this name already exists.
$stmt = $conn->prepare("SELECT * FROM RSO WHERE name = ?");
$stmt->bind_param("s", $name);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
  echo json_encode(['success' => false, 'message' => 'RSO already exists.']);
  exit;
}
$stmt->close();

$created_by = $_SESSION['user_id'];
$is_active = 2; // Pending

$stmt = $conn->prepare("INSERT INTO RSO (name, description, university_id, created_by, is_active) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("ssiii", $name, $description, $university_id, $created_by, $is_active);
$stmt->execute();
if ($stmt->affected_rows > 0) {
  echo json_encode(['success' => true, 'message' => 'RSO request submitted successfully.']);
} else {
  echo json_encode(['success' => false, 'message' => 'Error submitting RSO request: ' . $conn->error]);
}
$stmt->close();
// Notice: No closing PHP tag to prevent accidental output.
