<?php
// checkMembership.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

// Include your database connection file.
// It should create and assign a MySQLi connection to the $conn variable.
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if user_id and rso_id were sent in the POST request
    if (isset($_POST['user_id']) && isset($_POST['rso_id'])) {
        // Cast parameters to integer to ensure correct type
        $user_id = (int) $_POST['user_id'];
        $rso_id = (int) $_POST['rso_id'];

        // Prepare the SQL statement to check for membership
        $stmt = $conn->prepare("SELECT * FROM RSO_Members WHERE user_id = ? AND rso_id = ?");
        if (!$stmt) {
            echo json_encode([
                'success' => false,
                'message' => 'Database error: ' . $conn->error
            ]);
            exit;
        }
        
        // Bind the user-provided values to the prepared statement as integers
        $stmt->bind_param("ii", $user_id, $rso_id);
        $stmt->execute();
        
        // Get the result set from the statement
        if (method_exists($stmt, 'get_result')) {
            $result = $stmt->get_result();
            $inRSO = ($result->num_rows > 0);
        } else 
        {
            // Fallback method using store_result() and num_rows
            $stmt->store_result();
            $inRSO = ($stmt->num_rows > 0);
        }

        // Check if any row was returned
        if ($result->num_rows > 0) {
            // User is a member of the given RSO
            echo json_encode([
                'success' => true,
                'in_rso' => true
            ]);
        } else {
            // User is not a member of the given RSO
            echo json_encode([
                'success' => true,
                'in_rso' => false
            ]);
        }
        
        $stmt->close();
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Missing parameters: user_id and rso_id are required.'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method. POST required.'
    ]);
}

$conn->close();
?>
