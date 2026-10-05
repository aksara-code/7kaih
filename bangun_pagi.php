<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bangun Pagi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #edf1ee;
        }
    </style>
</head>
<body class="min-h-screen bg-[#edf1ee] text-slate-800 antialiased">
    <div class="relative overflow-hidden rounded-b-[32px] bg-[#0c6d4d] pb-16 pt-6 shadow-[0_18px_30px_rgba(12,109,77,0.22)]">
        <div class="absolute -right-12 -top-8 h-36 w-36 rounded-full bg-[#0d7f5a]/30 blur-2xl"></div>
        <div class="absolute -left-10 bottom-2 h-32 w-32 rounded-full bg-[#0a5e41]/30 blur-2xl"></div>

        <div class="relative z-10 mx-auto max-w-md px-4">
            <div class="mb-5 flex items-center justify-between gap-3">
                <a href="dashboard.php" class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-2 text-[10px] font-bold text-white backdrop-blur-sm transition hover:bg-white/15">
                    <span aria-hidden="true">←</span>
                    <span>Kembali ke Beranda</span>
                </a>

                <button id="openAddModalBtn" type="button" class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-2 text-[10px] font-extrabold text-[#0c6d4d] shadow-md shadow-emerald-900/10 transition hover:bg-emerald-50">
                    <span class="text-lg leading-none">＋</span>
                    <span>Baru</span>
                </button>
            </div>

            <div class="flex items-center justify-center gap-4">
                <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-[18px] bg-white shadow-lg shadow-emerald-900/10">
                    <img
                        src="https://cerdasberkarakter.kemendikdasmen.go.id/wp-content/uploads/2024/12/1-bangun-pagi.png"
                        alt="logo bangun pagi"
                        class="h-full w-full object-cover"
                    />
                </div>

                <div class="min-w-0 text-left">
                    <h1 class="text-3xl font-extrabold leading-none tracking-[-0.05em] text-white">Bangun Pagi</h1>
                    <p class="mt-2 text-sm font-medium leading-snug text-emerald-50">Catat kebiasaan bangun tidurmu hari ini</p>
                </div>
            </div>
        </div>
    </div>

    <div class="relative z-20 mx-auto -mt-12 w-full max-w-[460px] px-4">
        <div id="successNotification" class="mb-3 hidden rounded-r-xl border-l-4 border-emerald-600 bg-emerald-100 p-3.5 text-xs font-bold text-emerald-900 shadow-sm" role="status" aria-live="polite">
            Catatan kegiatan bangun pagi berhasil disimpan!
        </div>

        <div class="rounded-[30px] border border-slate-200 bg-white p-5 shadow-[0_20px_40px_rgba(15,23,42,0.10)]">
            <div class="mb-4">
                <p class="text-[0.68rem] font-extrabold uppercase tracking-[0.18em] text-slate-500">Riwayat</p>
                <h2 class="mt-1 text-xl font-extrabold text-slate-800">Kegiatan Bangun Pagi</h2>
            </div>

            <div id="historyList" class="space-y-3"></div>
            <div id="pager" class="mt-4 flex items-center justify-between gap-3"></div>
        </div>
    </div>

    <div id="addModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 p-3">
        <div class="max-h-[calc(100vh-1.5rem)] w-full max-w-md overflow-y-auto rounded-[26px] bg-white p-4 shadow-[0_30px_80px_rgba(15,23,42,0.25)]">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h2 class="text-xl font-extrabold text-slate-800">Tambah Aktivitas</h2>
                <button type="button" id="closeAddModalBtn" class="text-2xl font-light text-slate-500">×</button>
            </div>

            <form id="activityForm" class="space-y-4">
                <div class="overflow-hidden rounded-[18px] border border-slate-200 bg-slate-950">
                    <video id="cameraVideo" autoplay playsinline muted class="h-64 w-full object-cover bg-slate-900"></video>
                    <div class="bg-slate-50 px-3 py-2">
                        <p id="cameraStatus" class="text-xs font-semibold text-slate-600">Menyiapkan kamera...</p>
                    </div>
                </div>

                <div id="imagePreviewWrapper" class="hidden overflow-hidden rounded-[12px] border border-slate-200 bg-slate-50">
                    <img id="imagePreview" class="h-40 w-full object-cover" alt="Preview kegiatan" />
                </div>

                <div class="flex justify-center">
                    <button type="button" id="captureFaceBtn" disabled class="w-full rounded-[12px] bg-[#0d6b4e] px-3 py-3 text-sm font-bold text-white opacity-60 transition-all duration-200">
                        Tangkap Gambar
                    </button>
                </div>

                <button type="button" id="retakeFaceBtn" class="hidden w-full rounded-[12px] border border-[#0d6b4e] bg-white px-3 py-3 text-sm font-bold text-[#0d6b4e]">
                    Ambil Ulang
                </button>

                <input type="hidden" id="capturedImageData" value="">

                <button type="submit" class="w-full rounded-[14px] bg-[#0d6b4e] px-4 py-3 text-base font-bold text-white shadow-lg shadow-emerald-800/20">
                    Saya sudah bangun pagi
                </button>
            </form>
        </div>
    </div>

    <footer class="py-10 text-center text-sm text-slate-500">
        © 2026 Tujuh Kebiasaan Anak Indonesia Hebat
    </footer>

    <script src="activity-db.js"></script>
    <script>
        const CATEGORY = 'bangun';
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

        function formatClock(value) {
            const date = new Date(`2000-01-01T${value}:00`);
            return new Intl.DateTimeFormat('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false }).format(date);
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
                cameraStatus.textContent = 'Wajah terdeteksi! Silakan ambil foto.';
                cameraStatus.className = 'text-xs font-bold text-emerald-600';
                captureFaceBtn.disabled = false;
                captureFaceBtn.classList.remove('opacity-60');
                return;
            }

            faceDetected = false;
            cameraStatus.textContent = 'Mengarahkan wajah ke kamera...';
            cameraStatus.className = 'text-xs font-semibold text-amber-600';
            captureFaceBtn.disabled = true;
            captureFaceBtn.classList.add('opacity-60');
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
                cameraStatus.className = 'text-xs font-bold text-red-600';
                return;
            }

            if (window.location.protocol === 'file:') {
                cameraStatus.textContent = 'Buka halaman ini melalui http://localhost agar kamera bisa aktif.';
                cameraStatus.className = 'text-xs font-bold text-red-600';
                return;
            }

            try {
                if (cameraStream) {
                    cameraStream.getTracks().forEach(track => track.stop());
                }

                cameraStatus.textContent = 'Membuka kamera...';
                cameraStatus.className = 'text-xs font-semibold text-slate-600';

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
                cameraStatus.textContent = 'Memuat model wajah...';
                cameraStatus.className = 'text-xs font-semibold text-slate-600';

                await loadFaceModels();
                startFaceDetectionLoop();
                cameraStatus.textContent = 'Mengarahkan wajah ke kamera...';
                cameraStatus.className = 'text-xs font-semibold text-amber-600';
            } catch (error) {
                let message = 'Kamera tidak bisa dibuka. Izinkan akses kamera lalu coba lagi.';

                if (error && error.name === 'NotAllowedError') {
                    message = 'Izin kamera ditolak. Buka pengaturan browser dan izinkan kamera untuk localhost, lalu refresh halaman.';
                } else if (error && error.name === 'NotFoundError') {
                    message = 'Kamera tidak terdeteksi pada perangkat ini.';
                } else if (error && error.name === 'NotReadableError') {
                    message = 'Kamera sedang dipakai aplikasi lain. Tutup aplikasi lain lalu coba lagi.';
                } else if (error && (error.message || '').includes('Face API')) {
                    message = 'Model deteksi wajah gagal dimuat. Coba refresh halaman atau cek koneksi internet.';
                }

                cameraStatus.textContent = message;
                cameraStatus.className = 'text-xs font-bold text-red-600';
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
            captureFaceBtn.classList.add('opacity-60');
            captureFaceBtn.classList.remove('hidden');
            retakeFaceBtn.classList.add('hidden');
            capturedImageData.value = '';
            imagePreview.src = '';
            imagePreviewWrapper.classList.add('hidden');
        }

        function captureFacePhoto() {
            if (!faceDetected || !cameraVideo || cameraVideo.readyState < 2) {
                cameraStatus.textContent = 'Wajah belum terdeteksi, arahkan wajah ke kamera.';
                cameraStatus.className = 'text-xs font-bold text-red-600';
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
            cameraStatus.textContent = 'Foto berhasil diterima dan siap disimpan.';
            cameraStatus.className = 'text-xs font-bold text-emerald-600';
            captureFaceBtn.disabled = true;
            captureFaceBtn.classList.add('opacity-60');
            captureFaceBtn.classList.add('hidden');
            retakeFaceBtn.classList.remove('hidden');
        }

        function resetCapturedPhoto() {
            capturedImageData.value = '';
            imagePreview.src = '';
            imagePreviewWrapper.classList.add('hidden');
            retakeFaceBtn.classList.add('hidden');
            captureFaceBtn.disabled = false;
            captureFaceBtn.classList.remove('opacity-60');
            captureFaceBtn.classList.remove('hidden');
            cameraStatus.textContent = 'Mengarahkan wajah ke kamera...';
            cameraStatus.className = 'text-xs font-semibold text-amber-600';
        }

        function renderPager(totalItems) {
            if (totalItems < ITEMS_PER_PAGE) {
                pager.innerHTML = '';
                return;
            }

            const totalPages = Math.ceil(totalItems / ITEMS_PER_PAGE);
            if (currentPage > totalPages) currentPage = totalPages;

            pager.innerHTML = `
                <button type="button" id="prevPageBtn" class="inline-flex items-center justify-center text-xs font-bold text-slate-600 transition ${currentPage === 1 ? 'cursor-not-allowed opacity-40' : 'hover:text-slate-800'}" ${currentPage === 1 ? 'disabled' : ''}><span aria-hidden="true">‹</span> Prev</button>
                <div class="inline-flex min-w-[80px] items-center justify-center rounded-full bg-[#dfeee6] px-3 py-1.5 text-[11px] font-extrabold tracking-[0.14em] text-slate-700">${currentPage} / ${totalPages}</div>
                <button type="button" id="nextPageBtn" class="inline-flex items-center justify-center text-xs font-bold text-slate-600 transition ${currentPage >= totalPages ? 'cursor-not-allowed opacity-40' : 'hover:text-slate-800'}" ${currentPage >= totalPages ? 'disabled' : ''}>Next <span aria-hidden="true">›</span></button>
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
                    <div class="px-3 py-6 text-center">
                        <div class="mx-auto mb-2 flex h-14 w-14 items-center justify-center rounded-full bg-emerald-50">
                            <i class="fa-solid fa-sun text-2xl text-emerald-600" aria-label="Ikon bangun pagi"></i>
                        </div>
                        <h3 class="mb-1 text-xs font-extrabold text-slate-800 sm:text-sm">Kamu belum mengisi data</h3>
                        <p class="mx-auto max-w-xs text-[11px] font-medium leading-relaxed text-slate-500 sm:text-xs">
                            Belum ada catatan bangun pagi. Klik tombol <strong>"Baru"</strong> di bagian atas untuk menambahkan kegiatan.
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
                <div class="rounded-[22px] border border-[#ebf3ee] bg-[#f9fbfa] p-3 shadow-[0_8px_18px_rgba(15,23,42,0.04)]">
                    <div class="flex items-start gap-3">
                        <div class="min-w-0 flex-1 space-y-2">
                            <div class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-[0.08em] text-slate-500">
                                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-[#edf9f0] text-[#0c6d4d]">📅</span>
                                <span>${new Date(item.date + 'T00:00:00').toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })}</span>
                                <span class="text-slate-400">•</span>
                                <span class="font-extrabold text-slate-700">${formatClock(item.time)}</span>
                            </div>

                            <p class="text-sm font-extrabold text-slate-800">${escapeHtml(item.option || 'Bangun pagi')}</p>
                            <p class="text-[11px] leading-relaxed text-slate-600">
                                ${item.note ? escapeHtml(item.note) : 'Catatan bangun pagi hari ini.'}
                            </p>
                        </div>

                        <div class="w-28 shrink-0 overflow-hidden rounded-[12px] border border-emerald-100 bg-white">
                            ${item.image ? `<img src="${item.image}" class="h-24 w-full object-cover" alt="Foto kegiatan bangun pagi" />` : '<div class="flex h-24 w-full items-center justify-center bg-[#edf9f0] text-2xl text-slate-400">☀️</div>'}
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
                cameraStatus.textContent = 'Foto belum diambil. Pastikan wajah sudah terdeteksi.';
                cameraStatus.className = 'text-xs font-bold text-red-600';
                return;
            }

            const timestamp = setAutoDateTime();
            try {
                await ActivityDB.save(CATEGORY, {
                    date: timestamp.date,
                    option: 'Bangun pagi',
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
                cameraStatus.className = 'text-xs font-bold text-red-600';
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