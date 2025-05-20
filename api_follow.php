<?php
require_once 'db.php';
require_once 'auth.php';

header('Content-Type: application/json');
requireLogin();

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $follower_id = getCurrentUserId();
    $following_id = $_POST['user_id'] ?? null;

    if (!$following_id) {
        $response['message'] = 'Hindi mahanap ang user.';
        echo json_encode($response);
        exit;
    }

    // Check kung following na
    $check_stmt = $conn->prepare("SELECT follow_id FROM follows WHERE follower_id = ? AND following_id = ?");
    $check_stmt->bind_param("ii", $follower_id, $following_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        // Unfollow
        $stmt = $conn->prepare("DELETE FROM follows WHERE follower_id = ? AND following_id = ?");
        $stmt->bind_param("ii", $follower_id, $following_id);
        $action = 'unfollow';
    } else {
        // Follow
        $stmt = $conn->prepare("INSERT INTO follows (follower_id, following_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $follower_id, $following_id);
        $action = 'follow';
    }

    if ($stmt->execute()) {
        if ($action === 'follow') {
            // Create notification for new follow
            $notify_stmt = $conn->prepare("INSERT INTO notifications (user_id, actor_id, notification_type) VALUES (?, ?, 'follow')");
            $notify_stmt->bind_param("ii", $following_id, $follower_id);
            $notify_stmt->execute();
        }

        $response['success'] = true;
        $response['message'] = $action === 'follow' ? 'Matagumpay na na-follow!' : 'Matagumpay na na-unfollow!';
        $response['action'] = $action;
    } else {
        $response['message'] = 'May error sa pag-' . $action;
    }
}

echo json_encode($response);