<?php
require_once 'db.php';

$sql = "CREATE TABLE IF NOT EXISTS reposts (
    repost_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    thread_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (thread_id) REFERENCES threads(thread_id) ON DELETE CASCADE,
    UNIQUE KEY unique_repost (user_id, thread_id)
)";

if ($conn->query($sql)) {
    echo "Reposts table created successfully";
} else {
    echo "Error creating reposts table: " . $conn->error;
}
?>
