<?php
require_once 'db.php';
require_once 'auth.php';

header('Content-Type: application/json');
requireLogin();

$response = ['success' => false, 'comments' => [], 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $thread_id = $_GET['thread_id'] ?? null;
    
    if ($thread_id) {
        $stmt = $conn->prepare("
            SELECT t.*, u.username, u.profile_pic
            FROM threads t
            JOIN users u ON t.user_id = u.user_id
            WHERE t.parent_id = ?
            ORDER BY t.created_at DESC
        ");
        $stmt->bind_param("i", $thread_id);
        
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $comments = [];
            
            while ($row = $result->fetch_assoc()) {
                $comments[] = $row;
            }
            
            $response['success'] = true;
            $response['comments'] = $comments;
        } else {
            $response['message'] = 'May error sa pag-fetch ng comments';
        }
    } else {
        $response['message'] = 'Hindi valid ang thread ID';
    }
}

echo json_encode($response);