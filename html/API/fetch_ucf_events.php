<?php
include 'config.php';

// UCF API URL (XML feed)
$api_url = "https://events.ucf.edu/feed.xml";

// Load the XML file
$xml = simplexml_load_file($api_url);

if ($xml === false) {
    die("Error loading XML file.");
}

// Loop through each event and insert it into the database
foreach ($xml->event as $event) {
    $name = (string) $event->title;
    $description = (string) $event->description;
    $date = (string) $event->date;

    $sql = "INSERT INTO events (name, description, date) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $name, $description, $date);
    $stmt->execute();
}

echo "UCF events imported successfully!";
?>
