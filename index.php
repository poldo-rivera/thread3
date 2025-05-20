<?php
require_once 'db.php';
require_once 'auth.php';
requireLogin();

$current_user = getCurrentUser();

// Kunin ang mga threads para sa feed
$sql = "SELECT t.*, u.username, u.profile_pic,
        (SELECT COUNT(*) FROM reactions WHERE thread_id = t.thread_id) as reaction_count,
        (SELECT COUNT(*) FROM threads WHERE parent_id = t.thread_id) as reply_count
        FROM threads t
        JOIN users u ON t.user_id = u.user_id
        WHERE t.parent_id IS NULL
        ORDER BY t.created_at DESC";
$result = $conn->query($sql);

require_once 'header.php';
?>



           

    <div class="threads-container">
        <?php while ($thread = $result->fetch_assoc()): ?>
            <?php include 'thread_card.php'; ?>
        <?php endwhile; ?>
    </div>
</div>

<script>
// Voice Recording Logic
let mediaRecorder;
let audioChunks = [];
let isRecording = false;
let timer;
let seconds = 0;

document.getElementById('recordButton').addEventListener('click', async () => {
    const button = document.getElementById('recordButton');
    const timerDisplay = document.querySelector('.timer');
    
    if (!isRecording) {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            mediaRecorder = new MediaRecorder(stream);
            
            mediaRecorder.ondataavailable = (event) => {
                audioChunks.push(event.data);
            };
            
            mediaRecorder.onstop = () => {
                const audioBlob = new Blob(audioChunks, { type: 'audio/wav' });
                const audioUrl = URL.createObjectURL(audioBlob);
                
                // Create audio preview
                const audioPreview = document.createElement('audio');
                audioPreview.src = audioUrl;
                audioPreview.controls = true;
                
                const previewContainer = document.getElementById('mediaPreview');
                previewContainer.innerHTML = '';
                previewContainer.appendChild(audioPreview);
                
                // Create file input for form submission
                const audioFile = new File([audioBlob], 'voice_recording.wav', { type: 'audio/wav' });
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(audioFile);
                
                const audioInput = document.createElement('input');
                audioInput.type = 'file';
                audioInput.name = 'voice_recording';
                audioInput.style.display = 'none';
                audioInput.files = dataTransfer.files;
                
                document.getElementById('threadForm').appendChild(audioInput);
            };
            
            mediaRecorder.start();
            audioChunks = [];
            isRecording = true;
            button.textContent = '⏹️';
            
            // Start timer
            seconds = 0;
            timer = setInterval(() => {
                seconds++;
                const minutes = Math.floor(seconds / 60);
                const remainingSeconds = seconds % 60;
                timerDisplay.textContent = `${minutes.toString().padStart(2, '0')}:${remainingSeconds.toString().padStart(2, '0')}`;
            }, 1000);
            
        } catch (err) {
            console.error('Error accessing microphone:', err);
            alert('Hindi ma-access ang microphone. Pakisuri ang browser permissions.');
        }
    } else {
        mediaRecorder.stop();
        isRecording = false;
        button.textContent = '🎤';
        clearInterval(timer);
        timerDisplay.textContent = '00:00';
    }
});

// File Preview Logic
['image', 'video', 'audio'].forEach(type => {
    document.getElementById(type).addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;
        
        const previewContainer = document.getElementById('mediaPreview');
        previewContainer.innerHTML = '';
        
        if (type === 'image') {
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.style.maxWidth = '100%';
            previewContainer.appendChild(img);
        } else if (type === 'video') {
            const video = document.createElement('video');
            video.src = URL.createObjectURL(file);
            video.controls = true;
            previewContainer.appendChild(video);
        } else if (type === 'audio') {
            const audio = document.createElement('audio');
            audio.src = URL.createObjectURL(file);
            audio.controls = true;
            previewContainer.appendChild(audio);
        }
    });
});
</script>

<?php require_once 'footer.php'; ?>