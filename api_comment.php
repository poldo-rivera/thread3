<?php
require_once 'db.php';
require_once 'auth.php';

header('Content-Type: application/json');
requireLogin();

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = trim($_POST['content'] ?? '');
    $thread_id = $_POST['thread_id'] ?? null;
    $user_id = getCurrentUserId();
    
    if (empty($content) || empty($thread_id)) {
        $response['message'] = 'Kailangan may laman ang comment at thread ID.';
        echo json_encode($response);
        exit;
    }

    // I-save ang comment
    $stmt = $conn->prepare("INSERT INTO comments (thread_id, user_id, content) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $thread_id, $user_id, $content);

    if ($stmt->execute()) {
        // Kunin ang bagong comment details
        $comment_id = $stmt->insert_id;
        
        // Get comment with user details
        $get_comment = $conn->prepare("
            SELECT c.*, u.username, u.profile_pic 
            FROM comments c
            JOIN users u ON c.user_id = u.user_id
            WHERE c.comment_id = ?
        ");
        $get_comment->bind_param("i", $comment_id);
        $get_comment->execute();
        $comment = $get_comment->get_result()->fetch_assoc();

        $response['success'] = true;
        $response['message'] = 'Matagumpay na nai-post ang comment!';
        $response['comment'] = $comment;
    } else {
        $response['message'] = 'May error sa pag-save ng comment.';
    }
}

echo json_encode($response);