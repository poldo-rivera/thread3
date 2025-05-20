/**
 * Thread Post Creation Modal
 * Handles the creation and submission of new thread posts with media attachments
 */
document.addEventListener('DOMContentLoaded', function() {
    // Get DOM elements
    const modal = document.getElementById('createPostModal');
    const modalOverlay = modal.querySelector('.modal-overlay');
    const closeButton = modal.querySelector('.close-modal');
    const postButton = modal.querySelector('.post-button');
    const postForm = document.getElementById('postForm');
    const textarea = postForm.querySelector('textarea');
    const mediaButtons = modal.querySelectorAll('.media-button');

    // Media preview elements
    const mediaPreview = document.getElementById('mediaPreview');
    const imagePreview = document.getElementById('imagePreview');
    const videoPreview = document.getElementById('videoPreview');
    const audioPreview = document.getElementById('audioPreview');

    // File input elements
    const imageInput = document.getElementById('imageInput');
    const videoInput = document.getElementById('videoInput');
    const audioInput = document.getElementById('audioInput');

    /**
     * Modal Open/Close Handlers
     */
    document.querySelector('.create-post-button').addEventListener('click', function() {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
    });

    modalOverlay.addEventListener('click', closeModal);
    closeButton.addEventListener('click', closeModal);

    /**
     * Close the modal and reset form
     */
    function closeModal() {
        modal.classList.remove('active');
        document.body.style.overflow = ''; // Restore scrolling
        resetForm();
    }

    /**
     * Reset form and clear all media previews
     */
    function resetForm() {
        postForm.reset();
        mediaPreview.style.display = 'none';
        imagePreview.style.display = 'none';
        videoPreview.style.display = 'none';
        audioPreview.style.display = 'none';
        imagePreview.src = '';
        videoPreview.src = '';
        audioPreview.src = '';
        postButton.disabled = true;
    }

    /**
     * Enable/disable post button based on content
     */
    textarea.addEventListener('input', function() {
        postButton.disabled = !this.value.trim();
    });

    /**
     * Media Upload Button Handlers
     */
    mediaButtons.forEach(button => {
        button.addEventListener('click', function() {
            const type = this.dataset.type;
            const input = document.getElementById(type + 'Input');
            input.click(); // Trigger file input
        });
    });

    /**
     * Handle file selection and preview
     * @param {Event} event - File input change event
     * @param {string} type - Type of media ('image', 'video', or 'audio')
     */
    function handleFileSelect(event, type) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            mediaPreview.style.display = 'block';
            postButton.disabled = false;

            reader.onload = function(e) {
                // Hide all previews first
                imagePreview.style.display = 'none';
                videoPreview.style.display = 'none';
                audioPreview.style.display = 'none';

                // Show appropriate preview based on media type
                if (type === 'image') {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                } else if (type === 'video') {
                    videoPreview.src = e.target.result;
                    videoPreview.style.display = 'block';
                } else if (type === 'audio') {
                    audioPreview.src = e.target.result;
                    audioPreview.style.display = 'block';
                }
            };

            // Read file as data URL
            reader.readAsDataURL(file);
        }
    }

    // Add file change listeners
    imageInput.addEventListener('change', e => handleFileSelect(e, 'image'));
    videoInput.addEventListener('change', e => handleFileSelect(e, 'video'));
    audioInput.addEventListener('change', e => handleFileSelect(e, 'audio'));

    /**
     * Remove media and reset form
     */
    window.removeMedia = function() {
        resetForm();
    };

    /**
     * Handle post submission
     * Submits form data with any media attachments to the server
     */
    postButton.addEventListener('click', function() {
        const formData = new FormData(postForm);

        fetch('api_post_thread.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeModal();
                location.reload(); // Refresh to show new post
            } else {
                alert('Error posting thread: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error posting thread');
        });
    });
});
