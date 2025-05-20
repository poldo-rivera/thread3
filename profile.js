document.addEventListener('DOMContentLoaded', function() {
    // Tab switching
    const tabButtons = document.querySelectorAll('.tab-button');
    const contents = document.querySelectorAll('[id$="-content"]');

    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Remove active class from all buttons and contents
            tabButtons.forEach(btn => btn.classList.remove('active'));
            contents.forEach(content => content.style.display = 'none');

            // Add active class to clicked button and show corresponding content
            button.classList.add('active');
            const contentId = `${button.dataset.tab}-content`;
            document.getElementById(contentId).style.display = 'block';
        });
    });

    // Follow button functionality
    const followButton = document.querySelector('.profile-follow-button');
    if (followButton) {
        followButton.addEventListener('click', async function() {
            const userId = this.dataset.userId;
            const isFollowing = this.classList.contains('following');

            try {
                const response = await fetch('api_follow.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        user_id: userId,
                        action: isFollowing ? 'unfollow' : 'follow'
                    })
                });

                if (response.ok) {
                    // Toggle button state
                    this.classList.toggle('following');
                    this.textContent = isFollowing ? 'Follow' : 'Following';
                    
                    // Update followers count
                    const statsCount = document.querySelector('.stats-count');
                    if (statsCount) {
                        let count = parseInt(statsCount.textContent.replace(/,/g, ''));
                        count = isFollowing ? count - 1 : count + 1;
                        statsCount.textContent = new Intl.NumberFormat().format(count);
                    }
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });
    }
});
