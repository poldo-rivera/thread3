<?php
require_once 'db.php';
require_once 'auth.php';

header('Content-Type: application/json');
requireLogin();

$response = ['success' => false, 'html' => '', 'message' => ''];

$sql = "SELECT t.*, u.username, u.profile_pic,
        (SELECT COUNT(*) FROM reactions WHERE thread_id = t.thread_id) as reaction_count,
        (SELECT COUNT(*) FROM threads WHERE parent_id = t.thread_id) as reply_count
        FROM threads t
        JOIN users u ON t.user_id = u.user_id
        WHERE t.parent_id IS NULL
        ORDER BY t.created_at DESC
        LIMIT 50";

$result = $conn->query($sql);
if ($result) {
    ob_start();
    echo '<div class="threads-grid">';
    while ($thread = $result->fetch_assoc()) {
        include 'thread_card.php';
    }
    echo '</div>';
    $response['html'] = ob_get_clean();
    $response['success'] = true;
} else {
    $response['message'] = 'Error fetching latest threads';
}

echo json_encode($response);