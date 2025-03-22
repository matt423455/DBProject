<?php
header('Content-Type: application/json');
include 'config.php';

$sql = "SELECT name, description, date FROM events ORDER BY date ASC";
$result = $conn->query($sql);

$events = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
}

echo json_encode($events);
?>
