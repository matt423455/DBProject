<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Allow admins and super_admins.
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin','super_admin'])) {
    echo json_encode(['success'=> false, 'message'=>'Unauthorized.']);
    exit;
}

require __DIR__ . '/config.php';

$input = json_decode(file_get_contents('php://input'), true);
$rso_id = trim($input['rso_id'] ?? '');
$user_id = trim($input['user_id'] ?? '');

if ($rso_id === '' || $user_id === '') {
    echo json_encode(['success'=> false, 'message'=>'RSO ID and User ID are required.']);
    exit;
}

// (Optional) Check if the user exists.
$stmt = $conn->prepare("SELECT * FROM Users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(['success'=> false, 'message'=>'User does not exist.']);
    exit;
}
$stmt->close();

// Check if membership already exists.
$stmt = $conn->prepare("SELECT * FROM RSO_Members WHERE rso_id = ? AND user_id = ?");
$stmt->bind_param("ii", $rso_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    echo json_encode(['success'=> false, 'message'=>'User is already a member of this RSO.']);
    exit;
}
$stmt->close();

// Insert membership.
$stmt = $conn->prepare("INSERT INTO RSO_Members (rso_id, user_id) VALUES (?, ?)");
$stmt->bind_param("ii", $rso_id, $user_id);
$stmt->execute();
if ($stmt->affected_rows > 0) {
    echo json_encode(['success'=> true, 'message'=>'User added to RSO successfully.']);
} else {
    echo json_encode(['success'=> false, 'message'=>'Error adding user to RSO: ' . $conn->error]);
}
$stmt->close();
?>
