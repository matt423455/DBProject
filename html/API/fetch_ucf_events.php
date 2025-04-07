<?php
// File: API/fetch_ucf_events.php
header('Content-Type: application/json');

// Enable error reporting for debugging (disable in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'config.php'; // Ensure this file sets up the $conn database connection

$feedUrl = "https://events.ucf.edu/feed.xml";
$xml = simplexml_load_file($feedUrl);

if (!$xml) {
    echo json_encode([
        "success" => false,
        "message" => "Failed to load UCF events feed."
    ]);
    exit;
}

// In your provided XML, the root element is <events> and each event is in an <event> tag.
if (!isset($xml->event)) {
    echo json_encode([
        "success" => true,
        "message" => "No events found in the UCF XML feed."
    ]);
    exit;
}

$inserted = 0;
foreach ($xml->event as $event) {
    // Extract event title; skip if missing.
    $name = trim((string)$event->title);
    if (!$name) {
        continue;
    }
    
    // Extract description. It is wrapped in CDATA; you can keep the HTML or strip tags as needed.
    $description = trim((string)$event->description);
    
    // Extract the start date/time.
    $startDateStr = trim((string)$event->start_date);
    $startTimestamp = strtotime($startDateStr);
    if ($startTimestamp === false) {
        continue; // Skip if start_date is invalid.
    }
    $date = date('Y-m-d', $startTimestamp);
    $time = date('H:i:s', $startTimestamp);
    
    // Extract category; fallback to a default value.
    $category = trim((string)$event->category);
    if (!$category) {
        $category = "UCF Feed";
    }
    
    // Extract location; if empty, default to "UCF Main Campus".
    $location = trim((string)$event->location);
    if (!$location) {
        $location = "UCF Main Campus";
    }
    
    // Extract contact email; use a default if missing.
    $contact_email = trim((string)$event->contact_email);
    if (!$contact_email) {
        $contact_email = "info@ucf.edu";
    }
    
    // Set additional fields (if needed, add more from the XML such as room or virtual_url)
    $created_by = 1;

    // Prepare and execute insert into the events table.
    $sql = "INSERT INTO Event (name, description, event_date, event_time, event_category, location, contact_email, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode([
            "success" => false,
            "message" => "Database prepare failed: " . $conn->error
        ]);
        exit;
    }
    $stmt->bind_param("sssssssi", $name, $description, $date, $time, $category, $location, $contact_email, $created_by);
    
    if ($stmt->execute()) {
        $inserted++;
    }
    $stmt->close();
}

echo json_encode([
    "success" => true,
    "message" => "$inserted events imported successfully from UCF feed.",
    "data" => [] // Optionally, you could return an array of the imported events
]);
?>
