<?php
// Create Post Modal
?>
<!-- Post Creation Modal -->
<div class="modal" id="createPostModal">
    <div class="modal-overlay"></div>
    <div class="modal-container">
        <!-- Modal Header -->
        <div class="modal-header">
            <button class="close-modal">
                <svg viewBox="0 0 24 24" width="18" height="18">
                    <path fill="currentColor" d="M10.59 12L4.54 5.96l1.42-1.42L12 10.59l6.04-6.05 1.42 1.42L13.41 12l6.05 6.04-1.42 1.42L12 13.41l-6.04 6.05-1.42-1.42L10.59 12z"/>
                </svg>
            </button>
            <div class="modal-title">New thread</div>
            <button class="post-button" disabled>Post</button>
        </div>

        <!-- Modal Content -->
        <div class="modal-content">
            <!-- User Info -->
            <div class="user-info">
                <img src="<?php echo getCurrentUser()['profile_pic']; ?>" alt="Profile" class="profile-pic">
                <div class="post-info">
                    <div class="username"><?php echo getCurrentUser()['username']; ?></div>
                </div>
            </div>

            <!-- Post Form -->
            <form id="postForm" class="post-form" enctype="multipart/form-data">
                <textarea name="content" placeholder="Start a thread..." rows="4"></textarea>
                
                <!-- Media Preview Section -->
                <div id="mediaPreview" class="media-preview" style="display: none;">
                    <div class="preview-container">
                        <img id="imagePreview" style="display: none; max-width: 100%; border-radius: 16px;">
                        <video id="videoPreview" style="display: none; max-width: 100%; border-radius: 16px;" controls></video>
                        <audio id="audioPreview" style="display: none; width: 100%;" controls></audio>
                    </div>
                    <!-- Remove Media Button -->
                    <button type="button" class="remove-media" onclick="removeMedia()">
                        <svg viewBox="0 0 24 24" width="18" height="18">
                            <path fill="currentColor" d="M10.59 12L4.54 5.96l1.42-1.42L12 10.59l6.04-6.05 1.42 1.42L13.41 12l6.05 6.04-1.42 1.42L12 13.41l-6.04 6.05-1.42-1.42L10.59 12z"/>
                        </svg>
                    </button>
                </div>

                <!-- Media Upload Buttons -->
                <div class="media-options">
                    <!-- Image Upload Button -->
                    <button type="button" class="media-button" data-type="image">
                        <svg viewBox="0 0 24 24" width="24" height="24">
                            <path fill="currentColor" d="M3 5.5C3 4.119 4.119 3 5.5 3h13C19.881 3 21 4.119 21 5.5v13c0 1.381-1.119 2.5-2.5 2.5h-13C4.119 21 3 19.881 3 18.5v-13zM5.5 5c-.276 0-.5.224-.5.5v9.086l3-3 3 3 5-5 3 3V5.5c0-.276-.224-.5-.5-.5h-13zM19 15.414l-3-3-5 5-3-3-3 3v1.086c0 .276.224.5.5.5h13c.276 0 .5-.224.5-.5v-3.086zM9.75 7C8.784 7 8 7.784 8 8.75s.784 1.75 1.75 1.75 1.75-.784 1.75-1.75S10.716 7 9.75 7z"/>
                        </svg>
                    </button>
                    <!-- Video Upload Button -->
                    <button type="button" class="media-button" data-type="video">
                        <svg viewBox="0 0 24 24" width="24" height="24">
                            <path fill="currentColor" d="M2 4.5C2 3.12 3.12 2 4.5 2h15C20.88 2 22 3.12 22 4.5v15c0 1.38-1.12 2.5-2.5 2.5h-15C3.12 22 2 20.88 2 19.5v-15zM4.5 4c-.28 0-.5.22-.5.5v15c0 .28.22.5.5.5h15c.28 0 .5-.22.5-.5v-15c0-.28-.22-.5-.5-.5h-15zM14.58 12L9 8v8l5.58-4z"/>
                        </svg>
                    </button>
                    <!-- Audio Upload Button -->
                    <button type="button" class="media-button" data-type="audio">
                        <svg viewBox="0 0 24 24" width="24" height="24">
                            <path fill="currentColor" d="M12 3c.45 0 .81.37.81.83v15.66c0 .45-.36.83-.81.83s-.81-.37-.81-.83V3.83c0-.45.36-.83.81-.83zm-6.5 4.5c.45 0 .81.37.81.83v6.67c0 .45-.36.83-.81.83s-.81-.37-.81-.83V8.33c0-.45.36-.83.81-.83zm13 0c.45 0 .81.37.81.83v6.67c0 .45-.36.83-.81.83s-.81-.37-.81-.83V8.33c0-.45.36-.83.81-.83z"/>
                        </svg>
                    </button>
                </div>

                <!-- Hidden File Inputs -->
                <input type="file" name="image" id="imageInput" accept="image/*" style="display: none">
                <input type="file" name="video" id="videoInput" accept="video/*" style="display: none">
                <input type="file" name="audio" id="audioInput" accept="audio/*" style="display: none">
            </form>
        </div>
    </div>
</div>
