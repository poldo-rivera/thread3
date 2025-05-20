<?php
require_once 'db.php';
require_once 'auth.php';

header('Content-Type: application/json');
requireLogin();

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $thread_id = $_POST['thread_id'] ?? '';
    $content = trim($_POST['content'] ?? '');
    $user_id = getCurrentUserId();

    if (empty($thread_id) || empty($content)) {
        $response['message'] = 'Hindi kumpleto ang datos.';
        echo json_encode($response);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO comments (thread_id, user_id, content) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $thread_id, $user_id, $content);

    if ($stmt->execute()) {
        $comment_id = $stmt->insert_id;
        
        // Kunin ang bagong comment details kasama ang user info
        $get_stmt = $conn->prepare("
            SELECT c.*, u.username, u.profile_pic 
            FROM comments c
            JOIN users u ON c.user_id = u.user_id
            WHERE c.comment_id = ?
        ");
        $get_stmt->bind_param("i", $comment_id);
        $get_stmt->execute();
        $comment = $get_stmt->get_result()->fetch_assoc();

        $response['success'] = true;
        $response['message'] = 'Matagumpay na nai-save ang comment!';
        $response['comment'] = $comment;
    } else {
        $response['message'] = 'May error sa pag-save ng comment.';
    }
}

echo json_encode($response);