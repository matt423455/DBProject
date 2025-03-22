<?php
include 'config.php';
// UCF API URL (XML feed)
$api_url = "https://events.ucf.edu/feed.xml";

// Load the XML file
$xml = simplexml_load_file($api_url);

if ($xml === false) {
    die("Error loading XML file.");
}

// Loop through and print event details
foreach ($xml->event as $event) {
    echo "Event: " . htmlspecialchars($event->title) . "<br>";
    echo "Description: " . htmlspecialchars($event->description) . "<br>";
    echo "Date: " . htmlspecialchars($event->date) . "<br><br>";
}
?>
