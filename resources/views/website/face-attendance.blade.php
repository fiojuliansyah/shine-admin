<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi Wajah Fullscreen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --color-primary: #059669;
            --color-secondary: #dc2626;
        }

        /* 🎯 PERUBAHAN UTAMA: Mencegah Scrolling */
        html, body {
            overflow: hidden; /* Mencegah scrolling */
            width: 100%;
            height: 100%;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0fdfa;
            transition: background-color 0.3s;
        }

        :fullscreen {
            background-color: #f0fdfa;
        }

        /* Container untuk Fullscreen Video */
        .camera-fullscreen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 10;
        }

        /* Log Transparan di Atas Video */
        .transparent-log-overlay {
            position: absolute;
            top: 60px; /* Di bawah header */
            right: 15px;
            width: 350px;
            max-width: 90%;
            z-index: 20;
            background-color: rgba(255, 255, 255, 0.9); /* Latar belakang semi-transparan */
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            border-radius: 12px;
            padding: 15px;
        }
        
        #video-element,
        #face-preview {
            transform: scaleX(-1);
            width: 100%; 
            height: 100%;
            object-fit: cover;
        }

        #face-overlay {
            width: 100%;
            height: 100%;
            position: absolute;
            top: 0;
            left: 0;
        }

        /* Gaya Khusus untuk Log Content */
        #status-log {
            max-height: 250px; 
            overflow-y: auto;
            border-radius: 8px;
            background-color: rgba(249, 250, 251, 0.7); 
        }
    </style>
</head>

<body>
    <header class="header-fixed fixed top-0 left-0 w-full h-14 flex items-center justify-center z-50">
        <span class="text-lg font-bold text-gray-100">Absensi Wajah</span>
        <a href="#" class="absolute left-4 text-xl text-gray-100 hover:text-gray-300 transition"><i
                class="fas fa-arrow-left"></i></a>
        <button id="fullscreen-btn"
            class="absolute right-4 text-gray-100 hover:text-emerald-600 transition p-2 rounded-full">
            <i class="fas fa-expand-alt"></i>
        </button>
    </header>

    <div id="camera-container" class="camera-fullscreen relative bg-gray-900">
        <video id="video-element" autoplay muted playsinline></video>
        <canvas id="face-overlay"></canvas>
        <img id="face-preview" class="hidden" alt="Captured Face Preview" />
    </div>

    <div id="log-overlay" class="transparent-log-overlay">
        
        <h5 class="text-xl font-bold text-gray-800 border-b pb-2 mb-3">Status & Log Absensi</h5>

        <div id="realtime-attendance-status" class="space-y-3">
            
            <div id="status-display" class="p-3 border rounded-lg bg-white shadow-sm">
                <p id="message-status" class="text-sm font-medium text-gray-700">Memuat model...</p>
                <div id="loading-status" class="mt-1 text-emerald-600">
                    <i class="fas fa-sync-alt fa-spin text-xl"></i>
                </div>
            </div>

            <div class="space-y-1">
                <p class="text-sm font-semibold text-gray-600">Log Transaksi:</p>
                <div id="status-log" class="p-2 border border-gray-300">
                    Pilih aksi Clock In/Out untuk memulai.
                </div>
            </div>
            
            <div class="flex space-x-2 pt-3 border-t border-gray-200">
                <button type="button" id="start-clock-in-btn"
                    class="w-1/3 py-2 bg-blue-600 text-white rounded-lg font-bold text-xs disabled:opacity-50">
                    <i class="fas fa-sign-in-alt"></i> IN
                </button>
                <button type="button" id="start-clock-out-btn"
                    class="w-1/3 py-2 bg-red-600 text-white rounded-lg font-bold text-xs disabled:opacity-50">
                    <i class="fas fa-sign-out-alt"></i> OUT
                </button>
                <button type="button" id="stop-scan-btn"
                    class="w-1/3 py-2 bg-gray-400 text-white rounded-lg font-bold text-xs hidden">
                    <i class="fas fa-stop"></i> Stop
                </button>
            </div>
        </div>
    </div>
    
    <form id="attendance-form" action="{{ route('face.attendance.process') }}" method="POST" style="display: none;">
        @csrf
        <input type="hidden" id="face-descriptor-input" name="face_descriptor" value="">
        <input type="hidden" id="face-image-data-input" name="image" value="">
        <input type="hidden" id="latlong-input" name="latlong" value="0,0">
        <input type="hidden" id="attendance-mode-input" name="mode" value="">
        <input type="hidden" name="employee_nik" id="employee-nik-input" value="MULTI_SCAN_MODE">
        <button type="submit" id="final-submit-btn"></button>
    </form>

    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <script>
        const videoElement = document.getElementById('video-element');
        const faceOverlay = document.getElementById('face-overlay');
        const messageStatus = document.getElementById('message-status');
        const loadingStatus = document.getElementById('loading-status');
        const startClockInBtn = document.getElementById('start-clock-in-btn');
        const startClockOutBtn = document.getElementById('start-clock-out-btn');
        const stopScanBtn = document.getElementById('stop-scan-btn');
        const finalSubmitBtn = document.getElementById('final-submit-btn');
        const faceDescriptorInput = document.getElementById('face-descriptor-input');
        const faceImageDataInput = document.getElementById('face-image-data-input');
        const facePreview = document.getElementById('face-preview');
        const fullscreenBtn = document.getElementById('fullscreen-btn');
        const latlongInput = document.getElementById('latlong-input');
        const statusLog = document.getElementById('status-log');
        const attendanceModeInput = document.getElementById('attendance-mode-input');
        const employeeNikInput = document.getElementById('employee-nik-input');

        const minFaceSize = 10;
        const outputSize = 400;
        const detectionInterval = 200;

        let isModelLoaded = false;
        let stream = null;
        let isCapturing = false;
        let isReady = false;
        let captureInterval = null;
        let successfulAttendanceCooldown = {};
        let lastDetections = [];

        function logStatus(message, type = 'info', imageUrl = null) {
            const logContainer = statusLog;
            const now = new Date().toLocaleTimeString();
            let color = 'text-gray-700';

            if (type === 'success') color = 'text-green-600 font-semibold';
            if (type === 'error') color = 'text-red-600 font-semibold';
            if (type === 'warning') color = 'text-yellow-600 font-semibold';

            const newEntry = document.createElement('p');
            newEntry.className = `text-xs ${color} flex items-center mb-1`;
            
            let htmlContent = `[${now}] ${message}`;
            
            if (imageUrl) {
                htmlContent = `<img src="${imageUrl}" style="width: 80px; height: 80px; border-radius: 4px; object-fit: cover; margin-right: 8px;"> ${htmlContent}`;
            }

            newEntry.innerHTML = htmlContent;
            logContainer.prepend(newEntry);
            if (logContainer.children.length > 50) {
                logContainer.removeChild(logContainer.lastChild);
            }
        }

        function toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(err => {
                    console.error(`Error attempting to enable full-screen mode: ${err.message} (${err.name})`);
                });
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
            }
        }

        function updateFullscreenIcon() {
            if (document.fullscreenElement) {
                fullscreenBtn.innerHTML = '<i class="fas fa-compress-alt"></i>';
            } else {
                fullscreenBtn.innerHTML = '<i class="fas fa-expand-alt"></i>';
            }
        }

        function checkPrerequisites() {
            const isSystemReady = isModelLoaded && isReady;

            if (isCapturing) {
                startClockInBtn.classList.add('hidden');
                startClockOutBtn.classList.add('hidden');
                stopScanBtn.classList.remove('hidden');
                stopScanBtn.textContent = `Hentikan Scan (${attendanceModeInput.value.toUpperCase()})`;
            } else {
                startClockInBtn.classList.remove('hidden');
                startClockOutBtn.classList.remove('hidden');
                stopScanBtn.classList.add('hidden');
                startClockInBtn.disabled = !isSystemReady;
                startClockOutOutBtn.disabled = !isSystemReady;
            }

            finalSubmitBtn.disabled = true;
        }

        function stopCamera() {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                stream = null;
            }
        }

        function stopContinuousScan() {
            if (captureInterval) {
                clearTimeout(captureInterval);
                captureInterval = null;
            }
            isCapturing = false;
            stopCamera();
            attendanceModeInput.value = ''; 
            logStatus("Sesi absensi dihentikan.", 'warning');
            checkPrerequisites();
        }

        function loadModels() {
            try {
                return Promise.all([
                    faceapi.nets.ssdMobilenetv1.loadFromUri('/models'),
                    faceapi.nets.faceLandmark68Net.loadFromUri('/models'),
                    faceapi.nets.faceRecognitionNet.loadFromUri('/models')
                ]).then(() => {
                    isModelLoaded = true;
                    return true;
                });
            } catch (error) {
                return false;
            }
        }

        async function startCamera() {
            try {
                const constraints = {
                    video: {
                        facingMode: 'user'
                    }
                };
                stream = await navigator.mediaDevices.getUserMedia(constraints);
                videoElement.srcObject = stream;

                await new Promise(resolve => {
                    videoElement.onloadedmetadata = () => {
                        const videoRatio = videoElement.videoWidth / videoElement.videoHeight;
                        const container = document.getElementById('camera-container');

                        faceOverlay.width = videoElement.offsetWidth;
                        faceOverlay.height = videoElement.offsetHeight;
                        resolve();
                    };
                });
                return true;
            } catch (error) {
                return false;
            }
        }

        function cropAndDrawFace(detections) {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');

            const img = videoElement;

            const faceBox = detections[0].detection.box;

            const imgWidth = img.videoWidth;
            const imgHeight = img.videoHeight;

            const faceSize = Math.max(faceBox.width, faceBox.height);
            const cropSize = Math.min(imgWidth, imgHeight, Math.round(faceSize * 1.5));

            const centerX = faceBox.x + faceBox.width / 2;
            const centerY = faceBox.y + faceBox.height / 2;

            let cropX = centerX - cropSize / 2;
            let cropY = centerY - cropSize / 2;

            cropX = Math.max(0, Math.min(imgWidth - cropSize, cropX));
            cropY = Math.max(0, Math.min(imgHeight - cropSize, cropY));

            canvas.width = outputSize;
            canvas.height = outputSize;

            ctx.drawImage(
                img,
                cropX, cropY, cropSize, cropSize,
                0, 0, outputSize, outputSize
            );

            return canvas;
        }

        async function detectionLoop() {
            const currentMode = attendanceModeInput.value;
            if (!currentMode || !isCapturing || !isReady) return;

            const detections = await faceapi.detectAllFaces(videoElement)
                .withFaceLandmarks()
                .withFaceDescriptors();

            lastDetections = detections; 
            lastDetectionStatus = {};

            if (detections.length > 0) {
                messageStatus.textContent = `MODE ${currentMode.toUpperCase()}: Terdeteksi ${detections.length} wajah.`;
                
                for (let i = 0; i < detections.length; i++) {
                    const detection = detections[i];
                    const faceDetection = detection.detection;

                    if (faceDetection.box.width < minFaceSize) {
                        lastDetectionStatus[i] = 'red';
                        continue;
                    }

                    const descriptorArray = Array.from(detection.descriptor);
                    const faceHash = descriptorArray.slice(0, 5).join('-');

                    if (successfulAttendanceCooldown[faceHash] && successfulAttendanceCooldown[faceHash].time > Date.now()) {
                        lastDetectionStatus[i] = 'lime';
                        continue;
                    }

                    const finalImageBase64 = cropAndDrawFace([detection]).toDataURL('image/jpeg', 0.9);

                    const formData = new FormData();
                    formData.append('_token', document.querySelector('input[name="_token"]').value);
                    formData.append('employee_nik', employeeNikInput.value);
                    formData.append('face_descriptor', JSON.stringify(descriptorArray));
                    formData.append('image', finalImageBase64);
                    formData.append('latlong', '0,0');
                    formData.append('mode', currentMode);

                    try {
                        const response = await fetch('{{ route('face.attendance.process') }}', {
                            method: 'POST',
                            body: formData,
                        });
                        
                        const responseText = await response.text();
                        
                        let data;
                        try {
                            data = JSON.parse(responseText);
                        } catch (e) {
                            logStatus(`Wajah #${i+1} - Gagal Server (Bukan JSON). Cek Controller.`, 'error');
                            lastDetectionStatus[i] = 'error_red';
                            continue; 
                        }

                        if (response.ok && data.success) {
                            logStatus(`✅ ${data.success}`, 'success', finalImageBase64);
                            lastDetectionStatus[i] = 'lime';

                            successfulAttendanceCooldown[faceHash] = {
                                time: Date.now() + (60000), 
                                nik: data.nik || 'UNKNOWN'
                            };
                        } else if (data.error) {
                            if (data.error.includes('Wajah tidak cocok')) {
                                logStatus(`Wajah #${i+1} - ❌ TIDAK TERDAFTAR: ${data.error}`, 'error');
                                lastDetectionStatus[i] = 'red';
                            } else if (data.error.includes('sudah Clock Out') || data.error.includes(
                                'sudah Clock In')) {
                                
                                lastDetectionStatus[i] = 'lime'; 

                                successfulAttendanceCooldown[faceHash] = {
                                    time: Date.now() + (60000),
                                    nik: data.nik || 'UNKNOWN'
                                };
                            } else {
                                logStatus(`Wajah #${i+1} - ❌ GAGAL KRITIS: ${data.error}`, 'error');
                                lastDetectionStatus[i] = 'red';
                            }
                        } else {
                            logStatus(`Wajah #${i+1} - Status tidak dikenal.`, 'error');
                            lastDetectionStatus[i] = 'red';
                        }

                    } catch (error) {
                        logStatus(`Wajah #${i+1} - Gagal koneksi: ${error.message}`, 'error');
                        lastDetectionStatus[i] = 'error_red';
                    }
                }
            } else {
                messageStatus.textContent = `MODE ${currentMode.toUpperCase()}: Tidak ada wajah terdeteksi.`;
            }

            captureInterval = setTimeout(detectionLoop, detectionInterval); 
        }

        function renderLoop() {
            if (!isCapturing || !stream) return; 

            const ctx = faceOverlay.getContext('2d', { willReadFrequently: true });
            const currentMode = attendanceModeInput.value;
            ctx.clearRect(0, 0, faceOverlay.width, faceOverlay.height);

            const displaySize = {
                width: videoElement.offsetWidth,
                height: videoElement.offsetHeight
            };
            
            const resizedDetections = faceapi.resizeResults(lastDetections, displaySize);

            if (resizedDetections.length > 0) {
                for (let i = 0; i < resizedDetections.length; i++) {
                    const detection = resizedDetections[i].detection;
                    const faceSize = detection.box.width;
                    
                    let status = lastDetectionStatus[i] || (faceSize < minFaceSize ? 'red' : 'lime');
                    let boxColor = status === 'red' || status === 'error_red' ? 'red' : 'lime';
                    let label = status === 'red' ? 'TIDAK COCOK' : status === 'error_red' ? 'ERROR' : currentMode.toUpperCase();

                    ctx.save();
                    ctx.scale(-1, 1);
                    ctx.translate(-faceOverlay.width, 0);
                    new faceapi.draw.DrawBox(detection.box, { boxColor: boxColor, label: label }).draw(faceOverlay);
                    ctx.restore();
                }
            }
            
            requestAnimationFrame(renderLoop);
        }

        async function startSession(mode) {
            if (isCapturing) return;

            attendanceModeInput.value = mode;

            startClockInBtn.disabled = true;
            startClockOutBtn.disabled = true;
            
            if (!stream) {
                if (!await startCamera()) {
                    logStatus("Gagal memulai kamera.", 'error');
                    checkPrerequisites();
                    return;
                }
            }
            
            videoElement.style.display = 'block';
            faceOverlay.style.display = 'block';
            facePreview.classList.add('hidden');

            isCapturing = true;
            logStatus(`Sesi ${mode.toUpperCase()} dimulai. Memindai wajah...`, 'info');
            
            requestAnimationFrame(renderLoop); 
            detectionLoop();

            checkPrerequisites();
        }

        async function initialize() {
            loadingStatus.style.display = 'inline-block';

            messageStatus.textContent = 'Memuat model AI...';
            const modelsLoaded = await loadModels();

            if (!modelsLoaded) {
                loadingStatus.style.display = 'none';
                messageStatus.textContent = 'Gagal memuat model Face ID.';
                logStatus('Error: Model Face ID gagal dimuat.', 'error');
                return;
            }

            isReady = true;
            messageStatus.textContent = 'Model siap. Memulai kamera...';
            
            const cameraStarted = await startCamera(); 
            stopCamera(); 

            loadingStatus.style.display = 'none';

            if (cameraStarted) {
                messageStatus.textContent = 'Kamera & Sistem siap. Pilih Clock In atau Clock Out.';
            } else {
                messageStatus.textContent = 'Gagal mengakses kamera.';
                logStatus('Error: Pastikan Anda memberikan izin kamera.', 'error');
            }

            checkPrerequisites();
        }

        document.addEventListener('DOMContentLoaded', () => initialize());
        startClockInBtn.addEventListener('click', () => startSession('clockin'));
        startClockOutBtn.addEventListener('click', () => startSession('clockout'));
        stopScanBtn.addEventListener('click', stopContinuousScan);
        fullscreenBtn.addEventListener('click', toggleFullscreen);

        document.addEventListener('fullscreenchange', updateFullscreenIcon);
        window.addEventListener('beforeunload', stopCamera);

        document.getElementById('attendance-form').addEventListener('submit', function(e) {
            e.preventDefault();
            logStatus("Form submit diabaikan, menggunakan AJAX scanning.", 'info');
        });
    </script>
</body>

</html>