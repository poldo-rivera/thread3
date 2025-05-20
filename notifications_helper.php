<?php
require_once 'db.php';

function createNotification($actor_id, $user_id, $notification_type, $thread_id = null) {
    global $conn;
    
    // Don't create notification if user is acting on their own content
    if ($actor_id === $user_id) {
        return;
    }
    
    $sql = "INSERT INTO notifications (actor_id, user_id, notification_type, thread_id, created_at) 
            VALUES (?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisi", $actor_id, $user_id, $notification_type, $thread_id);
    return $stmt->execute();
}
