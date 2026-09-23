<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tidur Cepat</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
                    <span>Kembali ke Dashboard</span>
                </a>

                <button id="openAddModalBtn" type="button" class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-2 text-[10px] font-extrabold text-[#0c6d4d] shadow-md shadow-emerald-900/10 transition hover:bg-emerald-50">
                    <span class="text-lg leading-none">＋</span>
                    <span>Baru</span>
                </button>
            </div>

            <div class="flex items-center gap-4">
                <div class="flex h-16 w-16 items-center justify-center rounded-[18px] bg-white text-[2rem] shadow-lg shadow-emerald-900/10">
                    <span aria-label="logo tidur cepat">🌙</span>
                </div>

                <div class="flex-1">
                    <h1 class="text-3xl font-extrabold tracking-[-0.05em] text-white">Tidur Cepat</h1>
                    <p class="mt-1 text-sm font-medium text-emerald-50">Catat rutinitas tidur malammu</p>
                </div>
            </div>
        </div>
    </div>

    <div class="relative z-20 mx-auto -mt-12 w-full max-w-[460px] px-4">
        <div class="rounded-[30px] border border-slate-200 bg-white p-5 shadow-[0_20px_40px_rgba(15,23,42,0.10)]">
            <div class="mb-4">
                <p class="text-[0.68rem] font-extrabold uppercase tracking-[0.18em] text-slate-500">Riwayat</p>
                <h2 class="mt-1 text-xl font-extrabold text-slate-800">Kegiatan Tidur</h2>
            </div>

            <div id="historyList" class="space-y-3"></div>
            <div id="pager" class="mt-4 flex items-center justify-between gap-3"></div>
        </div>
    </div>

    <div id="addModal" class="fixed inset-0 z-50 hidden items-end justify-center bg-slate-900/40 p-3 sm:items-center">
        <div class="w-full max-w-md rounded-[26px] bg-white p-4 shadow-[0_30px_80px_rgba(15,23,42,0.25)]">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h2 class="text-xl font-extrabold text-slate-800">Tambah Kegiatan</h2>
                <button type="button" id="closeAddModalBtn" class="text-2xl font-light text-slate-500">×</button>
            </div>

            <form id="activityForm" class="space-y-4">
                <div>
                    <label class="mb-2 block text-[0.72rem] font-extrabold uppercase tracking-[0.14em] text-slate-700">Foto Kegiatan (Opsional)</label>
                    <input id="imageInput" type="file" accept="image/*" capture="environment" class="w-full rounded-[12px] border border-slate-200 bg-slate-50 px-3 py-3 text-sm text-slate-600 file:mr-3 file:rounded file:border-0 file:bg-[#0d6b4e] file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white" />
                    <div id="imagePreviewWrapper" class="mt-3 hidden overflow-hidden rounded-[12px] border border-slate-200 bg-slate-50">
                        <img id="imagePreview" class="h-40 w-full object-cover" alt="Preview tidur" />
                    </div>
                </div>

                <button type="submit" class="w-full rounded-[14px] bg-[#0d6b4e] px-4 py-3 text-base font-bold text-white shadow-lg shadow-emerald-800/20">
                    Saya sudah tidur cepat
                </button>
            </form>
        </div>
    </div>

    <footer class="py-10 text-center text-sm text-slate-500">
        © 2026 Tujuh Kebiasaan Anak Indonesia Hebat
    </footer>

    <script>
        const STORAGE_KEY = 'tidur_cepat_history';
        const ITEMS_PER_PAGE = 5;
        const addModal = document.getElementById('addModal');
        const openAddModalBtn = document.getElementById('openAddModalBtn');
        const closeAddModalBtn = document.getElementById('closeAddModalBtn');
        const cancelAddModalBtn = document.getElementById('cancelAddModalBtn');
        const historyList = document.getElementById('historyList');
        const pager = document.getElementById('pager');
        const activityForm = document.getElementById('activityForm');
        const imageInput = document.getElementById('imageInput');
        const imagePreview = document.getElementById('imagePreview');
        const imagePreviewWrapper = document.getElementById('imagePreviewWrapper');
        let currentPage = 1;

        function setAutoDateTime() {
            const now = new Date();
            return {
                date: now.toISOString().split('T')[0],
                time: now.toTimeString().slice(0, 5)
            };
        }

        function openModal() {
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
            const items = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');

            if (!items.length) {
                historyList.innerHTML = `
                    <div class="rounded-[22px] border border-emerald-100 bg-emerald-50/70 p-4 text-center shadow-sm">
                        <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-xl text-emerald-700">
                            <span aria-hidden="true">✓</span>
                        </div>
                        <div class="mx-auto mb-3 flex h-20 w-20 items-center justify-center rounded-full bg-white text-[2rem] shadow-inner shadow-emerald-100">
                            <span aria-label="ikon tidur cepat">🌙</span>
                        </div>
                        <p class="text-lg font-extrabold text-slate-800">Belum ada catatan tidur cepat</p>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">
                            Belum ada data tidur cepat. Klik tombol <span class="font-bold text-emerald-700">“Baru”</span> di atas untuk menambahkan kegiatan.
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

                            <p class="text-sm font-extrabold text-slate-800">${item.option || 'Tidur cepat'}</p>
                            <p class="text-[11px] leading-relaxed text-slate-600">
                                ${item.note ? item.note : 'Catatan tidur cepat hari ini.'}
                            </p>
                        </div>

                        <div class="w-28 shrink-0 overflow-hidden rounded-[12px] border border-emerald-100 bg-white">
                            ${item.image ? `<img src="${item.image}" class="h-24 w-full object-cover" alt="Foto kegiatan tidur" />` : '<div class="flex h-24 w-full items-center justify-center bg-[#edf9f0] text-2xl text-slate-400">🌙</div>'}
                        </div>
                    </div>
                </div>
            `).join('');

            renderPager(items.length);
        }

        activityForm.addEventListener('submit', function (event) {
            event.preventDefault();

            const selected = 'Tidur cepat';
            const file = imageInput.files && imageInput.files[0];
            const timestamp = setAutoDateTime();
            const reader = new FileReader();

            reader.onload = function () {
                const entry = {
                    date: timestamp.date,
                    time: timestamp.time,
                    option: selected,
                    summary: selected,
                    note: '',
                    image: file ? reader.result : '',
                    timestamp: new Date().toISOString()
                };

                const items = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
                items.unshift(entry);
                localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
                renderHistory();
                activityForm.reset();
                imagePreviewWrapper.classList.add('hidden');
                imagePreview.src = '';
                closeModal();
            };

            if (file) {
                reader.readAsDataURL(file);
            } else {
                reader.onload({ target: { result: '' } });
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

        renderHistory();
    </script>
</body>
</html>
