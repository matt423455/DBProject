<?php
// File: API/fetch_ucf_events.php
header('Content-Type: application/json');

// Enable error reporting for debugging (disable in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'config.php'; // Ensure this is correct

$feedUrl = "https://events.ucf.edu/feed.xml";
$xml = simplexml_load_file($feedUrl);

if (!$xml) {
    echo json_encode([
        "success" => false,
        "message" => "Failed to load UCF events feed."
    ]);
    exit;
}

// Try to support both RSS and Atom feeds
$events = [];

// First, check if it's an RSS feed with channel->item
if (isset($xml->channel->item)) {
    $events = $xml->channel->item;
} elseif (isset($xml->entry)) { // Check for Atom feed entries
    $events = $xml->entry;
}

// If no events were found, output an appropriate message.
if (empty($events) || count($events) == 0) {
    echo json_encode([
        "success" => true,
        "message" => "No events found in the UCF XML feed."
    ]);
    exit;
}

$inserted = 0;

foreach ($events as $event) {
    // Get the event title. For both RSS and Atom, <title> is expected.
    $name = (string)$event->title;
    if (!$name) continue; // Skip if there's no title

    // For the description, try multiple possible tags.
    if (isset($event->description)) {
         $description = (string)$event->description;
    } elseif (isset($event->summary)) {
         $description = (string)$event->summary;
    } elseif (isset($event->content)) {
         $description = (string)$event->content;
    } else {
         $description = "";
    }

    // Try to get the start time. RSS feeds may have a <start> tag,
    // while Atom feeds might use <published> or <updated>.
    $start = (string)$event->start;
    if (!$start) {
        if (isset($event->published)) {
            $start = (string)$event->published;
        } elseif (isset($event->updated)) {
            $start = (string)$event->updated;
        } else {
            // If there's no recognizable start time, skip this event.
            continue;
        }
    }
    
    $startDateTime = strtotime($start);
    if ($startDateTime === false) continue;
    $date = date('Y-m-d', $startDateTime);
    $time = date('H:i:s', $startDateTime);

    // Default values for remaining fields
    $category = "UCF Feed";
    // Use the provided location if available, or default to "UCF Main Campus"
    $location = trim((string)$event->location) ?: "UCF Main Campus";
    $contact = "info@ucf.edu";
    $created_by = "";
    $university_id = 2;  // UCF's university id

    // Prepare SQL to insert the event
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
    }
    $stmt->close();
}

echo json_encode([
    "success" => true,
    "message" => "$inserted events imported successfully from UCF feed.",
    "data" => [] // You could optionally return an array of imported events here.
]);
?>
