<?php
require_once 'db.php';
require_once 'auth.php';
require_once 'notifications_helper.php';

header('Content-Type: application/json');
requireLogin();

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = trim($_POST['content']);
    $user_id = getCurrentUserId();
    $image_url = null;
    $audio_url = null;
    $video_url = null;

    if (empty($content)) {
        $response['message'] = 'Content cannot be empty';
        echo json_encode($response);
        exit;
    }

    // Handle video upload if present
    if (isset($_FILES['video']) && $_FILES['video']['error'] === 0) {
        $allowed = ['mp4', 'webm', 'mov'];
        $filename = $_FILES['video']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $new_filename = 'uploads_video_' . $user_id . '_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['video']['tmp_name'], $new_filename)) {
                $video_url = $new_filename;
            }
        }
    }

    // Handle audio upload if present
    if (isset($_FILES['audio']) && $_FILES['audio']['error'] === 0) {
        $allowed = ['mp3', 'wav', 'ogg'];
        $filename = $_FILES['audio']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $new_filename = 'uploads_audio_' . $user_id . '_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['audio']['tmp_name'], $new_filename)) {
                $audio_url = $new_filename;
            }
        }
    }

    // Handle image upload if present
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $upload_dir = 'uploads/';
            $new_filename = 'thread_' . $user_id . '_' . time() . '.' . $ext;
            $full_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $full_path)) {
                $image_url = $full_path;
            } else {
                $response['message'] = 'Error uploading image';
                echo json_encode($response);
                exit;
            }
        } else {
            $response['message'] = 'Invalid image format. Allowed: jpg, jpeg, png, gif';
            echo json_encode($response);
            exit;
        }
    }

    // Check if this is a reply to another thread
    $parent_id = isset($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
    
    if ($parent_id) {
        $sql = "INSERT INTO threads (user_id, content, image_url, audio_url, video_url, parent_id) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssi", $user_id, $content, $image_url, $audio_url, $video_url, $parent_id);
    } else {
        $sql = "INSERT INTO threads (user_id, content, image_url, audio_url, video_url) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issss", $user_id, $content, $image_url, $audio_url, $video_url);
    }

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Thread posted successfully';
        
        // Create notification for reply
        if ($parent_id) {
            // Get parent thread owner
            $parent_sql = "SELECT user_id FROM threads WHERE thread_id = ?";
            $parent_stmt = $conn->prepare($parent_sql);
            $parent_stmt->bind_param("i", $parent_id);
            $parent_stmt->execute();
            $parent_result = $parent_stmt->get_result();
            
            if ($parent_data = $parent_result->fetch_assoc()) {
                createNotification($user_id, $parent_data['user_id'], 'reply', $parent_id);
            }
        }
    } else {
        $response['message'] = 'Error posting thread';
    }
}

echo json_encode($response);