<?php
// API/getLocations.php
header('Content-Type: application/json');

// Enable error reporting temporarily for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'config.php';

$query = "SELECT location_id, name FROM Location";
$result = $conn->query($query);

$locations = [];
if ($result) {
    while ($row = $result->fetch_assoc()){
        $locations[] = $row;
    }
} else {
    // In case the query fails
    echo json_encode(["success" => false, "message" => "Query error: " . $conn->error]);
    exit;
}

echo json_encode(["success" => true, "data" => $locations]);
