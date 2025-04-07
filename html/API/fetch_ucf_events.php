<?php
header('Content-Type: application/json');
include 'config.php';

// Load the XML feed
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

    // Date and time parsing
    $startDateTime = strtotime((string)$event->start);
    $date = date('Y-m-d', $startDateTime);
    $time = date('H:i:s', $startDateTime);

    // Fill in other required/default values
    $category = "UCF Feed";
    $location = (string)$event->location ?: "UCF Main Campus";
    $contact = "info@ucf.edu";
    $created_by = null;
    $university_id = null;

    // Prepare and insert into 'events' table
    $sql = "INSERT INTO events (name, description, event_date, event_time, event_category, location_name, contact_email, created_by, university_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssi", $name, $description, $date, $time, $category, $location, $contact, $created_by, $university_id);
    
    if ($stmt->execute()) {
        $inserted++;
    }
}

echo json_encode([
    "success" => true,
    "message" => "$inserted events imported successfully from UCF feed."
]);
?>
