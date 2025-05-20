<?php
require_once 'db.php';

// Add media_type and media_url columns if they don't exist
$alter_sql = "ALTER TABLE threads 
              ADD COLUMN IF NOT EXISTS media_type VARCHAR(10) NULL,
              ADD COLUMN IF NOT EXISTS media_url VARCHAR(255) NULL";

if ($conn->query($alter_sql)) {
    echo "Database updated successfully";
} else {
    echo "Error updating database: " . $conn->error;
}
?>
