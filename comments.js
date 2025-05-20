document.addEventListener('DOMContentLoaded', function() {
    // Handle comment button clicks to show/hide comments
    document.querySelectorAll('.comment-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const threadCard = this.closest('.thread-card');
            const commentSection = threadCard.querySelector('.comment-section-wrapper');
            
            // Toggle comment section visibility
            if (commentSection.style.display === 'none') {
                commentSection.style.display = 'block';
            } else {
                commentSection.style.display = 'none';
            }
        });
    });

    // Auto-expand textarea as user types
    document.querySelectorAll('.comment-form textarea').forEach(textarea => {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
            
            // Enable/disable submit button
            const submitButton = this.closest('form').querySelector('.comment-submit');
            submitButton.disabled = !this.value.trim();
        });
    });

    // Handle comment form submission
    document.querySelectorAll('.comment-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const threadId = this.dataset.threadId;
            const textarea = this.querySelector('textarea');
            const preview = this.querySelector('.comment-preview');
            const formData = new FormData(this);
            formData.append('parent_id', threadId);

            fetch('api_post_comment.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Clear form
                    textarea.value = '';
                    textarea.style.height = 'auto';
                    preview.innerHTML = '';
                    this.querySelector('.comment-submit').disabled = true;

                    // Add new comment to list
                    const commentsList = document.querySelector(`#comments-${threadId} .comments-list`);
                    const newComment = createCommentElement(data.comment);
                    commentsList.insertAdjacentHTML('beforeend', newComment);

                    // Update comment count
                    const countElement = document.querySelector(`[data-thread-id="${threadId}"] .comment-count`);
                    if (countElement) {
                        countElement.textContent = parseInt(countElement.textContent || 0) + 1;
                    }
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });

    // Handle media file selection
    document.querySelectorAll('.comment-media-input').forEach(input => {
        input.addEventListener('change', function() {
            const file = this.files[0];
            if (!file) return;

            const preview = this.closest('form').querySelector('.comment-preview');
            const reader = new FileReader();

            reader.onload = function(e) {
                const type = input.dataset.type;
                let previewContent = '';

                if (type === 'image') {
                    previewContent = `<img src="${e.target.result}" alt="Preview">`;
                } else if (type === 'video') {
                    previewContent = `<video src="${e.target.result}" controls></video>`;
                } else if (type === 'audio') {
                    previewContent = `<audio src="${e.target.result}" controls></audio>`;
                }

                preview.innerHTML = `
                    <div class="media-preview">
                        ${previewContent}
                        <button type="button" class="remove-preview">×</button>
                    </div>
                `;

                // Enable submit button when media is added
                input.closest('form').querySelector('.comment-submit').disabled = false;
            };

            reader.readAsDataURL(file);
        });
    });

    // Handle removing media preview
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-preview')) {
            const preview = e.target.closest('.comment-preview');
            const form = preview.closest('form');
            preview.innerHTML = '';
            
            // Reset file inputs
            form.querySelectorAll('input[type="file"]').forEach(input => {
                input.value = '';
            });

            // Disable submit button if no text
            const textarea = form.querySelector('textarea');
            form.querySelector('.comment-submit').disabled = !textarea.value.trim();
        }
    });
});

// Helper function to create comment HTML
function createCommentElement(comment) {
    const mediaHtml = comment.media_url ? `
        <div class="comment-media">
            ${comment.media_type === 'image' ? `<img src="${comment.media_url}" alt="Comment image">` :
              comment.media_type === 'video' ? `<video src="${comment.media_url}" controls></video>` :
              comment.media_type === 'audio' ? `<audio src="${comment.media_url}" controls></audio>` : ''}
        </div>
    ` : '';

    return `
        <div class="comment-item">
            <img src="${comment.profile_pic}" alt="Profile" class="profile-pic">
            <div class="comment-content">
                <div class="comment-header">
                    <span class="username">${comment.username}</span>
                    <span class="comment-time">Just now</span>
                </div>
                <div class="comment-text">${comment.content}</div>
                ${mediaHtml}
            </div>
        </div>
    `;
}
