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
<body class="flex min-h-screen flex-col bg-[#edf1ee] text-slate-800 antialiased selection:bg-emerald-200">

    <!-- Header Section -->
    <div class="relative overflow-hidden rounded-b-[32px] bg-[#0c6d4d] pb-16 pt-6 shadow-[0_18px_30px_rgba(12,109,77,0.22)]">
        <div class="absolute -right-12 -top-8 h-36 w-36 rounded-full bg-[#0d7f5a]/30 blur-2xl"></div>
        <div class="absolute -left-10 bottom-2 h-32 w-32 rounded-full bg-[#0a5e41]/30 blur-2xl"></div>

        <div class="relative z-10 mx-auto max-w-md px-4">
            <div class="mb-5 flex items-center justify-between gap-3">
                <a href="dashboard.php" class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3.5 py-2 text-xs font-bold text-white backdrop-blur-sm transition hover:bg-white/20 active:scale-95">
                    <span aria-hidden="true">←</span>
                    <span>Kembali ke Beranda</span>
                </a>

                <button id="openAddModalBtn" type="button" class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-xs font-extrabold text-[#0c6d4d] shadow-md shadow-emerald-900/10 transition hover:bg-emerald-50 active:scale-95">
                    <span class="text-base leading-none">＋</span>
                    <span>Baru</span>
                </button>
            </div>

            <div class="flex items-center justify-center gap-4">
                <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-[18px] bg-white shadow-lg shadow-emerald-900/10">
                    <img
                        src="https://cerdasberkarakter.kemendikdasmen.go.id/wp-content/uploads/2024/12/5-gemar-belajar.png"
                        alt="Logo Gemar Belajar"
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

    <!-- Main Content -->
    <div class="relative z-20 mx-auto -mt-12 w-full max-w-[460px] flex-1 px-4">
        <!-- Notifikasi Sukses -->
        <div id="successNotification" class="mb-3 hidden rounded-xl border-l-4 border-emerald-600 bg-emerald-100 p-3.5 text-xs font-bold text-emerald-900 shadow-sm transition-all" role="status" aria-live="polite">
            <i class="fa-solid fa-circle-check mr-1.5 text-emerald-700"></i>
            <span>Catatan kegiatan belajar berhasil disimpan!</span>
        </div>

        <div class="rounded-[30px] border border-slate-200 bg-white p-5 shadow-[0_20px_40px_rgba(15,23,42,0.10)]">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <p class="text-[0.68rem] font-extrabold uppercase tracking-[0.18em] text-slate-500">Riwayat</p>
                    <h2 class="mt-0.5 text-xl font-extrabold text-slate-800">Kegiatan Belajar</h2>
                </div>
            </div>

            <!-- List Riwayat -->
            <div id="historyList" class="space-y-3"></div>

            <!-- Pager / Navigasi Halaman -->
            <div id="pager" class="mt-4 flex items-center justify-between gap-3"></div>
        </div>
    </div>

    <!-- Modal Tambah Kegiatan -->
    <div id="addModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-3 backdrop-blur-sm transition-opacity">
        <div class="max-h-[calc(100vh-2rem)] w-full max-w-md overflow-y-auto rounded-[26px] bg-white p-5 shadow-[0_30px_80px_rgba(15,23,42,0.25)]">
            <div class="mb-4 flex items-center justify-between gap-3 border-b border-slate-100 pb-3">
                <h2 class="text-xl font-extrabold text-slate-800">Tambah Kegiatan Belajar</h2>
                <button type="button" id="closeAddModalBtn" class="flex h-8 w-8 items-center justify-center rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form id="activityForm" class="space-y-4">
                <div>
                    <label for="entryDate" class="mb-1.5 block text-[0.72rem] font-extrabold uppercase tracking-[0.14em] text-slate-700">Tanggal <span class="text-rose-500">*</span></label>
                    <input id="entryDate" type="date" required class="w-full rounded-[12px] border border-slate-200 bg-slate-50 px-3.5 py-3 text-sm font-medium text-slate-700 focus:border-[#0d6b4e] focus:bg-white focus:outline-none focus:ring-4 focus:ring-emerald-100" />
                </div>

                <div>
                    <label for="manualActivity" class="mb-1.5 block text-[0.72rem] font-extrabold uppercase tracking-[0.14em] text-slate-700">Buku / Materi yang Dipelajari <span class="text-rose-500">*</span></label>
                    <input id="manualActivity" type="text" required placeholder="Contoh: Buku Matematika, Bahasa Indonesia" class="w-full rounded-[12px] border border-slate-200 bg-slate-50 px-3.5 py-3 text-sm font-medium text-slate-700 focus:border-[#0d6b4e] focus:bg-white focus:outline-none focus:ring-4 focus:ring-emerald-100" />
                </div>

                <div>
                    <label for="activityNote" class="mb-1.5 block text-[0.72rem] font-extrabold uppercase tracking-[0.14em] text-slate-700">Informasi / Poin Penting yang Didapat</label>
                    <textarea id="activityNote" rows="3" placeholder="Tuliskan ringkasan materi atau hal menarik yang dipelajari hari ini..." class="w-full rounded-[12px] border border-slate-200 bg-slate-50 px-3.5 py-3 text-sm font-medium text-slate-700 focus:border-[#0d6b4e] focus:bg-white focus:outline-none focus:ring-4 focus:ring-emerald-100"></textarea>
                </div>

                <!-- Unggah Foto Kegiatan (Tanpa Preview Foto di Bawahnya) -->
                <div>
                    <label class="mb-1.5 block text-[0.72rem] font-extrabold uppercase tracking-[0.14em] text-slate-700">
                        FOTO KEGIATAN <span class="font-normal normal-case text-slate-400">(Opsional)</span>
                    </label>
                    <div class="flex items-center gap-3.5 rounded-[16px] border border-slate-200 bg-[#f8fafc] p-2">
                        <label for="imageInput" class="inline-flex cursor-pointer shrink-0 items-center rounded-full bg-[#084c38] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#063b2c] active:scale-95">
                            Choose File
                        </label>
                        <input id="imageInput" type="file" name="foto" accept="image/*" class="sr-only" />
                        <span id="fileNameDisplay" class="truncate text-xs font-semibold text-slate-600">
                            No file chosen
                        </span>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3 border-t border-slate-100 pt-3">
                    <button type="button" id="cancelAddModalBtn" class="flex-1 rounded-[12px] border border-slate-200 bg-white px-4 py-3 text-xs font-bold text-slate-700 transition hover:bg-slate-50 active:scale-95">Batal</button>
                    <button type="submit" class="flex-1 rounded-[12px] bg-[#0d6b4e] px-4 py-3 text-xs font-bold text-white shadow-lg shadow-emerald-800/20 transition hover:bg-[#0a5a41] active:scale-95">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="mt-auto py-10 text-center text-xs text-slate-500">
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
        const activityNote = document.getElementById('activityNote');
        const imageInput = document.getElementById('imageInput');
        const fileNameDisplay = document.getElementById('fileNameDisplay');

        let currentPage = 1;
        let activityItems = [];
        let successNotificationTimer = null;
        let selectedImageDataUrl = '';

        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, function (char) {
                const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
                return map[char];
            });
        }

        function formatNoteText(text) {
            if (!text) return 'Catatan belajar hari ini.';
            const safeText = escapeHtml(text);
            return safeText.replace(/\n/g, '<br>');
        }

        function setAutoDate() {
            entryDate.value = typeof ActivityDB !== 'undefined' && ActivityDB.today ? ActivityDB.today() : new Date().toISOString().split('T')[0];
        }

        function resetFormState() {
            activityForm.reset();
            selectedImageDataUrl = '';
            if (fileNameDisplay) {
                fileNameDisplay.textContent = 'No file chosen';
                fileNameDisplay.classList.add('text-slate-400');
                fileNameDisplay.classList.remove('text-slate-700');
            }
        }

        function openModal() {
            setAutoDate();
            addModal.classList.remove('hidden');
            addModal.classList.add('flex');
        }

        function closeModal() {
            addModal.classList.add('hidden');
            addModal.classList.remove('flex');
            resetFormState();
        }

        function formatDate(value) {
            if (!value) return '-';
            const date = new Date(value + 'T00:00:00');
            return new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }).format(date);
        }

        function handleImageSelect() {
            const file = imageInput.files && imageInput.files[0];
            if (!file) {
                fileNameDisplay.textContent = 'No file chosen';
                fileNameDisplay.classList.add('text-slate-400');
                fileNameDisplay.classList.remove('text-slate-700');
                selectedImageDataUrl = '';
                return;
            }

            // Tampilkan nama file yang dipilih
            fileNameDisplay.textContent = file.name;
            fileNameDisplay.classList.remove('text-slate-400');
            fileNameDisplay.classList.add('text-slate-700');

            // Tetap konversi gambar ke Base64 agar dapat disimpan ke database
            const reader = new FileReader();
            reader.onload = function (event) {
                selectedImageDataUrl = event.target.result;
            };
            reader.readAsDataURL(file);
        }

        imageInput.addEventListener('change', handleImageSelect);

        function renderPager(totalItems) {
            if (totalItems <= ITEMS_PER_PAGE) {
                pager.innerHTML = '';
                return;
            }

            const totalPages = Math.max(1, Math.ceil(totalItems / ITEMS_PER_PAGE));
            if (currentPage > totalPages) currentPage = totalPages;

            pager.innerHTML = `
                <button type="button" id="prevPageBtn" class="inline-flex items-center justify-center text-xs font-bold text-slate-600 transition ${currentPage === 1 ? 'cursor-not-allowed opacity-40' : 'hover:text-slate-900'}" ${currentPage === 1 ? 'disabled' : ''}>
                    <i class="fa-solid fa-chevron-left mr-1"></i> Prev
                </button>
                <div class="inline-flex min-w-[100px] items-center justify-center rounded-full bg-[#dfeee6] px-3.5 py-1.5 text-[10px] font-extrabold tracking-[0.12em] text-slate-700">
                    Hal ${currentPage} / ${totalPages}
                </div>
                <button type="button" id="nextPageBtn" class="inline-flex items-center justify-center text-xs font-bold text-slate-600 transition ${currentPage >= totalPages ? 'cursor-not-allowed opacity-40' : 'hover:text-slate-900'}" ${currentPage >= totalPages ? 'disabled' : ''}>
                    Next <i class="fa-solid fa-chevron-right ml-1"></i>
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

            if (!items || !items.length) {
                historyList.innerHTML = `
                    <div class="rounded-[22px] border border-emerald-100 bg-emerald-50/70 p-5 text-center shadow-sm">
                        <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-white shadow-sm">
                            <i class="fa-solid fa-book-open text-2xl text-emerald-600"></i>
                        </div>
                        <p class="text-sm font-extrabold text-slate-800">Belum ada data belajar</p>
                        <p class="mt-1.5 text-xs leading-relaxed text-slate-600">
                            Kamu belum mencatat kegiatan belajar. Klik tombol <span class="font-bold text-emerald-700">“Baru”</span> untuk menambah catatan pertama.
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
                <div class="group relative rounded-[22px] border border-[#ebf3ee] bg-[#f9fbfa] p-3.5 transition hover:border-emerald-200 hover:shadow-md">
                    <div class="flex items-start gap-3">
                        <div class="min-w-0 flex-1 space-y-1.5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-[0.08em] text-slate-500">
                                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-[#edf9f0] text-[#0c6d4d] text-[10px]">📅</span>
                                    <span>${formatDate(item.date)}</span>
                                    ${item.time ? `<span class="text-slate-300">•</span><span class="text-slate-600">${item.time}</span>` : ''}
                                </div>
                            </div>

                            <p class="text-sm font-extrabold text-slate-800 leading-snug">${escapeHtml(item.option || 'Belajar')}</p>
                            <p class="text-xs leading-relaxed text-slate-600">
                                ${formatNoteText(item.note)}
                            </p>
                        </div>

                        <div class="w-24 shrink-0 overflow-hidden rounded-[12px] border border-slate-200/80 bg-white">
                            ${item.image 
                                ? `<img src="${item.image}" class="h-24 w-full object-cover transition duration-300 group-hover:scale-105" alt="Foto kegiatan belajar" />` 
                                : '<div class="flex h-24 w-full items-center justify-center bg-[#edf9f0] text-2xl text-slate-400">📚</div>'
                            }
                        </div>
                    </div>
                </div>
            `).join('');

            renderPager(items.length);
        }
        
        activityForm.addEventListener('submit', async function (event) {
            event.preventDefault();

            const selected = manualActivity.value.trim() || 'Belajar';

            try {
                await ActivityDB.save(CATEGORY, {
                    date: entryDate.value,
                    option: selected,
                    note: activityNote.value.trim(),
                    image: selectedImageDataUrl || (imageInput.files && imageInput.files[0]) || null
                });

                currentPage = 1;
                await loadHistory();
                closeModal();

                successNotification.classList.remove('hidden');
                clearTimeout(successNotificationTimer);
                successNotificationTimer = setTimeout(() => {
                    successNotification.classList.add('hidden');
                }, 4000);
            } catch (error) {
                alert('Gagal menyimpan data: ' + error.message);
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
                if (typeof ActivityDB !== 'undefined' && ActivityDB.list) {
                    activityItems = await ActivityDB.list(CATEGORY);
                } else {
                    activityItems = [];
                }
                renderHistory();
            } catch (error) {
                historyList.innerHTML = `<p class="text-xs text-rose-600 p-3 bg-rose-50 rounded-lg">Gagal memuat data: ${escapeHtml(error.message)}</p>`;
                pager.innerHTML = '';
            }
        }

        document.addEventListener('DOMContentLoaded', loadHistory);
    </script>
</body>
</html>