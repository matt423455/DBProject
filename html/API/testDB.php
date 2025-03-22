<!DOCTYPE html>
<html>
<head>
    <title>DB Connection Test</title>
</head>
<body>
<h1>Testing MySQL Connection via config.php</h1>

<?php
// testDB.php

// Include the config file (must be in the same folder or adjust the path accordingly)
require __DIR__ . '/config.php';

// If we get here, it means $conn is defined and no connect_error was found
echo "<p><strong>Connection successful!</strong></p>";
?>

</body>
</html>
