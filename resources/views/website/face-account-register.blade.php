<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Akun & Face ID</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --color-primary: #059669; /* Emerald */
            --color-accent: #34d399; /* Emerald Light */
            --color-secondary: #10b981; /* Emerald Darker */
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0fdfa; /* Very Light Mint Background */
            transition: background-color 0.3s;
        }

        /* Fullscreen Mode Styling */
        :fullscreen {
            background-color: #f0fdfa;
        }

        .header-fixed {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        /* Gradient Card Style (Applied via Tailwind classes below) */
        .gradient-card {
            background-image: linear-gradient(to bottom right, #ffffff, #f0fdfa);
            transition: box-shadow 0.3s;
        }
        .gradient-card:hover {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .input-style {
            transition: all 0.2s;
            padding-top: 1.25rem;
            position: relative;
        }

        .input-style:focus-within {
            border-bottom-color: var(--color-primary);
        }

        .input-style input,
        .input-style select,
        .input-style textarea {
            border: none;
            padding: 0;
            line-height: 1.5;
            background-color: transparent;
        }

        .input-style input:focus,
        .input-style select:focus,
        .input-style textarea:focus {
            outline: none;
            box-shadow: none;
        }

        .input-style label {
            position: absolute;
            top: 0.25rem;
            left: 0;
            font-size: 0.75rem;
            color: #4b5563; /* Gray 600 */
            transition: all 0.2s;
            font-weight: 500;
        }

        #video-element,
        #face-overlay,
        #face-preview {
            transform: scaleX(-1);
        }

        #face-overlay {
            width: 100%;
            height: 100%;
            border-radius: 0.5rem;
            position: absolute;
            top: 0;
            left: 0;
        }
    </style>


</head>

<body>
    <header class="header-fixed fixed top-0 left-0 w-full bg-white h-14 flex items-center justify-center z-50">
        <span class="text-lg font-bold text-gray-800">Registrasi Akun & Wajah</span>
        <button id="fullscreen-btn" class="absolute right-4 text-gray-600 hover:text-emerald-600 transition p-2 rounded-full">
            <i class="fas fa-expand-alt"></i>
        </button>
    </header>

    <div class="page-content pt-14 pb-20">
        <div class="content p-4 lg:p-8">

            @if (session('success'))
                <div
                    class="mb-6 p-4 bg-green-50 border-l-4 border-green-600 text-green-700 rounded-lg text-left font-semibold shadow-md">
                    <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div
                    class="mb-6 p-4 bg-red-50 border-l-4 border-red-600 text-red-700 rounded-lg text-left font-semibold shadow-md">
                    <i class="fas fa-exclamation-triangle mr-2"></i> <span id="error-message-text">Terjadi Kesalahan: {{ session('error') }}</span>
                </div>
            @endif

            <form id="full-registration-form" class="space-y-6" action="{{ route('face.account.store') }}"
                method="POST">
                @csrf

                <div class="registration-container flex flex-wrap lg:flex-nowrap gap-6">

                    <div class="camera-column w-full lg:w-1/3 p-6 bg-gradient-to-br from-white to-emerald-50 shadow-2xl rounded-xl space-y-4 h-full gradient-card">
                        <h5 class="text-xl font-bold text-gray-800 border-b pb-2 mb-4">Langkah 1: Ambil Face ID
                            <span class="text-base text-emerald-600 ml-2"><i class="fas fa-lock"></i></span>
                        </h5>
                        <p class="text-sm text-gray-600">Posisikan wajah Anda dalam kotak kamera dan pastikan
                            pencahayaan cukup. Wajah akan diambil secara otomatis setelah stabil.</p>

                        <div id="camera-container"
                            class="relative w-full aspect-square bg-gray-900 rounded-lg shadow-xl overflow-hidden border-2 border-emerald-400">
                            <video id="video-element" autoplay muted playsinline
                                class="w-full h-full object-cover rounded-lg"></video>
                            <canvas id="face-overlay"></canvas>
                            <img id="face-preview"
                                class="w-full h-full object-cover rounded-lg hidden absolute top-0 left-0"
                                alt="Captured Face Preview" />
                            <div
                                class="absolute inset-0 border-4 border-dashed border-white/50 rounded-lg pointer-events-none">
                            </div>
                        </div>

                        <div id="status-display" class="text-center py-2">
                            <div id="loading-status" class="spinner-border text-emerald-500" role="status">
                                <i class="fas fa-sync-alt fa-spin text-xl text-emerald-600"></i>
                            </div>
                            <p id="message-status" class="mt-2 text-sm font-medium text-gray-700">Memuat model dan
                                kamera...</p>
                        </div>

                        <div class="flex flex-col space-y-2">
                            <button type="button" id="start-capture-btn"
                                class="w-full py-3 bg-emerald-600 text-white rounded-xl font-bold shadow-lg hover:bg-emerald-700 transition duration-200 disabled:opacity-50"
                                disabled>
                                <i class="fas fa-camera mr-2"></i> Mulai Ambil Wajah
                            </button>
                            <button type="button" id="recapture-btn"
                                class="w-full py-2 bg-yellow-500 text-white rounded-xl shadow-md hover:bg-yellow-600 transition disabled:opacity-50 hidden">
                                <i class="fas fa-redo mr-2"></i> Deteksi Ulang Wajah
                            </button>
                        </div>

                        <div id="face-result" class="mt-4 space-y-2" style="display: none;">
                            <div id="face-success"
                                class="bg-green-100 border border-green-500 text-green-700 p-3 rounded-lg text-center font-semibold"
                                style="display: none;">
                                <i class="fas fa-check-circle mr-2"></i> Face ID Berhasil Diambil!
                            </div>
                            <div id="face-error"
                                class="bg-red-100 border border-red-500 text-red-700 p-3 rounded-lg text-center font-semibold"
                                style="display: none;">
                                <i class="fas fa-exclamation-triangle mr-2"></i> <span id="error-message">Tidak ada
                                    wajah terdeteksi</span>
                            </div>
                        </div>

                        <input type="hidden" id="face-descriptor-input" name="face_descriptor" value="">
                        <input type="hidden" id="face-image-data-input" name="image" value="">
                    </div>

                    <div class="form-column w-full lg:w-2/3 p-6 bg-gradient-to-br from-white to-emerald-50 shadow-2xl rounded-xl space-y-6 gradient-card">

                        <h5 class="text-xl font-bold text-gray-800 border-b pb-2">Data Akun Wajib</h5>

                        <div class="grid md:grid-cols-2 gap-4">
                            <div class="input-style border-b border-gray-300">
                                <input type="text" name="name" class="w-full text-gray-800 focus:ring-0"
                                    placeholder="Nama Lengkap">
                                <label class="text-xs text-gray-500">Nama Lengkap</label>
                            </div>
                            <div class="input-style border-b border-gray-300">
                                <input type="email" name="email" class="w-full text-gray-800 focus:ring-0"
                                    placeholder="Email">
                                <label class="text-xs text-gray-500">Email</label>
                            </div>
                            <div class="input-style border-b border-gray-300">
                                <input type="password" name="password" class="w-full text-gray-800 focus:ring-0"
                                    placeholder="Password">
                                <label class="text-xs text-gray-500">Password</label>
                            </div>
                            <div class="input-style border-b border-gray-300">
                                <input type="tel" name="phone" class="w-full text-gray-800 focus:ring-0"
                                    placeholder="Nomor Telepon">
                                <label class="text-xs text-gray-500">Nomor Telepon</label>
                            </div>
                            <div class="input-style border-b border-gray-300">
                                <input type="text" name="nik" class="w-full text-gray-800 focus:ring-0"
                                    placeholder="NIK KTP">
                                <label class="text-xs text-gray-500">NIK KTP</label>
                            </div>
                            <div class="input-style border-b border-gray-300">
                                <input type="text" name="employee_nik" class="w-full text-gray-800 focus:ring-0"
                                    placeholder="NIK Karyawan">
                                <label class="text-xs text-gray-500">NIK Karyawan</label>
                            </div>
                        </div>

                        <h5 class="text-xl font-bold text-gray-800 mt-6 pt-4 border-t border-gray-200">Data
                            Organisasi & Karyawan</h5>
                            
                        <div class="input-style border-b border-gray-300">
                            <select name="site_id" class="w-full text-gray-800 focus:ring-0 bg-white">
                                <option value="" disabled selected>Pilih Lokasi Kerja (Site)</option>
                                @foreach ($sites as $site)
                                    <option value="{{ $site->id }}">{{ $site->name }}</option>
                                @endforeach
                            </select>
                            <label class="text-xs text-gray-500">Site ID</label>
                        </div>

                        <h5 class="text-xl font-bold text-gray-800 mt-6 pt-4 border-t border-gray-200">Data Profile
                            & Pribadi</h5>

                        <div class="grid md:grid-cols-2 gap-4">
                            <div class="input-style border-b border-gray-300">
                                <select name="gender" class="w-full text-gray-800 focus:ring-0 bg-white">
                                    <option value="" disabled selected>Pilih Jenis Kelamin</option>
                                    <option value="laki-laki">Laki-laki</option>
                                    <option value="perempuan">Perempuan</option>
                                </select>
                                <label class="text-xs text-gray-500">Jenis Kelamin</label>
                            </div>
                            <div class="input-style border-b border-gray-300">
                                <input type="text" name="birth_place" class="w-full text-gray-800 focus:ring-0"
                                    placeholder="Tempat Lahir">
                                <label class="text-xs text-gray-500">Tempat Lahir</label>
                            </div>
                            <div class="input-style border-b border-gray-300">
                                <input type="date" name="birth_date" class="w-full text-gray-800 focus:ring-0">
                                <label class="text-xs text-gray-500">Tanggal Lahir</label>
                            </div>
                            <div class="input-style border-b border-gray-300">
                                <select name="marriage_status" class="w-full text-gray-800 focus:ring-0 bg-white">
                                    <option value="" disabled selected>Pilih Status Pernikahan</option>
                                    <option value="TK-0">TK-0 : Tidak Kawin</option>
                                    <option value="K-0">K-0 : Kawin</option>
                                    <option value="K-1">K-1 : Kawin Anak 1</option>
                                </select>
                                <label class="text-xs text-gray-500">Status Pernikahan</label>
                            </div>
                            <div class="input-style border-b border-gray-300">
                                <input type="text" name="mother_name" class="w-full text-gray-800 focus:ring-0"
                                    placeholder="Nama Ibu Kandung">
                                <label class="text-xs text-gray-500">Nama Ibu Kandung</label>
                            </div>
                            <div class="input-style border-b border-gray-300">
                                <input type="number" name="number_of_children"
                                    class="w-full text-gray-800 focus:ring-0" placeholder="Jumlah Anak">
                                <label class="text-xs text-gray-500">Jumlah Anak</label>
                            </div>
                            <div class="input-style border-b border-gray-300">
                                <input type="text" name="npwp_number" class="w-full text-gray-800 focus:ring-0"
                                    placeholder="No NPWP">
                                <label class="text-xs text-gray-500">No NPWP</label>
                            </div>
                        </div>

                        <div class="input-style border-b border-gray-300">
                            <textarea name="address" class="w-full text-gray-800 focus:ring-0" placeholder="Alamat Lengkap"></textarea>
                            <label class="text-xs text-gray-500">Alamat</label>
                        </div>

                        <h5 class="text-xl font-bold text-gray-800 mt-6 pt-4 border-t border-gray-200">Informasi
                            Bank</h5>

                        <div class="grid md:grid-cols-2 gap-4">
                            <div class="input-style border-b border-gray-300">
                                <input type="text" name="bank_name" class="w-full text-gray-800 focus:ring-0"
                                    placeholder="Nama Bank">
                                <label class="text-xs text-gray-500">Nama Bank</label>
                            </div>
                            <div class="input-style border-b border-gray-300">
                                <input type="text" name="account_name" class="w-full text-gray-800 focus:ring-0"
                                    placeholder="Nama Pemilik Rekening">
                                <label class="text-xs text-gray-500">Nama Pemilik Rekening</label>
                            </div>
                            <div class="input-style border-b border-gray-300 md:col-span-2">
                                <input type="text" name="account_number" class="w-full text-gray-800 focus:ring-0"
                                    placeholder="Nomor Rekening">
                                <label class="text-xs text-gray-500">Nomor Rekening</label>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="fixed bottom-0 left-0 w-full p-4 bg-white shadow-2xl z-40 border-t border-gray-200">
                    <button type="submit" id="final-submit-btn" disabled
                        class="w-full py-3 bg-gray-400 text-white font-bold rounded-xl shadow-lg transition duration-200 disabled:opacity-50">
                        Lengkapi Data dan Ambil Wajah
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <script>
        const videoElement = document.getElementById('video-element');
        const faceOverlay = document.getElementById('face-overlay');
        const messageStatus = document.getElementById('message-status');
        const loadingStatus = document.getElementById('loading-status');
        const startCaptureBtn = document.getElementById('start-capture-btn');
        const recaptureBtn = document.getElementById('recapture-btn');
        const finalSubmitBtn = document.getElementById('final-submit-btn');
        const faceDescriptorInput = document.getElementById('face-descriptor-input');
        const faceImageDataInput = document.getElementById('face-image-data-input');
        const faceResult = document.getElementById('face-result');
        const faceSuccess = document.getElementById('face-success');
        const faceError = document.getElementById('face-error');
        const errorMessage = document.getElementById('error-message');
        const facePreview = document.getElementById('face-preview');
        const fullscreenBtn = document.getElementById('fullscreen-btn');

        let isModelLoaded = false;
        let stream = null;
        let captureInterval = null;
        let isCapturing = false;
        let isFaceDataReady = false;
        const requiredDetections = 10;
        const minFaceSize = 100;
        const outputSize = 400;

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

        function checkFormValidity() {
            const isBasicDataFilled = document.querySelector('input[name="name"]').value.trim() !== '' &&
                document.querySelector('input[name="email"]').value.trim() !== '' &&
                document.querySelector('input[name="password"]').value.trim() !== '';

            finalSubmitBtn.disabled = !(isFaceDataReady && isBasicDataFilled);

            if (!finalSubmitBtn.disabled) {
                finalSubmitBtn.textContent = 'Daftar dan Simpan Profile';
                finalSubmitBtn.classList.remove('bg-gray-400');
                finalSubmitBtn.classList.add('bg-emerald-600', 'hover:bg-emerald-700');
            } else {
                finalSubmitBtn.textContent = 'Lengkapi Data dan Ambil Wajah';
                finalSubmitBtn.classList.add('bg-gray-400');
                finalSubmitBtn.classList.remove('bg-emerald-600', 'hover:bg-emerald-700');
            }
        }

        function stopCamera() {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                stream = null;
            }
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

                        container.style.height = `${container.offsetWidth / videoRatio}px`;

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

        async function startFaceCapture() {
            if (!isModelLoaded || isCapturing) return;

            isFaceDataReady = false;
            faceDescriptorInput.value = '';
            faceImageDataInput.value = '';

            faceResult.style.display = 'none';
            faceSuccess.style.display = 'none';
            faceError.style.display = 'none';
            recaptureBtn.classList.add('hidden');

            if (!stream) {
                await startCamera();
            }
            videoElement.style.display = 'block';
            faceOverlay.style.display = 'block';
            facePreview.classList.add('hidden');

            isCapturing = true;
            startCaptureBtn.disabled = true;
            startCaptureBtn.textContent = 'Memproses... Harap Stabil';
            startCaptureBtn.classList.remove('bg-emerald-600', 'hover:bg-emerald-700');
            startCaptureBtn.classList.add('bg-blue-500');
            loadingStatus.style.display = 'inline-block';

            const ctx = faceOverlay.getContext('2d');
            let consecutiveDetections = 0;

            messageStatus.textContent = 'Mencari wajah (0 / 10)...';

            captureInterval = setInterval(async () => {
                if (!isCapturing) {
                    clearInterval(captureInterval);
                    return;
                }

                ctx.clearRect(0, 0, faceOverlay.width, faceOverlay.height);

                const detections = await faceapi.detectAllFaces(videoElement)
                    .withFaceLandmarks()
                    .withFaceDescriptors();

                const displaySize = {
                    width: videoElement.offsetWidth,
                    height: videoElement.offsetHeight
                };
                const resizedDetections = faceapi.resizeResults(detections, displaySize);

                if (resizedDetections.length === 0) {
                    messageStatus.textContent = 'Posisikan wajah Anda di kamera.';
                    consecutiveDetections = 0;
                    return;
                }

                if (detections.length === 1) {
                    const faceDetection = detections[0].detection;
                    const faceSize = faceDetection.box.width;

                    if (faceSize < minFaceSize) {
                        messageStatus.textContent = 'Wajah terlalu kecil. Mendekatlah!';
                        consecutiveDetections = 0;

                        const resizedBox = resizedDetections[0].detection.box;

                        ctx.save();
                        ctx.scale(-1, 1);
                        ctx.translate(-faceOverlay.width, 0);

                        new faceapi.draw.DrawBox(resizedBox, {
                            boxColor: 'red',
                            label: 'Wajah kecil'
                        }).draw(faceOverlay);

                        ctx.restore();

                        return;
                    }

                    consecutiveDetections++;
                    messageStatus.textContent =
                        `Wajah terdeteksi dan stabil (${consecutiveDetections} / ${requiredDetections})`;

                    const resizedBox = resizedDetections[0].detection.box;

                    ctx.save();
                    ctx.scale(-1, 1);
                    ctx.translate(-faceOverlay.width, 0);

                    new faceapi.draw.DrawBox(resizedBox, {
                        boxColor: 'lime',
                        label: 'Siap Ambil'
                    }).draw(faceOverlay);

                    ctx.restore();

                    if (consecutiveDetections >= requiredDetections) {
                        clearInterval(captureInterval);
                        isCapturing = false;

                        const finalDescriptor = detections[0].descriptor;
                        const squareCropCanvas = cropAndDrawFace(detections);
                        const finalImageBase64 = squareCropCanvas.toDataURL('image/jpeg', 0.9);

                        stopCamera();

                        faceDescriptorInput.value = JSON.stringify(Array.from(finalDescriptor));
                        faceImageDataInput.value = finalImageBase64;
                        isFaceDataReady = true;

                        videoElement.style.display = 'none';
                        faceOverlay.style.display = 'none';
                        facePreview.src = finalImageBase64;
                        facePreview.classList.remove('hidden');

                        loadingStatus.style.display = 'none';
                        messageStatus.textContent = 'Data Wajah Berhasil Diambil!';
                        faceResult.style.display = 'block';
                        faceSuccess.style.display = 'block';

                        recaptureBtn.classList.remove('hidden');
                        startCaptureBtn.style.display = 'none';

                        checkFormValidity();

                    }
                } else {
                    messageStatus.textContent = detections.length > 1 ? 'Terdeteksi banyak wajah!' :
                        'Posisikan wajah Anda di kamera.';
                    consecutiveDetections = 0;
                }
            }, 200);
        }

        function restartCapture() {
            if (isCapturing) return;

            faceResult.style.display = 'none';
            faceSuccess.style.display = 'none';
            faceError.style.display = 'none';
            recaptureBtn.classList.add('hidden');
            startCaptureBtn.style.display = 'block';
            startCaptureBtn.disabled = true;
            startCaptureBtn.textContent = 'Mulai Ambil Wajah';
            startCaptureBtn.classList.remove('bg-blue-500');
            startCaptureBtn.classList.add('bg-emerald-600', 'hover:bg-emerald-700');
            loadingStatus.style.display = 'inline-block';
            messageStatus.textContent = 'Memulai ulang kamera...';

            isFaceDataReady = false;
            faceDescriptorInput.value = '';
            faceImageDataInput.value = '';
            checkFormValidity();

            initialize(true);
        }

        async function initialize(restart = false) {
            loadingStatus.style.display = 'inline-block';

            if (!isModelLoaded) {
                messageStatus.textContent = 'Memuat model AI...';
                const modelsLoaded = await loadModels();

                if (!modelsLoaded) {
                    loadingStatus.style.display = 'none';
                    messageStatus.textContent = 'Gagal memuat model Face ID.';
                    errorMessage.textContent = 'Pastikan file model ada di folder /models.';
                    faceResult.style.display = 'block';
                    faceError.style.display = 'block';
                    return;
                }
            }

            messageStatus.textContent = 'Model siap. Memulai kamera...';
            const cameraStarted = await startCamera();

            loadingStatus.style.display = 'none';

            if (cameraStarted) {
                startCaptureBtn.disabled = false;
                startCaptureBtn.textContent = 'Mulai Ambil Wajah';
                messageStatus.textContent = 'Kamera aktif. Tekan tombol untuk registrasi.';

                if (restart) {
                    startFaceCapture();
                }
            } else {
                messageStatus.textContent = 'Gagal mengakses kamera.';
                errorMessage.textContent = 'Pastikan Anda memberikan izin kamera.';
                faceResult.style.display = 'block';
                faceError.style.display = 'block';
            }

            checkFormValidity();
            document.getElementById('full-registration-form').addEventListener('input', checkFormValidity);
        }

        document.addEventListener('DOMContentLoaded', () => initialize(false));
        startCaptureBtn.addEventListener('click', startFaceCapture);
        recaptureBtn.addEventListener('click', restartCapture);
        fullscreenBtn.addEventListener('click', toggleFullscreen);

        document.addEventListener('fullscreenchange', updateFullscreenIcon);

        window.addEventListener('beforeunload', stopCamera);
    </script>


</body>

</html>