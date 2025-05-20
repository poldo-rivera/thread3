// Theme switching functionality
document.addEventListener('DOMContentLoaded', function() {
    const themeToggle = document.getElementById('theme-toggle');
    
    // Check for saved theme preference or default to dark
    const savedTheme = localStorage.getItem('theme') || 'dark';
    document.documentElement.setAttribute('data-theme', savedTheme);
    
    themeToggle.addEventListener('click', function() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
    });
});

// Handle thread form submission
const threadForm = document.getElementById('threadForm');
if (threadForm) {
    threadForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        try {
            const response = await fetch('api_post_thread.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            
            if (data.success) {
                location.reload();
            } else {
                alert(data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while posting the thread');
        }
    });
}

// Handle reactions
document.querySelectorAll('.reaction-btn').forEach(button => {
    button.addEventListener('click', async function() {
        const threadId = this.dataset.threadId;
        const formData = new FormData();
        formData.append('thread_id', threadId);
        formData.append('reaction_type', 'like'); // Default reaction type
        
        try {
            const response = await fetch('api_react.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            
            if (data.success) {
                location.reload();
            } else {
                alert(data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while reacting to the thread');
        }
    });
});

// Handle follow/unfollow
document.querySelectorAll('.follow-btn').forEach(button => {
    button.addEventListener('click', async function() {
        const userId = this.dataset.userId;
        const isFollowing = this.dataset.following === 'true';
        const formData = new FormData();
        formData.append('user_id', userId);
        
        try {
            const response = await fetch('api_follow.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            
            if (data.success) {
                this.textContent = isFollowing ? 'Follow' : 'Unfollow';
                this.dataset.following = (!isFollowing).toString();
                
                // Update followers count kung visible sa page
                const followersCount = document.querySelector('.profile-stats');
                if (followersCount) {
                    location.reload(); // I-refresh para makita ang bagong count
                }
            } else {
                alert(data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('May error sa pag-follow/unfollow');
        }
    });
});

// Handle comment button clicks
document.querySelectorAll('.comment-btn').forEach(button => {
    button.addEventListener('click', function() {
        const threadId = this.dataset.threadId;
        const commentSection = document.querySelector(`#comments-${threadId}`);
        
        if (commentSection.style.display === 'none' || !commentSection.style.display) {
            commentSection.style.display = 'block';
            // Load existing comments
            loadComments(threadId);
        } else {
            commentSection.style.display = 'none';
        }
    });
});

// Function para i-load ang existing comments
async function loadComments(threadId) {
    const container = document.querySelector(`#comments-container-${threadId}`);
    try {
        const response = await fetch(`api_get_comments.php?thread_id=${threadId}`);
        const data = await response.json();
        
        if (data.success) {
            container.innerHTML = data.comments.map(comment => `
                <div class="comment-card">
                    <div class="thread-header">
                        <img src="${comment.profile_pic}" alt="Profile" class="profile-pic">
                        <div class="thread-info">
                            <a href="profile.php?id=${comment.user_id}" class="username">
                                ${comment.username}
                            </a>
                            <span class="timestamp">${new Date(comment.created_at).toLocaleString()}</span>
                        </div>
                    </div>
                    <div class="thread-content">
                        ${comment.content}
                        ${comment.image_url ? `<img src="${comment.image_url}" class="thread-image">` : ''}
                    </div>
                </div>
            `).join('');
        }
    } catch (error) {
        console.error('Error:', error);
        container.innerHTML = '<p class="error">May error sa pag-load ng comments</p>';
    }
}

// Handle repost/quote button
document.querySelectorAll('.quote-btn').forEach(button => {
    button.addEventListener('click', async function() {
        const threadId = this.dataset.threadId;
        try {
            const response = await fetch(`api_get_thread.php?thread_id=${threadId}`);
            const data = await response.json();
            
            if (data.success) {
                const thread = data.thread;
                const quoteContent = `Reposted from @${thread.username}:\n${thread.content}`;
                
                // Create form data for repost
                const formData = new FormData();
                formData.append('content', quoteContent);
                formData.append('quoted_thread_id', threadId);
                
                const postResponse = await fetch('api_post_thread.php', {
                    method: 'POST',
                    body: formData
                });
                
                const postData = await postResponse.json();
                if (postData.success) {
                    location.reload();
                } else {
                    alert(postData.message);
                }
            }
        } catch (error) {
            console.error('Error:', error);
            alert('May error sa pag-repost');
        }
    });
});

// Handle comment form submissions
document.querySelectorAll('.comment-form').forEach(form => {
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        const threadId = this.dataset.threadId;
        const formData = new FormData(this);
        formData.append('thread_id', threadId);
        
        try {
            const response = await fetch('api_comment.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            
            if (data.success) {
                // Clear form
                this.reset();
                
                // Add new comment to container
                const container = document.getElementById(`comments-container-${threadId}`);
                const commentHtml = `
                    <div class="comment-card">
                        <div class="thread-header">
                            <img src="${data.comment.profile_pic}" alt="Profile" class="profile-pic">
                            <div class="thread-info">
                                <a href="profile.php?id=${data.comment.user_id}" class="username">
                                    ${data.comment.username}
                                </a>
                                <span class="timestamp">${new Date(data.comment.created_at).toLocaleString()}</span>
                            </div>
                        </div>
                        <div class="thread-content">
                            <p>${data.comment.content}</p>
                        </div>
                    </div>
                `;
                container.insertAdjacentHTML('afterbegin', commentHtml);
                
                // Update comment count
                const commentBtn = document.querySelector(`.comment-btn[data-thread-id="${threadId}"]`);
                const currentCount = parseInt(commentBtn.textContent.match(/\d+/)[0]);
                commentBtn.textContent = `💬 ${currentCount + 1}`;
            } else {
                alert(data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('May error sa pag-submit ng comment');
        }
    });
});