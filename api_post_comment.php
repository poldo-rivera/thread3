<?php
require_once 'auth.php';
require_once 'db.php';

// Check if user is logged in
$current_user = getCurrentUser();
if (!$current_user) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Get post data
$content = $_POST['content'] ?? '';
$parent_id = $_POST['parent_id'] ?? null;

if (empty($content) && !isset($_FILES['image']) && !isset($_FILES['video']) && !isset($_FILES['audio'])) {
    echo json_encode(['success' => false, 'message' => 'Content or media is required']);
    exit;
}

if (!$parent_id) {
    echo json_encode(['success' => false, 'message' => 'Parent thread ID is required']);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // Insert comment
    $sql = "INSERT INTO threads (user_id, content, parent_id, created_at) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $current_user['user_id'], $content, $parent_id);
    $stmt->execute();
    $comment_id = $conn->insert_id;

    // Handle media upload if present
    $media_type = null;
    $media_url = null;

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $media_type = 'image';
        $media_url = handleMediaUpload($_FILES['image'], $comment_id);
    } elseif (isset($_FILES['video']) && $_FILES['video']['error'] === 0) {
        $media_type = 'video';
        $media_url = handleMediaUpload($_FILES['video'], $comment_id);
    } elseif (isset($_FILES['audio']) && $_FILES['audio']['error'] === 0) {
        $media_type = 'audio';
        $media_url = handleMediaUpload($_FILES['audio'], $comment_id);
    }

    // Update media info if uploaded
    if ($media_type && $media_url) {
        $update_sql = "UPDATE threads SET media_type = ?, media_url = ? WHERE thread_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssi", $media_type, $media_url, $comment_id);
        $update_stmt->execute();
    }

    // Commit transaction
    $conn->commit();

    // Get the complete comment data
    $comment_sql = "SELECT t.*, u.username, u.profile_pic 
                   FROM threads t 
                   JOIN users u ON t.user_id = u.user_id 
                   WHERE t.thread_id = ?";
    $comment_stmt = $conn->prepare($comment_sql);
    $comment_stmt->bind_param("i", $comment_id);
    $comment_stmt->execute();
    $comment = $comment_stmt->get_result()->fetch_assoc();

    echo json_encode([
        'success' => true,
        'comment' => $comment
    ]);

} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error saving comment']);
}

// Helper function to handle media uploads
function handleMediaUpload($file, $thread_id) {
    $upload_dir = 'uploads/';
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $file_name = $thread_id . '_' . uniqid() . '.' . $file_extension;
    $target_path = $upload_dir . $file_name;

    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        return $target_path;
    }
    throw new Exception('Failed to upload file');
}
