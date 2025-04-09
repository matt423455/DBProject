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
    // Post a new comment
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'You must be logged in to post a comment.']);
        exit;
    }
    $user_id = $_SESSION['user_id'];
    $input = json_decode(file_get_contents("php://input"), true);
    $event_id = isset($input['event_id']) ? intval($input['event_id']) : 0;
    $comment_text = isset($input['comment_text']) ? trim($input['comment_text']) : '';

    if (!$event_id || empty($comment_text)) {
        echo json_encode(['success' => false, 'message' => 'Event ID and comment text are required.']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO Comment (event_id, user_id, comment_text) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $event_id, $user_id, $comment_text);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Comment posted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to post comment: ' . $conn->error]);
    }
    $stmt->close();
    exit;

} elseif ($method === 'PUT') {
    // Edit a comment
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'You must be logged in to edit a comment.']);
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $input = json_decode(file_get_contents("php://input"), true);
    $comment_id = isset($input['comment_id']) ? intval($input['comment_id']) : 0;
    $comment_text = isset($input['comment_text']) ? trim($input['comment_text']) : '';

    if (!$comment_id || empty($comment_text)) {
        echo json_encode(['success' => false, 'message' => 'Comment ID and new text are required.']);
        exit;
    }

    // Verify user owns the comment
    $stmt = $conn->prepare("SELECT user_id FROM Comment WHERE comment_id = ?");
    $stmt->bind_param("i", $comment_id);
    $stmt->execute();
    $stmt->bind_result($owner_id);
    $stmt->fetch();
    $stmt->close();

    if ($owner_id !== $user_id) {
        echo json_encode(['success' => false, 'message' => 'You do not have permission to edit this comment.']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE Comment SET comment_text = ? WHERE comment_id = ?");
    $stmt->bind_param("si", $comment_text, $comment_id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Comment updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update comment: ' . $conn->error]);
    }
    $stmt->close();
    exit;

} elseif ($method === 'DELETE') {
    // Delete a comment
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'You must be logged in to delete a comment.']);
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $input = json_decode(file_get_contents("php://input"), true);
    $comment_id = isset($input['comment_id']) ? intval($input['comment_id']) : 0;

    if (!$comment_id) {
        echo json_encode(['success' => false, 'message' => 'Comment ID is required.']);
        exit;
    }

    // Verify ownership
    $stmt = $conn->prepare("SELECT user_id FROM Comment WHERE comment_id = ?");
    $stmt->bind_param("i", $comment_id);
    $stmt->execute();
    $stmt->bind_result($owner_id);
    $stmt->fetch();
    $stmt->close();

    if ($owner_id !== $user_id) {
        echo json_encode(['success' => false, 'message' => 'You do not have permission to delete this comment.']);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM Comment WHERE comment_id = ?");
    $stmt->bind_param("i", $comment_id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Comment deleted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete comment: ' . $conn->error]);
    }
    $stmt->close();
    exit;

} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}
?>
