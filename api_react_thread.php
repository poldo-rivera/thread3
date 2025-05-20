<?php
require_once 'auth.php';
require_once 'db.php';
require_once 'notifications_helper.php';

// Check if user is logged in
$current_user = getCurrentUser();
if (!$current_user) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Get thread_id from POST request
$thread_id = $_POST['thread_id'] ?? null;
if (!$thread_id) {
    echo json_encode(['success' => false, 'message' => 'Thread ID is required']);
    exit;
}

// Check if user already reacted to this thread
$check_sql = "SELECT * FROM reactions WHERE user_id = ? AND thread_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $current_user['user_id'], $thread_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows > 0) {
    // User already reacted - remove reaction
    $delete_sql = "DELETE FROM reactions WHERE user_id = ? AND thread_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("ii", $current_user['user_id'], $thread_id);
    $success = $delete_stmt->execute();
    $action = 'unreact';
} else {
    // User hasn't reacted - add reaction
    $insert_sql = "INSERT INTO reactions (user_id, thread_id) VALUES (?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("ii", $current_user['user_id'], $thread_id);
    $success = $insert_stmt->execute();
    $action = 'react';

    // Get thread owner and create notification
    if ($success) {
        $thread_sql = "SELECT user_id FROM threads WHERE thread_id = ?";
        $thread_stmt = $conn->prepare($thread_sql);
        $thread_stmt->bind_param("i", $thread_id);
        $thread_stmt->execute();
        $thread_result = $thread_stmt->get_result();
        if ($thread_data = $thread_result->fetch_assoc()) {
            createNotification($current_user['user_id'], $thread_data['user_id'], 'reaction', $thread_id);
        }
    }
}

// Get updated reaction count
$count_sql = "SELECT COUNT(*) as count FROM reactions WHERE thread_id = ?";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param("i", $thread_id);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$count = $count_result->fetch_assoc()['count'];

echo json_encode([
    'success' => $success,
    'action' => $action,
    'reaction_count' => $count
]);
