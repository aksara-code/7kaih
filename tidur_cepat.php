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
<body class="min-h-screen text-slate-800 antialiased">
    <div class="relative overflow-hidden bg-[#0b6a4d] pb-24 pt-10 rounded-b-[120px] shadow-[0_20px_40px_rgba(11,93,63,0.18)]">
        <div class="absolute -right-12 -top-8 h-36 w-36 rounded-full bg-[#0a5d42]/40 blur-2xl"></div>
        <div class="absolute -left-10 bottom-8 h-32 w-32 rounded-full bg-[#0d7f5a]/30 blur-2xl"></div>

        <div class="relative z-10 mx-auto max-w-4xl px-4 text-center">
            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-[22px] bg-white shadow-lg shadow-emerald-900/10">
                <span class="text-[2.1rem]" aria-label="logo tidur cepat">🌙</span>
            </div>
            <h1 class="mt-7 text-[2.5rem] font-extrabold tracking-[-0.05em] text-white">Tidur Cepat</h1>
            <p class="mt-1 text-lg font-medium text-white/85">Aplikasi Tujuh Kebiasaan Anak Indonesia Hebat</p>
        </div>
    </div>

    <div class="relative z-20 mx-auto -mt-16 w-full max-w-[460px] px-4">
        <div class="rounded-[30px] bg-white p-5 shadow-[0_20px_40px_rgba(15,23,42,0.10)] border border-slate-100">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <p class="text-[0.68rem] font-extrabold uppercase tracking-[0.18em] text-slate-500">Riwayat</p>
                    <h2 class="text-xl font-extrabold text-slate-800">Kegiatan Tidur</h2>
                </div>
                <button id="openAddModalBtn" type="button" class="flex items-center gap-2 rounded-full bg-[#0d6b4e] px-3 py-2 text-sm font-bold text-white shadow-md shadow-emerald-900/10 hover:bg-[#0b5a42]">
                    <span class="text-lg leading-none">＋</span>
                    <span>Tambah</span>
                </button>
            </div>

            <div id="historyList" class="space-y-3"></div>
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
                    <label class="mb-2 block text-[0.72rem] font-extrabold uppercase tracking-[0.14em] text-slate-700">Tanggal</label>
                    <input id="entryDate" type="date" readonly class="w-full rounded-[12px] border border-slate-200 bg-slate-50 px-3 py-3 text-base font-medium text-slate-700 focus:border-[#0d6b4e] focus:bg-white focus:outline-none focus:ring-4 focus:ring-emerald-100" />
                </div>

                <div>
                    <label class="mb-2 block text-[0.72rem] font-extrabold uppercase tracking-[0.14em] text-slate-700">Jam</label>
                    <input id="entryTime" type="time" readonly class="w-full rounded-[12px] border border-slate-200 bg-slate-50 px-3 py-3 text-base font-medium text-slate-700 focus:border-[#0d6b4e] focus:bg-white focus:outline-none focus:ring-4 focus:ring-emerald-100" />
                </div>

                <div>
                    <label class="mb-2 block text-[0.72rem] font-extrabold uppercase tracking-[0.14em] text-slate-700">Nama Kegiatan</label>
                    <input id="manualActivity" type="text" placeholder="Contoh: doa sebelum tidur, matikan gawai, tidur tepat waktu" class="w-full rounded-[12px] border border-slate-200 bg-slate-50 px-3 py-3 text-base font-medium text-slate-700 focus:border-[#0d6b4e] focus:bg-white focus:outline-none focus:ring-4 focus:ring-emerald-100" />
                </div>

                <div>
                    <label class="mb-2 block text-[0.72rem] font-extrabold uppercase tracking-[0.14em] text-slate-700">Catatan Tidur</label>
                    <textarea id="activityNote" rows="3" placeholder="Tuliskan aktivitas sebelum tidur..." class="w-full rounded-[12px] border border-slate-200 bg-slate-50 px-3 py-3 text-base font-medium text-slate-700 focus:border-[#0d6b4e] focus:bg-white focus:outline-none focus:ring-4 focus:ring-emerald-100"></textarea>
                </div>

                <div>
                    <label class="mb-2 block text-[0.72rem] font-extrabold uppercase tracking-[0.14em] text-slate-700">Foto Kegiatan</label>
                    <input id="imageInput" type="file" accept="image/*" class="w-full rounded-[12px] border border-slate-200 bg-slate-50 px-3 py-3 text-sm text-slate-600 file:mr-3 file:rounded file:border-0 file:bg-[#0d6b4e] file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white" />
                    <div id="imagePreviewWrapper" class="mt-3 hidden overflow-hidden rounded-[12px] border border-slate-200 bg-slate-50">
                        <img id="imagePreview" class="h-40 w-full object-cover" alt="Preview tidur" />
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

    <script>
        const STORAGE_KEY = 'tidur_cepat_history';
        const addModal = document.getElementById('addModal');
        const openAddModalBtn = document.getElementById('openAddModalBtn');
        const closeAddModalBtn = document.getElementById('closeAddModalBtn');
        const cancelAddModalBtn = document.getElementById('cancelAddModalBtn');
        const historyList = document.getElementById('historyList');
        const activityForm = document.getElementById('activityForm');
        const entryDate = document.getElementById('entryDate');
        const entryTime = document.getElementById('entryTime');
        const manualActivity = document.getElementById('manualActivity');
        const imageInput = document.getElementById('imageInput');
        const imagePreview = document.getElementById('imagePreview');
        const imagePreviewWrapper = document.getElementById('imagePreviewWrapper');

        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, function (char) {
                const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
                return map[char];
            });
        }

        function setAutoDateTime() {
            const now = new Date();
            entryDate.value = now.toISOString().split('T')[0];
            entryTime.value = now.toTimeString().slice(0, 5);
        }

        function openModal() {
            setAutoDateTime();
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

        function renderHistory() {
            const items = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');

            if (!items.length) {
                historyList.innerHTML = `
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4 text-center text-sm font-medium text-slate-500">
                        Belum ada riwayat tidur.
                    </div>
                `;
                return;
            }

            historyList.innerHTML = items.map(item => `
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-bold text-slate-800">${formatDate(item.date)}</p>
                            <p class="text-xs text-slate-500">${item.time} • ${item.option || 'Tidur cepat'}</p>
                            ${item.note ? `<p class="mt-1 text-xs text-slate-600">${escapeHtml(item.note)}</p>` : ''}
                        </div>
                        <span class="rounded-full bg-emerald-100 px-2 py-1 text-[10px] font-bold uppercase tracking-[0.12em] text-emerald-700">Tercatat</span>
                    </div>
                    ${item.image ? `<img src="${item.image}" class="mt-3 h-32 w-full rounded-xl object-cover" alt="Foto kegiatan tidur" />` : ''}
                </div>
            `).join('');
        }

        activityForm.addEventListener('submit', function (event) {
            event.preventDefault();

            const selected = manualActivity.value.trim() || 'Tidur cepat';
            const file = imageInput.files && imageInput.files[0];
            const reader = new FileReader();

            reader.onload = function (event) {
                const entry = {
                    date: entryDate.value,
                    time: entryTime.value,
                    option: selected,
                    summary: selected,
                    note: document.getElementById('activityNote').value.trim(),
                    image: file ? event.target.result : ''
                };

                const items = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
                items.unshift(entry);
                localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
                renderHistory();
                activityForm.reset();
                manualActivity.value = '';
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
        cancelAddModalBtn.addEventListener('click', closeModal);
        addModal.addEventListener('click', function (event) {
            if (event.target === addModal) {
                closeModal();
            }
        });

        renderHistory();
    </script>
</body>
</html>
