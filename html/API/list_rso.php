<?php
// API/list_rso.php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Optionally, you can require the user to be logged in (or even a super_admin)
// For this example, we allow any logged-in user to see the list
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

require __DIR__ . '/config.php';

$query = "SELECT rso_id, name, description, university_id, created_by, is_active FROM RSO";
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
