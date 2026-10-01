<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tidur Cepat - Kebiasaan Anak Indonesia Hebat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f1f5f3;
        }
        .face-guide-box {
            border: 2px dashed rgba(255, 255, 255, 0.4);
            border-radius: 50%;
        }
    </style>
</head>
<body class="min-h-screen bg-[#f1f5f3] text-slate-800 antialiased selection:bg-emerald-500 selection:text-white">

    <!-- Header Section -->
    <div class="relative overflow-hidden rounded-b-[36px] bg-gradient-to-br from-[#0c6d4d] via-[#09573d] to-[#06422e] pb-16 pt-7 shadow-[0_20px_40px_rgba(12,109,77,0.25)]">
        <!-- Ambient Blur Decor -->
        <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-emerald-400/20 blur-3xl pointer-events-none"></div>
        <div class="absolute -left-10 bottom-0 h-36 w-36 rounded-full bg-emerald-300/15 blur-2xl pointer-events-none"></div>

        <div class="relative z-10 mx-auto max-w-md px-5">
            <!-- Navigation & Action Bar -->
            <div class="mb-6 flex items-center justify-between gap-3">
                <a href="dashboard.php" class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-xs font-semibold text-white backdrop-blur-md transition hover:bg-white/20 active:scale-95">
                    <span aria-hidden="true">←</span>
                    <span>Kembali ke Beranda</span>
                </a>

                <button id="openAddModalBtn" type="button" class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-xs font-extrabold text-[#0c6d4d] shadow-lg shadow-emerald-950/20 transition hover:bg-emerald-50 active:scale-95">
                    <span class="text-base leading-none">＋</span>
                    <span>Baru</span>
                </button>
            </div>

            <!-- Title & Branding -->
            <div class="flex items-center gap-4">
                <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-white p-1 shadow-md shadow-emerald-950/20 ring-4 ring-white/10">
                    <img
                        src="https://cerdasberkarakter.kemendikdasmen.go.id/wp-content/uploads/2024/12/7-tidur-cepat.png"
                        alt="Logo Tidur Cepat"
                        class="h-full w-full object-cover rounded-xl"
                    />
                </div>

                <div class="min-w-0">
                    <h1 class="text-2xl font-extrabold tracking-tight text-white">Tidur Cepat</h1>
                    <p class="mt-1 text-xs font-medium text-emerald-100/90 leading-relaxed">Catat & bangun kebiasaan tidur malam yang disiplin</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Container -->
    <main class="relative z-20 mx-auto -mt-10 w-full max-w-[480px] px-4 pb-12">
        <div id="successNotification" class="mb-3 hidden rounded-r-xl border-l-4 border-emerald-600 bg-emerald-100 p-3.5 text-xs font-bold text-emerald-900 shadow-sm" role="status" aria-live="polite">
            Catatan kegiatan tidur cepat berhasil disimpan!
        </div>

        <div class="rounded-[30px] border border-slate-200 bg-white p-5 shadow-[0_20px_40px_rgba(15,23,42,0.10)]">
            <div class="mb-4">
                <p class="text-[0.68rem] font-extrabold uppercase tracking-[0.18em] text-slate-500">Riwayat</p>
                <h2 class="mt-1 text-xl font-extrabold text-slate-800">Kegiatan Tidur</h2>
            </div>

            <!-- Dynamic List -->
            <div id="historyList" class="space-y-3"></div>

            <!-- Pagination -->
            <div id="pager" class="mt-4 flex items-center justify-between gap-3"></div>
        </div>
    </main>

    <!-- Modal Form Tambah Kegiatan -->
    <div id="addModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 p-3 backdrop-blur-sm">
        <div class="max-h-[calc(100vh-1.5rem)] w-full max-w-md overflow-y-auto rounded-[28px] bg-white p-5 shadow-2xl transition-all">
            <!-- Modal Header -->
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-extrabold text-slate-800">Tambah Catatan Tidur</h2>
                    <p class="text-xs text-slate-500">Ambil foto verifikasi untuk mencatat kegiatan</p>
                </div>
                <button type="button" id="closeAddModalBtn" class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-800 transition">
                    ✕
                </button>
            </div>

            <!-- Form -->
            <form id="activityForm" class="space-y-4">
                <!-- Camera Viewport -->
                <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-slate-950 shadow-inner">
                    <video id="cameraVideo" autoplay playsinline muted class="h-60 w-full object-cover"></video>
                    
                    <!-- Visual Guide Frame -->
                    <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
                        <div class="face-guide-box h-40 w-40"></div>
                    </div>

                    <!-- Status Bar -->
                    <div class="absolute bottom-0 inset-x-0 bg-slate-950/80 backdrop-blur-md px-3 py-2 text-center border-t border-white/10">
                        <p id="cameraStatus" class="text-xs font-semibold text-slate-300">Menyiapkan kamera...</p>
                    </div>
                </div>

                <!-- Preview Area -->
                <div id="imagePreviewWrapper" class="hidden overflow-hidden rounded-2xl border border-emerald-200 bg-emerald-50/50 p-2">
                    <p class="mb-2 text-[11px] font-bold text-emerald-800 text-center">Foto Terverifikasi:</p>
                    <img id="imagePreview" class="h-40 w-full rounded-xl object-cover shadow-sm" alt="Preview tidur" />
                </div>

                <!-- Action Buttons -->
                <div class="space-y-2.5 pt-1">
                    <button type="button" id="captureFaceBtn" disabled class="w-full rounded-xl bg-[#0d6b4e] py-3 text-sm font-bold text-white shadow-md transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-[#0a5840]">
                        Tangkap Gambar
                    </button>

                    <button type="button" id="retakeFaceBtn" class="hidden w-full rounded-xl border border-slate-300 bg-slate-50 py-3 text-sm font-bold text-slate-700 hover:bg-slate-100 transition">
                        Ambil Foto Ulang
                    </button>

                    <input type="hidden" id="capturedImageData" value="">

                    <button type="submit" class="w-full rounded-xl bg-gradient-to-r from-[#0c6d4d] to-[#09573d] py-3 text-sm font-extrabold text-white shadow-lg shadow-emerald-900/20 hover:from-[#0a5d42] hover:to-[#074731] active:scale-[0.99] transition">
                        Saya Sudah Tidur Cepat
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="py-8 text-center text-xs font-medium text-slate-400">
        © 2026 Tujuh Kebiasaan Anak Indonesia Hebat
    </footer>

    <!-- Logic Script -->
    <script src="activity-db.js"></script>
    <script>
        const CATEGORY = 'tidur';
        const ITEMS_PER_PAGE = 5;
        const MODEL_URL = 'https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/weights';
        const FALLBACK_MODEL_URL = 'https://justadudewhohacks.github.io/face-api.js/models';

        const addModal = document.getElementById('addModal');
        const successNotification = document.getElementById('successNotification');
        const openAddModalBtn = document.getElementById('openAddModalBtn');
        const closeAddModalBtn = document.getElementById('closeAddModalBtn');
        const cancelAddModalBtn = document.getElementById('cancelAddModalBtn');
        const historyList = document.getElementById('historyList');
        const pager = document.getElementById('pager');
        const activityForm = document.getElementById('activityForm');
        const cameraVideo = document.getElementById('cameraVideo');
        const cameraStatus = document.getElementById('cameraStatus');
        const captureFaceBtn = document.getElementById('captureFaceBtn');
        const imagePreview = document.getElementById('imagePreview');
        const imagePreviewWrapper = document.getElementById('imagePreviewWrapper');
        const capturedImageData = document.getElementById('capturedImageData');
        const retakeFaceBtn = document.getElementById('retakeFaceBtn');

        let currentPage = 1;
        let activityItems = [];
        let successNotificationTimer = null;
        let cameraStream = null;
        let faceDetectionTimer = null;
        let faceDetected = false;

        function setAutoDateTime() {
            const now = new Date();
            return {
                date: ActivityDB.today(),
                time: now.toTimeString().slice(0, 5)
            };
        }

        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, char => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[char]));
        }

        function openModal() {
            addModal.classList.remove('hidden');
            addModal.classList.add('flex');
            startCamera();
        }

        function closeModal() {
            stopCamera();
            addModal.classList.add('hidden');
            addModal.classList.remove('flex');
        }

        async function loadFaceModels() {
            if (!window.faceapi) {
                throw new Error('Face API belum dimuat.');
            }

            try {
                await Promise.all([
                    faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                    faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                    faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
                ]);
                return;
            } catch (primaryError) {
                console.warn('Primary face model URL failed, retrying with fallback URL.', primaryError);
            }

            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri(FALLBACK_MODEL_URL),
                faceapi.nets.faceLandmark68Net.loadFromUri(FALLBACK_MODEL_URL),
                faceapi.nets.faceRecognitionNet.loadFromUri(FALLBACK_MODEL_URL)
            ]);
        }

        async function detectFace() {
            if (!cameraVideo || cameraVideo.readyState < 2) {
                return;
            }

            const detection = await faceapi
                .detectSingleFace(cameraVideo, new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.5 }))
                .withFaceLandmarks();

            if (detection) {
                faceDetected = true;
                cameraStatus.textContent = '✓ Wajah terdeteksi! Silakan ambil foto.';
                cameraStatus.className = 'text-xs font-bold text-emerald-400';
                captureFaceBtn.disabled = false;
                captureFaceBtn.classList.remove('opacity-50');
                return;
            }

            faceDetected = false;
            cameraStatus.textContent = 'Posisikan wajah di dalam area kamera...';
            cameraStatus.className = 'text-xs font-semibold text-amber-300';
            captureFaceBtn.disabled = true;
            captureFaceBtn.classList.add('opacity-50');
        }

        function startFaceDetectionLoop() {
            if (faceDetectionTimer) {
                clearInterval(faceDetectionTimer);
            }

            faceDetectionTimer = setInterval(() => {
                detectFace();
            }, 500);
        }

        async function startCamera() {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                cameraStatus.textContent = 'Browser ini tidak mendukung kamera.';
                cameraStatus.className = 'text-xs font-bold text-rose-400';
                return;
            }

            if (window.location.protocol === 'file:') {
                cameraStatus.textContent = 'Buka halaman via HTTP/HTTPS agar kamera dapat diakses.';
                cameraStatus.className = 'text-xs font-bold text-rose-400';
                return;
            }

            try {
                if (cameraStream) {
                    cameraStream.getTracks().forEach(track => track.stop());
                }

                cameraStatus.textContent = 'Membuka kamera...';
                cameraStatus.className = 'text-xs font-semibold text-slate-300';

                const constraints = {
                    video: {
                        facingMode: 'user',
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    },
                    audio: false
                };

                try {
                    cameraStream = await navigator.mediaDevices.getUserMedia(constraints);
                } catch (firstError) {
                    cameraStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
                }

                cameraVideo.srcObject = cameraStream;
                await cameraVideo.play();
                cameraStatus.textContent = 'Memuat model analisis wajah...';
                cameraStatus.className = 'text-xs font-semibold text-slate-300';

                await loadFaceModels();
                startFaceDetectionLoop();
                cameraStatus.textContent = 'Posisikan wajah di dalam area kamera...';
                cameraStatus.className = 'text-xs font-semibold text-amber-300';
            } catch (error) {
                let message = 'Kamera tidak dapat diakses. Mohon izinkan akses kamera.';

                if (error && error.name === 'NotAllowedError') {
                    message = 'Izin kamera ditolak. Silakan berikan izin di pengaturan browser.';
                } else if (error && error.name === 'NotFoundError') {
                    message = 'Kamera tidak ditemukan pada perangkat ini.';
                } else if (error && error.name === 'NotReadableError') {
                    message = 'Kamera sedang digunakan oleh aplikasi lain.';
                } else if (error && (error.message || '').includes('Face API')) {
                    message = 'Gagal memuat sistem deteksi wajah. Periksa koneksi internet.';
                }

                cameraStatus.textContent = message;
                cameraStatus.className = 'text-xs font-bold text-rose-400';
                console.error(error);
            }
        }

        function stopCamera() {
            if (faceDetectionTimer) {
                clearInterval(faceDetectionTimer);
                faceDetectionTimer = null;
            }

            if (cameraStream) {
                cameraStream.getTracks().forEach(track => track.stop());
                cameraStream = null;
            }

            if (cameraVideo) {
                cameraVideo.srcObject = null;
            }

            faceDetected = false;
            captureFaceBtn.disabled = true;
            captureFaceBtn.classList.add('opacity-50');
            captureFaceBtn.classList.remove('hidden');
            retakeFaceBtn.classList.add('hidden');
            capturedImageData.value = '';
            imagePreview.src = '';
            imagePreviewWrapper.classList.add('hidden');
        }

        function captureFacePhoto() {
            if (!faceDetected || !cameraVideo || cameraVideo.readyState < 2) {
                cameraStatus.textContent = 'Wajah belum terdeteksi secara jelas.';
                cameraStatus.className = 'text-xs font-bold text-rose-400';
                return;
            }

            const canvas = document.createElement('canvas');
            canvas.width = cameraVideo.videoWidth;
            canvas.height = cameraVideo.videoHeight;
            const context = canvas.getContext('2d');
            context.drawImage(cameraVideo, 0, 0, canvas.width, canvas.height);

            const imageData = canvas.toDataURL('image/jpeg', 0.9);
            capturedImageData.value = imageData;
            imagePreview.src = imageData;
            imagePreviewWrapper.classList.remove('hidden');
            cameraStatus.textContent = 'Foto terverifikasi dan siap disimpan!';
            cameraStatus.className = 'text-xs font-bold text-emerald-400';
            captureFaceBtn.disabled = true;
            captureFaceBtn.classList.add('opacity-50', 'hidden');
            retakeFaceBtn.classList.remove('hidden');
        }

        function resetCapturedPhoto() {
            capturedImageData.value = '';
            imagePreview.src = '';
            imagePreviewWrapper.classList.add('hidden');
            retakeFaceBtn.classList.add('hidden');
            captureFaceBtn.disabled = false;
            captureFaceBtn.classList.remove('opacity-50', 'hidden');
            cameraStatus.textContent = 'Posisikan wajah di dalam area kamera...';
            cameraStatus.className = 'text-xs font-semibold text-amber-300';
        }

        function renderPager(totalItems) {
            if (totalItems < ITEMS_PER_PAGE) {
                pager.innerHTML = '';
                return;
            }

            const totalPages = Math.max(1, Math.ceil(totalItems / ITEMS_PER_PAGE));
            if (currentPage > totalPages) currentPage = totalPages;

            pager.innerHTML = `
                <button type="button" id="prevPageBtn" class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-bold text-slate-600 hover:bg-slate-100 transition ${currentPage === 1 ? 'pointer-events-none opacity-40' : ''}" ${currentPage === 1 ? 'disabled' : ''}>
                    ‹ Prev
                </button>
                <div class="rounded-full bg-slate-100 px-3.5 py-1 text-[11px] font-extrabold text-slate-600">
                    Halaman ${currentPage} / ${totalPages}
                </div>
                <button type="button" id="nextPageBtn" class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-bold text-slate-600 hover:bg-slate-100 transition ${currentPage >= totalPages ? 'pointer-events-none opacity-40' : ''}" ${currentPage >= totalPages ? 'disabled' : ''}>
                    Next ›
                </button>
            `;

            document.getElementById('prevPageBtn')?.addEventListener('click', () => {
                if (currentPage > 1) {
                    currentPage -= 1;
                    renderHistory();
                }
            });

            document.getElementById('nextPageBtn')?.addEventListener('click', () => {
                if (currentPage < totalPages) {
                    currentPage += 1;
                    renderHistory();
                }
            });
        }

        function renderHistory() {
            const items = activityItems;

            if (!items.length) {
                historyList.innerHTML = `
                    <div class="rounded-[22px] border border-emerald-100 bg-emerald-50/70 p-4 text-center shadow-sm">
                        <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-emerald-600">
                            <i class="fa-solid fa-moon text-2xl text-white" aria-label="Ikon tidur cepat"></i>
                        </div>
                        <h3 class="text-base font-extrabold text-slate-800">Kamu Belum Mengisi Data</h3>
                        <p class="mt-1 text-xs leading-relaxed text-slate-500">
                            Belum ada data tidur. Klik tombol <span class="font-bold text-emerald-700">“Baru”</span> di atas untuk menambahkan catatan tidur cepat harimu.
                        </p>
                    </div>
                `;
                renderPager(0);
                return;
            }

            const totalPages = Math.max(1, Math.ceil(items.length / ITEMS_PER_PAGE));
            if (currentPage > totalPages) currentPage = totalPages;

            const start = (currentPage - 1) * ITEMS_PER_PAGE;
            const pageItems = items.slice(start, start + ITEMS_PER_PAGE);

            historyList.innerHTML = pageItems.map(item => `
                <div class="group relative overflow-hidden rounded-2xl border border-slate-100 bg-slate-50/60 p-3.5 transition hover:border-emerald-200 hover:bg-white hover:shadow-md">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 mb-1.5">
                                <span class="inline-flex items-center gap-1 rounded-md bg-emerald-100/80 px-2 py-0.5 text-[10px] font-bold text-emerald-800">
                                    📅 ${new Date(item.date + 'T00:00:00').toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })}
                                </span>
                                <span class="inline-flex items-center rounded-md bg-slate-200/70 px-2 py-0.5 text-[10px] font-bold text-slate-700">
                                    ⏰ ${item.time}
                                </span>
                            </div>

                            <h4 class="text-sm font-extrabold text-slate-800">${escapeHtml(item.option || 'Tidur cepat')}</h4>
                            <p class="mt-0.5 text-xs text-slate-500 line-clamp-2">
                                ${item.note ? escapeHtml(item.note) : 'Catatan verifikasi tidur tepat waktu.'}
                            </p>
                        </div>

                        <div class="h-20 w-20 shrink-0 overflow-hidden rounded-xl border border-slate-200 bg-slate-100 shadow-inner">
                            ${item.image ? `<img src="${item.image}" class="h-full w-full object-cover transition group-hover:scale-105" alt="Foto verifikasi" />` : '<div class="flex h-full w-full items-center justify-center text-xl text-slate-400">🌙</div>'}
                        </div>
                    </div>
                </div>
            `).join('');

            renderPager(items.length);
        }

        activityForm.addEventListener('submit', async function (event) {
            event.preventDefault();

            const photo = capturedImageData.value;
            if (!photo) {
                cameraStatus.textContent = 'Foto belum diambil. Pastikan wajah terdeteksi.';
                cameraStatus.className = 'text-xs font-bold text-rose-400';
                return;
            }

            const timestamp = setAutoDateTime();
            try {
                await ActivityDB.save(CATEGORY, {
                    date: timestamp.date,
                    option: 'Tidur cepat',
                    note: '',
                    image: photo
                });
                currentPage = 1;
                await loadHistory();
                activityForm.reset();
                capturedImageData.value = '';
                imagePreviewWrapper.classList.add('hidden');
                imagePreview.src = '';
                closeModal();
                successNotification.classList.remove('hidden');
                clearTimeout(successNotificationTimer);
                successNotificationTimer = setTimeout(() => {
                    successNotification.classList.add('hidden');
                }, 5000);
            } catch (error) {
                cameraStatus.textContent = error.message;
                cameraStatus.className = 'text-xs font-bold text-rose-400';
            }
        });

        captureFaceBtn.addEventListener('click', captureFacePhoto);
        retakeFaceBtn.addEventListener('click', resetCapturedPhoto);
        openAddModalBtn.addEventListener('click', openModal);
        closeAddModalBtn.addEventListener('click', closeModal);
        if (cancelAddModalBtn) cancelAddModalBtn.addEventListener('click', closeModal);
        addModal.addEventListener('click', function (event) {
            if (event.target === addModal) {
                closeModal();
            }
        });

        async function loadHistory() {
            try {
                activityItems = await ActivityDB.list(CATEGORY);
                renderHistory();
            } catch (error) {
                historyList.textContent = error.message;
                pager.innerHTML = '';
            }
        }

        loadHistory();
    </script>
</body>
</html>