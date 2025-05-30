<div class="thread-card" data-thread-id="<?php echo $thread['thread_id']; ?>">
    <div class="thread-header">
        <img src="<?php echo $thread['profile_pic']; ?>" alt="Profile" class="profile-pic">
        <div class="thread-info">
            <div class="user-info">
                <a href="profile.php?id=<?php echo $thread['user_id']; ?>" class="username">
                    <?php echo htmlspecialchars($thread['username']); ?>
                </a>
                <span class="timestamp"><?php echo date('M d', strtotime($thread['created_at'])); ?></span>
            </div>
        </div>
    </div>

    <div class="thread-content">
        <p><?php echo nl2br(htmlspecialchars($thread['content'])); ?></p>
        <?php if ($thread['image_url']): ?>
            <div class="thread-media">
                <img src="<?php echo $thread['image_url']; ?>" alt="Thread image" class="thread-image">
            </div>
        <?php endif; ?>
        <?php if ($thread['video_url']): ?>
            <div class="thread-media">
                <video controls>
                    <source src="<?php echo $thread['video_url']; ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            </div>
        <?php endif; ?>
        <?php if ($thread['audio_url']): ?>
            <div class="thread-media audio-player">
                <audio controls>
                    <source src="<?php echo $thread['audio_url']; ?>" type="audio/mpeg">
                    Your browser does not support the audio element.
                </audio>
            </div>
        <?php endif; ?>
    </div>

    <?php
            // Check if user has reacted to this thread
            $has_reacted_sql = "SELECT 1 FROM reactions WHERE user_id = ? AND thread_id = ?";
            $has_reacted_stmt = $conn->prepare($has_reacted_sql);
            $has_reacted_stmt->bind_param("ii", $current_user['user_id'], $thread['thread_id']);
            $has_reacted_stmt->execute();
            $has_reacted = $has_reacted_stmt->get_result()->num_rows > 0;
            ?>
            <div class="thread-actions">
                <button class="action-button react-button <?php echo $has_reacted ? 'reacted' : ''; ?>" data-thread-id="<?php echo $thread['thread_id']; ?>">
                    <svg viewBox="0 0 24 24" width="24" height="24">
                        <path fill="currentColor" d="M16.697 5.5c-1.222-.06-2.679.51-3.89 2.16l-.805 1.09-.806-1.09C9.984 6.01 8.526 5.44 7.304 5.5c-1.243.07-2.349.78-2.91 1.91-.552 1.12-.633 2.78.479 4.82 1.074 1.97 3.257 4.27 7.129 6.61 3.87-2.34 6.052-4.64 7.126-6.61 1.111-2.04 1.03-3.7.477-4.82-.561-1.13-1.666-1.84-2.908-1.91zm4.187 7.69c-1.351 2.48-4.001 5.12-8.379 7.67l-.503.3-.504-.3c-4.379-2.55-7.029-5.19-8.382-7.67-1.36-2.5-1.41-4.86-.514-6.67.887-1.79 2.647-2.91 4.601-3.01 1.651-.09 3.368.56 4.798 2.01 1.429-1.45 3.146-2.1 4.796-2.01 1.954.1 3.714 1.22 4.601 3.01.896 1.81.846 4.17-.514 6.67z"/>
                    </svg>
                    <span class="action-count"><?php echo $thread['reaction_count']; ?></span>
                </button>
        <button class="action-btn comment-btn" title="Comment">
            <svg viewBox="0 0 24 24" width="18" height="18">
                <path fill="currentColor" d="M1.751 10c0-4.42 3.584-8 8.005-8h4.366c4.49 0 8.129 3.64 8.129 8.13 0 2.96-1.607 5.68-4.196 7.11l-8.054 4.46v-3.69h-.067c-4.49.1-8.183-3.51-8.183-8.01zm8.005-6c-3.317 0-6.005 2.69-6.005 6 0 3.37 2.77 6.08 6.138 6.01l.351-.01h1.761v2.3l5.087-2.81c1.951-1.08 3.163-3.13 3.163-5.36 0-3.39-2.744-6.13-6.129-6.13H9.756z"/>
            </svg>
            <span class="action-count comment-count"><?php 
    $comment_count_sql = "SELECT COUNT(*) as count FROM threads WHERE parent_id = ?";
    $comment_count_stmt = $conn->prepare($comment_count_sql);
    $comment_count_stmt->bind_param("i", $thread['thread_id']);
    $comment_count_stmt->execute();
    $comment_count = $comment_count_stmt->get_result()->fetch_assoc()['count'];
    echo $comment_count;
?></span>
        </button>
        <?php
            // Check if user has reposted
            $repost_check_sql = "SELECT * FROM reposts WHERE user_id = ? AND thread_id = ?";
            $repost_check_stmt = $conn->prepare($repost_check_sql);
            $repost_check_stmt->bind_param("ii", $current_user['user_id'], $thread['thread_id']);
            $repost_check_stmt->execute();
            $has_reposted = $repost_check_stmt->get_result()->fetch_assoc() ? true : false;

            // Get repost count
            $repost_count_sql = "SELECT COUNT(*) as count FROM reposts WHERE thread_id = ?";
            $repost_count_stmt = $conn->prepare($repost_count_sql);
            $repost_count_stmt->bind_param("i", $thread['thread_id']);
            $repost_count_stmt->execute();
            $repost_count = $repost_count_stmt->get_result()->fetch_assoc()['count'];
        ?>
        <button class="action-btn repost-btn <?php echo $has_reposted ? 'active' : ''; ?>" title="Repost">
            <svg viewBox="0 0 24 24" width="18" height="18">
                <path fill="currentColor" d="M4.5 3.88l4.432 4.14-1.364 1.46L5.5 7.55V16c0 1.1.896 2 2 2H13v2H7.5c-2.209 0-4-1.79-4-4V7.55L1.432 9.48.068 8.02 4.5 3.88zM16.5 6H11V4h5.5c2.209 0 4 1.79 4 4v8.45l2.068-1.93 1.364 1.46-4.432 4.14-4.432-4.14 1.364-1.46 2.068 1.93V8c0-1.1-.896-2-2-2z"/>
            </svg>
            <span class="action-count"><?php echo $repost_count; ?></span>
        </button>
    </div>
    <div class="comment-section-wrapper" style="display: none;">
        <?php include 'comment_section.php'; ?>
    </div>
</div>