<?php
// API/comments.php
header('Content-Type: application/json; charset=utf-8');
session_start();
require __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Fetch comments for a specific event
    $event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;
    if (!$event_id) {
        echo json_encode(['success' => false, 'message' => 'No event ID provided.']);
        exit;
    }
    // Fetch comments and join with Users to get the username
    $stmt = $conn->prepare("
        SELECT c.comment_id, c.event_id, c.user_id, c.comment_text, c.created_at, u.username 
        FROM Comment c 
        LEFT JOIN Users u ON c.user_id = u.user_id 
        WHERE c.event_id = ? 
        ORDER BY c.created_at DESC
    ");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $comments = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    echo json_encode(['success' => true, 'data' => $comments]);
    exit;
} elseif ($method === 'POST') {
    // Post a new comment; user must be logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'You must be logged in to post a comment.']);
        exit;
    }
    $user_id = $_SESSION['user_id'];
    
    // Decode JSON payload from request body
    $input = json_decode(file_get_contents("php://input"), true);
    $event_id = isset($input['event_id']) ? intval($input['event_id']) : 0;
    $comment_text = isset($input['comment_text']) ? trim($input['comment_text']) : '';
    
    if (!$event_id || empty($comment_text)) {
        echo json_encode(['success' => false, 'message' => 'Event ID and comment text are required.']);
        exit;
    }
    
    // Insert the new comment
    $stmt = $conn->prepare("INSERT INTO Comment (event_id, user_id, comment_text) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $event_id, $user_id, $comment_text);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Comment posted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to post comment: ' . $conn->error]);
    }
    $stmt->close();
    exit;
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}
?>
