<?php
require_once 'utils.php';

// Get comments for this thread
$comments_sql = "SELECT c.*, u.username, u.profile_pic 
                FROM threads c 
                JOIN users u ON c.user_id = u.user_id 
                WHERE c.parent_id = ? 
                ORDER BY c.created_at ASC";
$comments_stmt = $conn->prepare($comments_sql);
$comments_stmt->bind_param("i", $thread['thread_id']);
$comments_stmt->execute();
$comments = $comments_stmt->get_result();
?>

<div class="comment-section" id="comments-<?php echo $thread['thread_id']; ?>">
    <!-- Comment Form -->
    <div class="comment-form-container">
        <img src="<?php echo getCurrentUser()['profile_pic']; ?>" alt="Profile" class="profile-pic">
        <form class="comment-form" data-thread-id="<?php echo $thread['thread_id']; ?>">
            <div class="comment-input-wrapper">
                <textarea name="content" placeholder="Write a comment..." rows="1"></textarea>
                <div class="comment-actions">
                    <div class="media-upload-buttons">
                        <label title="Add Image">
                            <input type="file" name="image" accept="image/*" class="comment-media-input" data-type="image">
                            📷
                        </label>
                        <label title="Add Video">
                            <input type="file" name="video" accept="video/*" class="comment-media-input" data-type="video">
                            🎥
                        </label>
                        <label title="Add Audio">
                            <input type="file" name="audio" accept="audio/*" class="comment-media-input" data-type="audio">
                            🎵
                        </label>
                    </div>
                    <button type="submit" class="comment-submit" disabled>Reply</button>
                </div>
            </div>
            <div class="comment-preview"></div>
        </form>
    </div>

    <!-- Comments List -->
    <div class="comments-list">
        <?php while ($comment = $comments->fetch_assoc()): ?>
        <div class="comment-item">
            <img src="<?php echo $comment['profile_pic']; ?>" alt="Profile" class="profile-pic">
            <div class="comment-content">
                <div class="comment-header">
                    <span class="username"><?php echo $comment['username']; ?></span>
                    <span class="comment-time"><?php echo timeAgo($comment['created_at']); ?></span>
                </div>
                <div class="comment-text"><?php echo $comment['content']; ?></div>
                <?php if (isset($comment['media_type']) && $comment['media_type']): ?>
                    <div class="comment-media">
                        <?php if ($comment['media_type'] === 'image'): ?>
                            <img src="<?php echo $comment['media_url']; ?>" alt="Comment image">
                        <?php elseif ($comment['media_type'] === 'video'): ?>
                            <video src="<?php echo $comment['media_url']; ?>" controls></video>
                        <?php elseif ($comment['media_type'] === 'audio'): ?>
                            <audio src="<?php echo $comment['media_url']; ?>" controls></audio>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>
