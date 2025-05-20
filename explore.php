<?php
require_once 'db.php';
require_once 'auth.php';
requireLogin();

// Get current user
$current_user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$current_user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get search query if any
$search = isset($_GET['q']) ? trim($_GET['q']) : '';

// Get current user ID from session
$current_user_id = $_SESSION['user_id'];

// Get users with search and follow status
$sql = "SELECT u.*, 
        (SELECT COUNT(*) FROM threads WHERE user_id = u.user_id AND parent_id IS NULL) as thread_count,
        (SELECT COUNT(*) FROM follows WHERE following_id = u.user_id) as follower_count,
        EXISTS(SELECT 1 FROM follows WHERE follower_id = ? AND following_id = u.user_id) as is_following
        FROM users u
        WHERE u.user_id != ?";

if ($search) {
    $sql .= " AND (u.username LIKE ?)"; 
}

$sql .= " ORDER BY follower_count DESC, thread_count DESC";

$stmt = $conn->prepare($sql);

if ($search) {
    $search_param = "%$search%";
    $stmt->bind_param("iis", $current_user_id, $current_user_id, $search_param);
} else {
    $stmt->bind_param("ii", $current_user_id, $current_user_id);
}

$stmt->execute();
$result = $stmt->get_result();
$users = [];
while ($user = $result->fetch_assoc()) {
    $users[] = $user;
}
$stmt->close();

require_once 'header.php';
?>

<div class="container">
    <div class="row">
        <!-- Main Content -->
        <div class="col-md-7 mx-auto">
            <div class="main-content">
                <!-- Search Header -->
                <div class="content-header sticky-top">
                    <div class="search-box">
                        <i class="bi bi-search"></i>
                        <form action="explore.php" method="GET" class="w-100">
                            <input type="text" name="q" placeholder="Search people..." value="<?php echo htmlspecialchars($search); ?>">
                        </form>
                    </div>
                </div>

                <!-- Users List -->
                <div class="users-list">
                    <?php if (empty($users) && $search): ?>
                        <div class="no-results">
                            <i class="bi bi-people"></i>
                            <p>No users found for "<?php echo htmlspecialchars($search); ?>"</p>
                        </div>
                    <?php elseif (empty($users)): ?>
                        <div class="no-results">
                            <i class="bi bi-people"></i>
                            <p>No users found</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <div class="user-card">
                                <div class="user-info">
                                    <a href="profile.php?username=<?php echo urlencode($user['username']); ?>" class="user-avatar">
                                        <img src="<?php echo htmlspecialchars($user['profile_pic']); ?>" alt="Profile picture">
                                    </a>
                                    <div class="user-details">
                                        <div class="user-name">
                                            <a href="profile.php?username=<?php echo urlencode($user['username']); ?>">
                                                <h6><?php echo htmlspecialchars($user['username']); ?></h6>
                                            </a>
                                        </div>
                                        <div class="user-stats">
                                            <span><?php echo number_format($user['thread_count']); ?> threads</span>
                                            <span class="dot">·</span>
                                            <span><span class="follower-count"><?php echo number_format($user['follower_count']); ?></span> followers</span>
                                        </div>
                                    </div>
                                </div>
                                <?php if ($user['user_id'] != $current_user['user_id']): ?>
                                    <button type="button" 
                                            class="follow-btn <?php echo $user['is_following'] ? 'following' : ''; ?>" 
                                            onclick="toggleFollow(this, <?php echo $user['user_id']; ?>)">
                                        <?php echo $user['is_following'] ? 'Following' : 'Follow'; ?>
                                    </button>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
async function toggleFollow(button, userId) {
    try {
        // Disable button while processing
        button.disabled = true;
        
        const formData = new FormData();
        formData.append('user_id', userId);
        
        const response = await fetch('api_follow.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Update button text and class based on action
            if (data.action === 'follow') {
                button.textContent = 'Following';
                button.classList.add('following');
            } else {
                button.textContent = 'Follow';
                button.classList.remove('following');
            }
            
            // Update follower count if it exists
            const statsContainer = button.closest('.user-card').querySelector('.user-stats');
            if (statsContainer) {
                const followerText = statsContainer.querySelector('.follower-count');
                if (followerText) {
                    let count = parseInt(followerText.textContent);
                    count = data.action === 'follow' ? count + 1 : count - 1;
                    followerText.textContent = count;
                }
            }
        } else {
            alert(data.message || 'Error processing request');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error processing request');
    } finally {
        // Re-enable button
        button.disabled = false;
    }
}
</script>

<?php require_once 'footer.php'; ?>