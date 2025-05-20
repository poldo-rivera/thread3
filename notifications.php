<?php
require_once 'db.php';
require_once 'auth.php';
requireLogin();

$user_id = getCurrentUserId();

// Mark all notifications as read
$conn->query("UPDATE notifications SET is_read = 1 WHERE user_id = $user_id");

// Get notifications with thread details
$sql = "SELECT n.*, u.username, u.profile_pic, 
        t.content as thread_content, t.thread_id,
        (SELECT COUNT(*) FROM reactions WHERE thread_id = t.thread_id) as reaction_count,
        (SELECT COUNT(*) FROM threads WHERE parent_id = t.thread_id) as reply_count
        FROM notifications n
        JOIN users u ON n.actor_id = u.user_id
        LEFT JOIN threads t ON n.thread_id = t.thread_id
        WHERE n.user_id = ?
        ORDER BY n.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$notifications = $stmt->get_result();

require_once 'header.php';
?>

<div class="notifications-container">
    <h2>Notifications</h2>

    <?php if ($notifications->num_rows === 0): ?>
        <p class="no-notifications">No notifications yet</p>
    <?php else: ?>
        <?php while ($notification = $notifications->fetch_assoc()): ?>
            <div class="notification-card">
                <div class="notification-header">
                    <img src="<?php echo $notification['profile_pic']; ?>" alt="Profile" class="profile-pic">
                    <div class="notification-content">
                        <div class="notification-text">
                            <a href="profile.php?id=<?php echo $notification['actor_id']; ?>" class="username">
                                <?php echo htmlspecialchars($notification['username']); ?>
                            </a>
                            
                            <?php
                            switch ($notification['notification_type']) {
                                case 'follow':
                                    echo ' started following you';
                                    break;
                                case 'reply':
                                case 'quote':
                                case 'reaction':
                                    $action = $notification['notification_type'] === 'reply' ? 'replied to' : 
                                            ($notification['notification_type'] === 'quote' ? 'quoted' : 'reacted to');
                                    echo " $action your thread";
                                    if ($notification['thread_content']) {
                                        echo ": <a href='thread.php?id=" . $notification['thread_id'] . "' class='thread-link'>" . 
                                             htmlspecialchars(substr($notification['thread_content'], 0, 50)) . 
                                             (strlen($notification['thread_content']) > 50 ? '...' : '') . "</a>";
                                    }
                                    break;
                            }
                            ?>
                        </div>
                        <div class="notification-meta">
                            <span class="timestamp"><?php echo date('M d, Y h:i A', strtotime($notification['created_at'])); ?></span>
                            <?php if ($notification['thread_id']): ?>
                                <span class="stats">
                                    <span><?php echo number_format($notification['reaction_count']); ?> reactions</span>
                                    <span><?php echo number_format($notification['reply_count']); ?> replies</span>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>