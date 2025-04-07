<?php
// File: API/getUniversities.php
header('Content-Type: application/json');
include 'config.php'; // Adjust path as necessary

$query = "SELECT university_id, name FROM University";
$result = $conn->query($query);
$universities = [];
while ($row = $result->fetch_assoc()) {
    $universities[] = $row;
}
echo json_encode(["success" => true, "data" => $universities]);
?>
