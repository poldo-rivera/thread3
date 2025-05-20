<?php
require_once 'db.php';
require_once 'auth.php';

header('Content-Type: application/json');
requireLogin();

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = trim($_POST['content']);
    $parent_id = $_POST['parent_id'];
    $user_id = getCurrentUserId();
    $image_url = null;

    if (empty($content) || empty($parent_id)) {
        $response['message'] = 'Content and parent thread ID are required';
        echo json_encode($response);
        exit;
    }

    // Handle image upload if present
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $new_filename = 'uploads_thread_' . $user_id . '_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $new_filename)) {
                $image_url = $new_filename;
            }
        }
    }

    $stmt = $conn->prepare("INSERT INTO threads (user_id, content, parent_id, image_url) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isis", $user_id, $content, $parent_id, $image_url);

    if ($stmt->execute()) {
        // Create notification for the parent thread owner
        $stmt = $conn->prepare("
            INSERT INTO notifications (user_id, actor_id, thread_id, notification_type)
            SELECT user_id, ?, ?, 'reply'
            FROM threads
            WHERE thread_id = ?
        ");
        $thread_id = $conn->insert_id;
        $stmt->bind_param("iii", $user_id, $thread_id, $parent_id);
        $stmt->execute();

        $response['success'] = true;
        $response['message'] = 'Reply posted successfully';
    } else {
        $response['message'] = 'Error posting reply';
    }
}

echo json_encode($response);