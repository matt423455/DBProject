<?php
// File: API/fetch_ucf_events.php
header('Content-Type: application/json');

// Enable error reporting for debugging (disable in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'config.php'; // Ensure this path is correct

// Load the XML feed from UCF
$feedUrl = "https://events.ucf.edu/feed.xml";
$xml = simplexml_load_file($feedUrl);

// Handle error if feed is unavailable
if (!$xml) {
    echo json_encode(["success" => false, "message" => "Failed to load UCF events feed."]);
    exit;
}

$inserted = 0;
foreach ($xml->channel->item as $event) {
    $name = (string)$event->title;
    $description = (string)$event->description;

    // Ensure the event start exists and is valid
    $start = (string)$event->start;
    if (!$start) {
        // Skip this event if no start time is provided
        continue;
    }
    $startDateTime = strtotime($start);
    if ($startDateTime === false) {
        // Skip events with invalid start time format
        continue;
    }
    $date = date('Y-m-d', $startDateTime);
    $time = date('H:i:s', $startDateTime);

    // Fill in other required/default values
    $category = "UCF Feed";
    // Use event location if available; otherwise default to "UCF Main Campus"
    $location = trim((string)$event->location);
    if (!$location) {
        $location = "UCF Main Campus";
    }
    $contact = "info@ucf.edu";
    $created_by = "";
    $university_id = 2;  // UCF's university id

    // Prepare and insert into 'events' table
    $sql = "INSERT INTO events (name, description, event_date, event_time, event_category, location_name, contact_email, created_by, university_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode([
            "success" => false,
            "message" => "Database prepare failed: " . $conn->error
        ]);
        exit;
    }
    $stmt->bind_param("ssssssssi", $name, $description, $date, $time, $category, $location, $contact, $created_by, $university_id);
    
    if ($stmt->execute()) {
        $inserted++;
    } else {
        // Optionally log error or include details in the response for debugging:
        // error_log("Database execute failed: " . $stmt->error);
    }
    $stmt->close();
}

echo json_encode([
    "success" => true,
    "message" => "$inserted events imported successfully from UCF feed."
]);
?>
