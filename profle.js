document.addEventListener('DOMContentLoaded', function() {
    // Tab switching functionality
    const tabButtons = document.querySelectorAll('.tab-button');
    const contentDivs = document.querySelectorAll('.profile-content');

    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const tabName = button.getAttribute('data-tab');
            
            // Update active tab button
            tabButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            
            // Show selected content
            contentDivs.forEach(div => {
                if (div.id === `${tabName}-content`) {
                    div.classList.remove('hidden');
                } else {
                    div.classList.add('hidden');
                }
            });
        });
    });

    // Follow button functionality
    const followButton = document.querySelector('.profile-follow-button');
    if (followButton) {
        followButton.addEventListener('click', async function() {
            const userId = this.getAttribute('data-user-id');
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
