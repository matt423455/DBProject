// config.php
session_start();

$DB_HOST = 'localhost';         // or '127.0.0.1'
$DB_NAME = 'college_events';    // the DB you created or already have
$DB_USER = 'myuser';            // the user you created or found
$DB_PASS = 'mySecretPass!';     // the password for that user

try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error connecting to database: " . $e->getMessage());
}
?>