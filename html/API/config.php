$db_host     = "localhost";  // or "127.0.0.1"
$db_user     = "appuser";    // the username you used: "appuser"
$db_password = "yourStrongPassword";  // replace with the actual password
$db_name     = "college_events";  // replace with the actual database name

// Create connection using MySQLi
$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}