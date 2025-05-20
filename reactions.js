document.addEventListener('DOMContentLoaded', function() {
    // Handle reaction button clicks
    document.querySelectorAll('.react-button').forEach(button => {
        button.addEventListener('click', function() {
            const threadId = this.dataset.threadId;
            const countElement = this.querySelector('.action-count');
            
            // Send reaction to server
            const formData = new FormData();
            formData.append('thread_id', threadId);

            fetch('api_react_thread.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update reaction count
                    countElement.textContent = data.reaction_count;
                    
                    // Toggle reacted class
                    if (data.action === 'react') {
                        this.classList.add('reacted');
                    } else {
                        this.classList.remove('reacted');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    });
});
