document.addEventListener('DOMContentLoaded', function() {
    // Handle repost button clicks
    document.querySelectorAll('.repost-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const threadCard = this.closest('.thread-card');
            const threadId = threadCard.dataset.threadId;
            const countElement = this.querySelector('.action-count');

            // Send repost request
            const formData = new FormData();
            formData.append('thread_id', threadId);

            fetch('api_repost_thread.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update repost count
                    countElement.textContent = data.repost_count;

                    // Toggle active state
                    if (data.action === 'added') {
                        button.classList.add('active');
                    } else {
                        button.classList.remove('active');
                    }
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });
});
