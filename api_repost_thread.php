<?php
require_once 'auth.php';
require_once 'db.php';

// Check if user is logged in
$current_user = getCurrentUser();
if (!$current_user) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Get thread ID
$thread_id = $_POST['thread_id'] ?? null;
if (!$thread_id) {
    echo json_encode(['success' => false, 'message' => 'Thread ID is required']);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // Check if thread exists
    $check_sql = "SELECT * FROM threads WHERE thread_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $thread_id);
    $check_stmt->execute();
    $thread = $check_stmt->get_result()->fetch_assoc();

    if (!$thread) {
        throw new Exception('Thread not found');
    }

    // Check if user already reposted
    $check_repost_sql = "SELECT * FROM reposts WHERE user_id = ? AND thread_id = ?";
    $check_repost_stmt = $conn->prepare($check_repost_sql);
    $check_repost_stmt->bind_param("ii", $current_user['user_id'], $thread_id);
    $check_repost_stmt->execute();
    $existing_repost = $check_repost_stmt->get_result()->fetch_assoc();

    if ($existing_repost) {
        // Remove repost
        $delete_sql = "DELETE FROM reposts WHERE user_id = ? AND thread_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("ii", $current_user['user_id'], $thread_id);
        $delete_stmt->execute();
        $action = 'removed';
    } else {
        // Add repost
        $insert_sql = "INSERT INTO reposts (user_id, thread_id, created_at) VALUES (?, ?, NOW())";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("ii", $current_user['user_id'], $thread_id);
        $insert_stmt->execute();
        $action = 'added';
    }

    // Get updated repost count
    $count_sql = "SELECT COUNT(*) as count FROM reposts WHERE thread_id = ?";
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param("i", $thread_id);
    $count_stmt->execute();
    $repost_count = $count_stmt->get_result()->fetch_assoc()['count'];

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'action' => $action,
        'repost_count' => $repost_count
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
