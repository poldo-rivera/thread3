<?php
require_once 'db.php';
require_once 'auth.php';

header('Content-Type: application/json');
requireLogin();

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $thread_id = $_POST['thread_id'];
    $reaction_type = $_POST['reaction_type'];
    $user_id = getCurrentUserId();

    if (empty($thread_id) || empty($reaction_type)) {
        $response['message'] = 'Thread ID and reaction type are required';
        echo json_encode($response);
        exit;
    }

    // Check if user already reacted
    $stmt = $conn->prepare("SELECT reaction_id FROM reactions WHERE thread_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $thread_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update existing reaction
        $stmt = $conn->prepare("UPDATE reactions SET reaction_type = ? WHERE thread_id = ? AND user_id = ?");
        $stmt->bind_param("sii", $reaction_type, $thread_id, $user_id);
    } else {
        // Create new reaction
        $stmt = $conn->prepare("INSERT INTO reactions (thread_id, user_id, reaction_type) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $thread_id, $user_id, $reaction_type);

        // Create notification
        $notify_stmt = $conn->prepare("
            INSERT INTO notifications (user_id, actor_id, thread_id, notification_type)
            SELECT user_id, ?, ?, 'reaction'
            FROM threads
            WHERE thread_id = ?
        ");
        $notify_stmt->bind_param("iii", $user_id, $thread_id, $thread_id);
        $notify_stmt->execute();
    }

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Reaction updated successfully';
    } else {
        $response['message'] = 'Error updating reaction';
    }
}

echo json_encode($response);