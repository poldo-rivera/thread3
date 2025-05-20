<?php
require_once 'db.php';
require_once 'auth.php';
requireLogin();

$profile_id = $_GET['id'] ?? getCurrentUserId();
$current_user = getCurrentUser();

// Kunin ang profile information
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc(); // Removed the & operator

// Check kung following
$current_user_id = getCurrentUserId(); // Store muna sa variable
$stmt = $conn->prepare("SELECT * FROM follows WHERE follower_id = ? AND following_id = ?");
$stmt->bind_param("ii", $current_user_id, $profile_id);
$stmt->execute();
$follow_result = $stmt->get_result();
$is_following = $follow_result->num_rows > 0;

// Kunin ang followers at following count
$stmt = $conn->prepare("SELECT 
    (SELECT COUNT(*) FROM follows WHERE following_id = ?) as followers,
    (SELECT COUNT(*) FROM follows WHERE follower_id = ?) as following");
$stmt->bind_param("ii", $profile_id, $profile_id);
$stmt->execute();
$counts = $stmt->get_result()->fetch_assoc();

require_once 'header.php';
?>

<div class="profile-container">
    <div class="profile-header">
        <div class="profile-info">
            <div class="profile-main">
                <h1 class="profile-name"><?php echo htmlspecialchars(isset($profile['name']) && $profile['name'] ? $profile['name'] : $profile['username']); ?></h1>
                <div class="profile-username">@<?php echo htmlspecialchars($profile['username']); ?></div>
                
                <?php if (!empty($profile['bio'])): ?>
                    <div class="profile-bio"><?php echo nl2br(htmlspecialchars($profile['bio'])); ?></div>
                <?php endif; ?>
            </div>
            
            <div class="profile-avatar">
                <img src="<?php echo $profile['profile_pic'] ?: 'https://www.gravatar.com/avatar/default?s=200'; ?>" 
                     alt="<?php echo htmlspecialchars($profile['username']); ?>">
            </div>
        </div>
        
        <div class="profile-stats">
            <div class="profile-followers">
                <span class="stats-count"><?php echo number_format($counts['followers']); ?></span>
                <span class="stats-label">followers</span>
            </div>
        </div>
        
        <?php if ($profile_id == getCurrentUserId()): ?>
            <a href="edit_profile.php" class="profile-edit-button">Edit profile</a>
        <?php else: ?>
            <button class="profile-follow-button <?php echo $is_following ? 'following' : ''; ?>" 
                    data-user-id="<?php echo $profile_id; ?>">
                <?php echo $is_following ? 'Following' : 'Follow'; ?>
            </button>
        <?php endif; ?>
    </div>
    
    <div class="profile-tabs">
        <button class="tab-button active" data-tab="threads">Threads</button>
        <button class="tab-button" data-tab="replies">Replies</button>
      
        <button class="tab-button" data-tab="reposts">Reposts</button>
    </div>
    
    <div class="profile-content" id="threads-content">
        <?php
        // Get user's threads
        $current_user_id = getCurrentUserId();
        $sql = "SELECT t.*, u.username, u.profile_pic,
                (SELECT COUNT(*) FROM reactions WHERE thread_id = t.thread_id) as reaction_count,
                (SELECT COUNT(*) FROM threads WHERE parent_id = t.thread_id) as reply_count,
                EXISTS(SELECT 1 FROM reactions WHERE thread_id = t.thread_id AND user_id = ? AND reaction_type = 'like') as has_reacted
                FROM threads t
                JOIN users u ON t.user_id = u.user_id
                WHERE t.user_id = ? AND t.parent_id IS NULL
                ORDER BY t.created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $current_user_id, $profile_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($thread = $result->fetch_assoc()) {
            include 'thread_card.php';
        }
        ?>
    </div>

    <div class="profile-content" id="replies-content" style="display: none;">
        <?php
        // Get user's replies (threads with parent_id)
        $sql = "SELECT t.*, u.username, u.profile_pic,
                p.content as parent_content, p.thread_id as parent_thread_id,
                pu.username as parent_username,
                (SELECT COUNT(*) FROM reactions WHERE thread_id = t.thread_id) as reaction_count,
                (SELECT COUNT(*) FROM threads WHERE parent_id = t.thread_id) as reply_count,
                EXISTS(SELECT 1 FROM reactions WHERE thread_id = t.thread_id AND user_id = ? AND reaction_type = 'like') as has_reacted
                FROM threads t
                JOIN users u ON t.user_id = u.user_id
                JOIN threads p ON t.parent_id = p.thread_id
                JOIN users pu ON p.user_id = pu.user_id
                WHERE t.user_id = ? AND t.parent_id IS NOT NULL
                ORDER BY t.created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $current_user_id, $profile_id);
        $stmt->execute();
        $replies = $stmt->get_result();
        
        while ($thread = $replies->fetch_assoc()) {
            // Add parent thread info
            echo '<div class="reply-container">';
            echo '<div class="parent-thread">';
            echo '<a href="thread.php?id=' . $thread['parent_thread_id'] . '" class="parent-link">';
            echo '<span class="parent-username">@' . htmlspecialchars($thread['parent_username']) . '</span>';
            echo '<span class="parent-content">' . htmlspecialchars(substr($thread['parent_content'], 0, 100)) . 
                 (strlen($thread['parent_content']) > 100 ? '...' : '') . '</span>';
            echo '</a>';
            echo '</div>';
            include 'thread_card.php';
            echo '</div>';
        }
        ?>
    </div>

    <div class="profile-content" id="reposts-content" style="display: none;">
        <?php
        // Get user's reposts
        $sql = "SELECT t.*, u.username, u.profile_pic,
                rt.content as reposted_content, rt.thread_id as reposted_thread_id,
                rt.image_url, rt.video_url, rt.audio_url,
                ru.username as reposted_username, ru.profile_pic as reposted_profile_pic,
                (SELECT COUNT(*) FROM reactions WHERE thread_id = rt.thread_id) as reaction_count,
                (SELECT COUNT(*) FROM threads WHERE parent_id = rt.thread_id) as reply_count,
                EXISTS(SELECT 1 FROM reactions WHERE thread_id = rt.thread_id AND user_id = ? AND reaction_type = 'like') as has_reacted
                FROM reposts r
                JOIN threads rt ON r.thread_id = rt.thread_id
                JOIN users ru ON rt.user_id = ru.user_id
                JOIN users u ON r.user_id = u.user_id
                LEFT JOIN threads t ON r.thread_id = t.thread_id
                WHERE r.user_id = ?
                ORDER BY r.created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $current_user_id, $profile_id);
        $stmt->execute();
        $reposts = $stmt->get_result();
        
        while ($repost = $reposts->fetch_assoc()) {
            echo '<div class="repost-container">';
            echo '<div class="repost-header">';
            echo '<i class="bi bi-repeat"></i> Reposted';
            echo '</div>';
            // Show original thread
            $thread = [
                'thread_id' => $repost['reposted_thread_id'],
                'user_id' => $repost['user_id'],
                'username' => $repost['reposted_username'],
                'profile_pic' => $repost['reposted_profile_pic'],
                'content' => $repost['reposted_content'],
                'created_at' => $repost['created_at'],
                'reaction_count' => $repost['reaction_count'],
                'reply_count' => $repost['reply_count'],
                'has_reacted' => $repost['has_reacted'],
                'image_url' => $repost['image_url'],
                'video_url' => $repost['video_url'],
                'audio_url' => $repost['audio_url']
            ];
            include 'thread_card.php';
            echo '</div>';
        }
        ?>
    </div>
</div>

<script src="profile.js"></script>
<?php require_once 'footer.php'; ?>