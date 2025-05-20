<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'db.php';
require_once 'auth.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in']);
    exit;
}

// Get current user ID
$current_user_id = $_SESSION['user_id'];

// Get POST data
$input = file_get_contents('php://input');
error_log('Received input: ' . $input);

$data = json_decode($input, true);
$user_id = $data['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'User ID is required']);
    exit;
}

error_log('Current user ID: ' . $current_user_id);
error_log('Target user ID: ' . $user_id);

header('Content-Type: application/json');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get raw POST data
$raw_data = file_get_contents('php://input');

// Log the raw data
error_log('Raw POST data: ' . $raw_data);

$data = json_decode($raw_data, true);
$user_id = $data['user_id'] ?? null;

if (!$user_id) {
    error_log('No user_id provided');
    echo json_encode(['success' => false, 'message' => 'User ID is required']);
    exit;
}

// Log current user info
error_log('Current user ID: ' . $current_user['user_id']);
error_log('Target user ID: ' . $user_id);

try {
    // Check if already following
    $check_sql = "SELECT * FROM followers WHERE follower_id = ? AND following_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    if (!$check_stmt) {
        throw new Exception('Error preparing check statement: ' . $conn->error);
    }
    
    $check_stmt->bind_param("ii", $current_user_id, $user_id);
    if (!$check_stmt->execute()) {
        throw new Exception('Error executing check statement: ' . $check_stmt->error);
    }
    
    $result = $check_stmt->get_result();
    $is_following = $result->num_rows > 0;
    $check_stmt->close();
    
    error_log('Is following check - Rows found: ' . $result->num_rows);
    
    if ($is_following) {
        // Unfollow
        $sql = "DELETE FROM followers WHERE follower_id = ? AND following_id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Error preparing unfollow statement: ' . $conn->error);
        }
        
        $stmt->bind_param("ii", $current_user_id, $user_id);
        if (!$stmt->execute()) {
            throw new Exception('Error executing unfollow: ' . $stmt->error);
        }
        
        $response = [
            'success' => true,
            'following' => false,
            'message' => 'Successfully unfollowed'
        ];
    } else {
        // Follow
        $sql = "INSERT INTO followers (follower_id, following_id) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Error preparing follow statement: ' . $conn->error);
        }
        
        $stmt->bind_param("ii", $current_user_id, $user_id);
        if (!$stmt->execute()) {
            throw new Exception('Error executing follow: ' . $stmt->error);
        }
        
        $response = [
            'success' => true,
            'following' => true,
            'message' => 'Successfully followed'
        ];
    }
    
    if (isset($stmt)) {
        $stmt->close();
    }
    
    error_log('Response: ' . json_encode($response));
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log('Error in follow/unfollow: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
