<?php
require_once 'db.php';
require_once 'auth.php';

header('Content-Type: application/json');
requireLogin();

$response = ['success' => false, 'html' => '', 'message' => ''];

$current_user_id = getCurrentUserId();

// Get users you're not following yet
$sql = "SELECT u.*, 
        (SELECT COUNT(*) FROM follows WHERE following_id = u.user_id) as followers_count
        FROM users u
        WHERE u.user_id != ? 
        AND u.user_id NOT IN (
            SELECT following_id FROM follows WHERE follower_id = ?
        )
        ORDER BY followers_count DESC
        LIMIT 20";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $current_user_id, $current_user_id);

if ($stmt->execute()) {
    $result = $stmt->get_result();
    ob_start();
    echo '<div class="people-grid">';
    while ($user = $result->fetch_assoc()) {
        ?>
        <div class="user-card">
            <img src="<?php echo $user['profile_pic'] ?: 'default_profile.png'; ?>" alt="Profile">
            <h3><?php echo htmlspecialchars($user['username']); ?></h3>
            <p><?php echo $user['followers_count']; ?> followers</p>
            <button class="follow-btn" data-user-id="<?php echo $user['user_id']; ?>" 
                    data-following="false">
                Follow
            </button>
        </div>
        <?php
    }
    echo '</div>';
    $response['html'] = ob_get_clean();
    $response['success'] = true;
} else {
    $response['message'] = 'Error fetching suggested users';
}

echo json_encode($response);