document.getElementById('uploadForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);
    const xhr = new XMLHttpRequest();
    const progressContainer = document.getElementById('progressContainer');
    const progressBar = document.getElementById('uploadProgress');
    const statusText = document.getElementById('uploadStatus');
    let startTime = null;

    // upload.php uploads the file and returns a JSON response
    xhr.open('POST', 'upload.php', true);
    xhr.upload.onprogress = function(event) {
        if (!startTime) startTime = new Date().getTime();
        if (event.lengthComputable) {
            const now = new Date().getTime();
            const elapsedTime = (now - startTime) / 1000; // seconds
            const uploadedMB = event.loaded / (1024 * 1024);
            const totalMB = event.total / (1024 * 1024);
            const percent = (event.loaded / event.total) * 100;

            // Upload speed in MB/s
            const speed = uploadedMB / elapsedTime;

            // Remaining time in seconds
            const remainingMB = totalMB - uploadedMB;
            const etaSeconds = remainingMB / speed;

            // Format ETA
            const etaMinutes = Math.floor(etaSeconds / 60);
            const etaSecondsFormatted = Math.floor(etaSeconds % 60);
            const etaString = `${etaMinutes > 0 ? etaMinutes + 'm ' : ''}${etaSecondsFormatted}s`;

            // Update progress bar and status text
            progressBar.value = percent;
            statusText.textContent = `Uploading: ${uploadedMB.toFixed(2)}MB / ${totalMB.toFixed(2)}MB (${percent.toFixed(1)}%) | Speed: ${speed.toFixed(2)}MB/s | ETA: ${etaString}`;
        }
    };

    // When upload starts
    xhr.onloadstart = function() {
        progressContainer.style.display = 'block';
        progressBar.value = 0;
        statusText.textContent = 'Starting upload...';
        startTime = new Date().getTime();
    };

    // When upload completes
    xhr.onload = function () {
        try {
            const res = JSON.parse(xhr.responseText);
            if (res.success) {
                statusText.textContent = '✅ Upload complete!';
                setTimeout(() => {
                    window.location.href = "BDrive.php?folder=" + 
                    encodeURIComponent(form.querySelector('input[name="folder"]').value.replace(/^files/, ''));
                }, 1500);     
            } else {
                statusText.textContent = '❌' + res.errors.join('\n');
            }
        } catch (err) {
            statusText.textContent = '❌ Invalid server response.';
        }
    };
    
    // On error
    xhr.onerror = function() {
        statusText.textContent = '❌ Error during upload.';
    };
    
    xhr.send(formData);
});

   