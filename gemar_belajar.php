<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gemar Belajar</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
                        src="https://cerdasberkarakter.kemendikdasmen.go.id/wp-content/uploads/2024/12/5-gemar-belajar.png"
                        alt="logo gemar belajar"
                        class="h-full w-full object-cover"
                    />
                </div>

                <div class="min-w-0 text-left">
                    <h1 class="text-3xl font-extrabold leading-none tracking-[-0.05em] text-white">Gemar Belajar</h1>
                    <p class="mt-2 text-sm font-medium leading-snug text-emerald-50">Catat materi dan ilmu yang kamu dapatkan</p>
                </div>
            </div>
        </div>
    </div>

    <div class="relative z-20 mx-auto -mt-12 w-full max-w-[460px] px-4">
        <div id="successNotification" class="mb-3 hidden rounded-r-xl border-l-4 border-emerald-600 bg-emerald-100 p-3.5 text-xs font-bold text-emerald-900 shadow-sm" role="status" aria-live="polite">
            Catatan kegiatan belajar berhasil disimpan!
        </div>

        <div class="rounded-[30px] border border-slate-200 bg-white p-5 shadow-[0_20px_40px_rgba(15,23,42,0.10)]">
            <div class="mb-4">
                <p class="text-[0.68rem] font-extrabold uppercase tracking-[0.18em] text-slate-500">Riwayat</p>
                <h2 class="mt-1 text-xl font-extrabold text-slate-800">Kegiatan Belajar</h2>
            </div>

            <div id="historyList" class="space-y-3"></div>
            <div id="pager" class="mt-4 flex items-center justify-between gap-3"></div>
        </div>
    </div>

    <div id="addModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 p-3">
        <div class="max-h-[calc(100vh-1.5rem)] w-full max-w-md overflow-y-auto rounded-[26px] bg-white p-4 shadow-[0_30px_80px_rgba(15,23,42,0.25)]">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h2 class="text-xl font-extrabold text-slate-800">Tambah Kegiatan</h2>
                <button type="button" id="closeAddModalBtn" class="text-2xl font-light text-slate-500">×</button>
            </div>

            <form id="activityForm" class="space-y-4">
                <div>
                    <label class="mb-2 block text-[0.72rem] font-extrabold uppercase tracking-[0.14em] text-slate-700">Tanggal</label>
                    <input id="entryDate" type="date" class="w-full rounded-[12px] border border-slate-200 bg-slate-50 px-3 py-3 text-base font-medium text-slate-700 focus:border-[#0d6b4e] focus:bg-white focus:outline-none focus:ring-4 focus:ring-emerald-100" />
                </div>

                <div>
                    <label class="mb-2 block text-[0.72rem] font-extrabold uppercase tracking-[0.14em] text-slate-700">Buku yang Dipelajari</label>
                    <input id="manualActivity" type="text" placeholder="Contoh: Buku Matematika, Bahasa Indonesia" class="w-full rounded-[12px] border border-slate-200 bg-slate-50 px-3 py-3 text-base font-medium text-slate-700 focus:border-[#0d6b4e] focus:bg-white focus:outline-none focus:ring-4 focus:ring-emerald-100" />
                </div>

                <div>
                    <label class="mb-2 block text-[0.72rem] font-extrabold uppercase tracking-[0.14em] text-slate-700">Informasi yang Didapat</label>
                    <textarea id="activityNote" rows="3" placeholder="Tuliskan informasi atau materi yang didapat hari ini..." class="w-full rounded-[12px] border border-slate-200 bg-slate-50 px-3 py-3 text-base font-medium text-slate-700 focus:border-[#0d6b4e] focus:bg-white focus:outline-none focus:ring-4 focus:ring-emerald-100"></textarea>
                </div>

                <div>
                    <label class="mb-2 block text-[0.72rem] font-extrabold uppercase tracking-[0.14em] text-slate-700">Foto Kegiatan (Opsional)</label>
                    <input id="imageInput" type="file" accept="image/*" capture="environment" class="w-full rounded-[12px] border border-slate-200 bg-slate-50 px-3 py-3 text-sm text-slate-600 file:mr-3 file:rounded file:border-0 file:bg-[#0d6b4e] file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white" />
                    <div id="imagePreviewWrapper" class="mt-3 hidden overflow-hidden rounded-[12px] border border-slate-200 bg-slate-50">
                        <img id="imagePreview" class="h-40 w-full object-cover" alt="Preview belajar" />
                    </div>
                </div>

                <div class="mt-5 flex items-center justify-between gap-3">
                    <button type="button" id="cancelAddModalBtn" class="flex-1 rounded-[12px] border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700">Batal</button>
                    <button type="submit" class="flex-1 rounded-[12px] bg-[#0d6b4e] px-4 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-800/20">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <footer class="py-10 text-center text-sm text-slate-500">
        © 2026 Tujuh Kebiasaan Anak Indonesia Hebat
    </footer>

    <script src="activity-db.js"></script>
    <script>
        const CATEGORY = 'belajar';
        const ITEMS_PER_PAGE = 5;
        const addModal = document.getElementById('addModal');
        const successNotification = document.getElementById('successNotification');
        const openAddModalBtn = document.getElementById('openAddModalBtn');
        const closeAddModalBtn = document.getElementById('closeAddModalBtn');
        const cancelAddModalBtn = document.getElementById('cancelAddModalBtn');
        const historyList = document.getElementById('historyList');
        const pager = document.getElementById('pager');
        const activityForm = document.getElementById('activityForm');
        const entryDate = document.getElementById('entryDate');
        const manualActivity = document.getElementById('manualActivity');
        const imageInput = document.getElementById('imageInput');
        const imagePreview = document.getElementById('imagePreview');
        const imagePreviewWrapper = document.getElementById('imagePreviewWrapper');
        let currentPage = 1;
        let activityItems = [];
        let successNotificationTimer = null;

        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, function (char) {
                const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
                return map[char];
            });
        }

        function setAutoDate() {
            entryDate.value = ActivityDB.today();
        }

        function openModal() {
            setAutoDate();
            addModal.classList.remove('hidden');
            addModal.classList.add('flex');
        }

        function closeModal() {
            addModal.classList.add('hidden');
            addModal.classList.remove('flex');
        }

        function formatDate(value) {
            const date = new Date(value + 'T00:00:00');
            return new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }).format(date);
        }

        function handleImageSelect() {
            const file = imageInput.files && imageInput.files[0];
            if (!file) {
                imagePreviewWrapper.classList.add('hidden');
                imagePreview.src = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function (event) {
                imagePreview.src = event.target.result;
                imagePreviewWrapper.classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        }

        imageInput.addEventListener('change', handleImageSelect);

        function renderPager(totalItems) {
            if (totalItems < ITEMS_PER_PAGE) {
                pager.innerHTML = '';
                return;
            }

            const totalPages = Math.max(1, Math.ceil(totalItems / ITEMS_PER_PAGE));
            if (currentPage > totalPages) currentPage = totalPages;

            pager.innerHTML = `
                <button type="button" id="prevPageBtn" class="inline-flex items-center justify-center text-xs font-bold text-slate-600 transition ${currentPage === 1 ? 'cursor-not-allowed opacity-40' : 'hover:text-slate-800'}" ${currentPage === 1 ? 'disabled' : ''}><span aria-hidden="true">‹</span> Prev</button>
                <div class="inline-flex min-w-[110px] items-center justify-center rounded-full bg-[#dfeee6] px-4 py-2 text-[11px] font-extrabold tracking-[0.14em] text-slate-700">Hal ${currentPage} / ${totalPages}</div>
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
                    <div class="rounded-[22px] border border-emerald-100 bg-emerald-50/70 p-4 text-center shadow-sm">
                        <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-emerald-50">
                            <i class="fa-solid fa-book-open text-2xl text-emerald-600" aria-label="Ikon belajar"></i>
                        </div>
                        <p class="text-base font-extrabold text-slate-800">Kamu belum mengisi data</p>
                        <p class="mt-2 text-xs leading-relaxed text-slate-600">
                            Belum ada data belajar. Klik tombol <span class="font-bold text-emerald-700">“Baru”</span> di atas untuk menambahkan kegiatan.
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
                                <span class="font-extrabold text-slate-700">${item.time}</span>
                            </div>

                            <p class="text-sm font-extrabold text-slate-800">${escapeHtml(item.option || 'Belajar')}</p>
                            <p class="text-[11px] leading-relaxed text-slate-600">
                                ${item.note ? escapeHtml(item.note) : 'Catatan belajar hari ini.'}
                            </p>
                        </div>

                        <div class="w-28 shrink-0 overflow-hidden rounded-[12px] border border-emerald-100 bg-white">
                            ${item.image ? `<img src="${item.image}" class="h-24 w-full object-cover" alt="Foto kegiatan belajar" />` : '<div class="flex h-24 w-full items-center justify-center bg-[#edf9f0] text-2xl text-slate-400">📚</div>'}
                        </div>
                    </div>
                </div>
            `).join('');

            renderPager(items.length);
        }

        activityForm.addEventListener('submit', async function (event) {
            event.preventDefault();

            const selected = manualActivity.value.trim() || 'Belajar';
            const file = imageInput.files && imageInput.files[0];
            try {
                await ActivityDB.save(CATEGORY, {
                    date: entryDate.value,
                    option: selected,
                    note: document.getElementById('activityNote').value.trim(),
                    image: file || null
                });
                currentPage = 1;
                await loadHistory();
                activityForm.reset();
                imagePreviewWrapper.classList.add('hidden');
                imagePreview.src = '';
                closeModal();
                successNotification.classList.remove('hidden');
                clearTimeout(successNotificationTimer);
                successNotificationTimer = setTimeout(() => {
                    successNotification.classList.add('hidden');
                }, 5000);
            } catch (error) {
                alert(error.message);
            }
        });

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
