<?php
require_once 'db.php';

// First check if display_name column exists
$result = $conn->query("SHOW COLUMNS FROM users LIKE 'display_name'");
if ($result->num_rows == 0) {
    // Add display_name column
    $sql = "ALTER TABLE users ADD COLUMN display_name VARCHAR(50)";
    if ($conn->query($sql)) {
        // Update display_name with username
        $sql = "UPDATE users SET display_name = username WHERE display_name IS NULL";
        if ($conn->query($sql)) {
        do {
            // Clear out the result sets
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->more_results() && $conn->next_result());
        
        echo "Display name column added successfully and initialized with usernames";
    } else {
        echo "Error adding display_name column: " . $conn->error;
    }
} else {
    echo "Display name column already exists";
}
?>
