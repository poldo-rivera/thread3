<?php
require_once 'db.php';

$sql = "CREATE TABLE IF NOT EXISTS followers (
    follower_id INT NOT NULL,
    following_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (follower_id, following_id),
    FOREIGN KEY (follower_id) REFERENCES users(user_id),
    FOREIGN KEY (following_id) REFERENCES users(user_id)
)";

if ($conn->query($sql)) {
    echo "Followers table created successfully";
} else {
    echo "Error creating followers table: " . $conn->error;
}
?>
