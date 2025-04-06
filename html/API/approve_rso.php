<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Only allow super_admins (or the appropriate admin role) to approve RSO requests.
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'super_admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}

require __DIR__ . '/config.php';

$input = json_decode(file_get_contents('php://input'), true);
$rso_id = trim($input['rso_id'] ?? '');
if ($rso_id === '') {
    echo json_encode(['success' => false, 'message' => 'RSO ID is required.']);
    exit;
}

// Check if the RSO exists and is pending (is_active = 2).
$stmt = $conn->prepare("SELECT * FROM RSO WHERE rso_id = ? AND is_active = 2");
$stmt->bind_param("i", $rso_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'RSO not found or already approved.']);
    exit;
}
$rso = $result->fetch_assoc();
$stmt->close();

$approved_by = $_SESSION['user_id']; // The admin who is approving.
$created_by = $rso['created_by'];     // The user who submitted the request.

// Approve the RSO by setting is_active = 1 and updating approved_by.
$stmt = $conn->prepare("UPDATE RSO SET is_active = 1, approved_by = ? WHERE rso_id = ?");
$stmt->bind_param("ii", $approved_by, $rso_id);
$stmt->execute();
if ($stmt->affected_rows > 0) {
    // Auto-add the creator to RSO_Members as "leader".
    // First check if they are already a member.
    $stmt_check = $conn->prepare("SELECT * FROM RSO_Members WHERE rso_id = ? AND user_id = ?");
    $stmt_check->bind_param("ii", $rso_id, $created_by);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    if ($result_check->num_rows == 0) {
        $role = "leader";
        $stmt_insert = $conn->prepare("INSERT INTO RSO_Members (rso_id, user_id, role) VALUES (?, ?, ?)");
        $stmt_insert->bind_param("iis", $rso_id, $created_by, $role);
        $stmt_insert->execute();
        $stmt_insert->close();
    } else {
        // If already a member, update their role to leader.
        $role = "leader";
        $stmt_update = $conn->prepare("UPDATE RSO_Members SET role = ? WHERE rso_id = ? AND user_id = ?");
        $stmt_update->bind_param("sii", $role, $rso_id, $created_by);
        $stmt_update->execute();
        $stmt_update->close();
    }
    echo json_encode(['success' => true, 'message' => 'RSO approved successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error approving RSO: ' . $conn->error]);
}
$stmt->close();
?>
