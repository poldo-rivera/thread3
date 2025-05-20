<?php
require_once 'db.php';
require_once 'auth.php';
requireLogin();

$user_id = getCurrentUserId();
$error = '';
$success = '';

// Kunin ang current user info
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bio = trim($_POST['bio'] ?? '');
    $username = trim($_POST['username'] ?? '');

    // Validation para sa username
    if (empty($username)) {
        $error = 'Kailangan ilagay ang username.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username)) {
        $error = 'Ang username ay dapat 3-30 characters, letters, numbers, o underscore lang.';
    } else {
        // Check kung may ibang gumagamit ng username
        $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? AND user_id != ?");
        $check_stmt->bind_param("si", $username, $user_id);
        $check_stmt->execute();
        $check_stmt->store_result();
        if ($check_stmt->num_rows > 0) {
            $error = 'Ang username na ito ay ginagamit na ng iba.';
        }
    }

    // Handle profile picture upload kung walang error sa username
    if (!$error) {
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['profile_pic']['name'];
            $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($filetype, $allowed)) {
                // Create uploads directory kung wala pa
                $upload_dir = 'uploads/profile_pictures/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                // Generate unique filename
                $new_filename = $upload_dir . 'profile_' . $user_id . '_' . time() . '.' . $filetype;
                
                if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $new_filename)) {
                    // Delete old profile picture kung meron
                    if (!empty($user['profile_pic']) && file_exists($user['profile_pic'])) {
                        unlink($user['profile_pic']);
                    }
                    
                    // Update database (kasama username, bio, at profile_pic)
                    $stmt = $conn->prepare("UPDATE users SET username = ?, profile_pic = ?, bio = ? WHERE user_id = ?");
                    $stmt->bind_param("sssi", $username, $new_filename, $bio, $user_id);
                    
                    if ($stmt->execute()) {
                        $success = 'Matagumpay na na-update ang profile!';
                        $user['profile_pic'] = $new_filename;
                        $user['bio'] = $bio;
                        $user['username'] = $username;
                    } else {
                        $error = 'May error sa pag-update ng database.';
                    }
                } else {
                    $error = 'May error sa pag-upload ng profile picture.';
                }
            } else {
                $error = 'Hindi supported ang file type. Dapat JPG, JPEG, PNG, o GIF lang.';
            }
        } else {
            // Update username at bio lang kung walang bagong picture
            $stmt = $conn->prepare("UPDATE users SET username = ?, bio = ? WHERE user_id = ?");
            $stmt->bind_param("ssi", $username, $bio, $user_id);
            
            if ($stmt->execute()) {
                $success = 'Matagumpay na na-update ang profile!';
                $user['bio'] = $bio;
                $user['username'] = $username;
            } else {
                $error = 'May error sa pag-update ng profile.';
            }
        }
    }
}

require_once 'header.php';
?>

<div class="edit-profile-container">
    <h2>I-edit ang Profile</h2>
    
    <?php if ($error): ?>
        <div class="error-message"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="success-message"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="edit-profile-form">
        <div class="current-profile">
            <img src="<?php echo !empty($user['profile_pic']) ? $user['profile_pic'] : 'default_profile.png'; ?>" 
                 alt="Current Profile Picture" 
                 class="current-profile-pic">
        </div>
        
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required maxlength="30"
                   value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>">
            <small>3-30 characters, letters, numbers, o underscore lang.</small>
        </div>

        <div class="form-group">
            <label for="profile_pic">Pumili ng Profile Picture:</label>
            <input type="file" id="profile_pic" name="profile_pic" accept="image/*">
            <small>Supported formats: JPG, JPEG, PNG, GIF</small>
        </div>

        <div class="form-group">
            <label for="bio">Bio:</label>
            <textarea id="bio" name="bio" rows="4" placeholder="Maglagay ng bio..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
        </div>

        <button type="submit" class="save-btn">I-save ang Changes</button>
    </form>
</div>

<style>
.edit-profile-container {
    max-width: 600px;
    margin: 2rem auto;
    padding: 2rem;
    background-color: var(--card-bg);
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.edit-profile-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.current-profile {
    text-align: center;
    margin-bottom: 1rem;
}

.current-profile-pic {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid var(--accent-color);
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    color: var(--text-color);
}

.form-group input[type="file"],
.form-group input[type="text"] {
    width: 100%;
    padding: 0.5rem;
    background-color: var(--input-bg);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    color: var(--text-color);
}

.form-group textarea {
    width: 100%;
    padding: 0.75rem;
    background-color: var(--input-bg);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    color: var(--text-color);
    resize: vertical;
}

.save-btn {
    background-color: var(--accent-color);
    color: white;
    padding: 0.75rem;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    transition: background-color 0.3s ease;
}

.save-btn:hover {
    background-color: var(--accent-hover);
}

.error-message {
    background-color: #ffebee;
    color: #c62828;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
}

.success-message {
    background-color: #e8f5e9;
    color: #2e7d32;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
}

small {
    color: var(--secondary-text);
    font-size: 0.875rem;
}
</style>

<?php require_once 'footer.php'; ?>