<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beribadah</title>
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
<body class="flex min-h-screen flex-col bg-[#edf1ee] text-slate-800 antialiased">
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
                        src="https://cerdasberkarakter.kemendikdasmen.go.id/wp-content/uploads/2024/12/2-beribadah.png"
                        alt="logo beribadah"
                        class="h-full w-full object-cover"
                    />
                </div>

                <div class="min-w-0 text-left">
                    <h1 class="text-3xl font-extrabold leading-none tracking-[-0.05em] text-white">Beribadah</h1>
                    <p class="mt-2 text-sm font-medium leading-snug text-emerald-50">Catat waktu ibadahmu hari ini</p>
                </div>
            </div>
        </div>
    </div>

    <div class="relative z-20 mx-auto -mt-12 w-full max-w-[460px] flex-1 px-4">
        <div id="successNotification" class="mb-3 hidden rounded-r-xl border-l-4 border-emerald-600 bg-emerald-100 p-3.5 text-xs font-bold text-emerald-900 shadow-sm" role="status" aria-live="polite">
            Catatan kegiatan ibadah berhasil disimpan!
        </div>

        <div class="rounded-[30px] border border-slate-200 bg-white p-5 shadow-[0_20px_40px_rgba(15,23,42,0.10)]">
            <div class="mb-4">
                <p class="text-[0.68rem] font-extrabold uppercase tracking-[0.18em] text-slate-500">Riwayat</p>
                <h2 class="mt-1 text-xl font-extrabold text-slate-800">Kegiatan Beribadah</h2>
            </div>

            <div id="historyList" class="space-y-3"></div>
            <div id="pager" class="mt-4 flex items-center justify-between gap-3"></div>
        </div>
    </div>

    <div id="addModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 p-3">
        <div class="max-h-[calc(100vh-1.5rem)] w-full max-w-md overflow-y-auto rounded-[26px] bg-white p-3 shadow-[0_30px_80px_rgba(15,23,42,0.25)]">
            <div class="mb-3 flex items-center justify-between gap-3">
                <h2 class="text-xl font-extrabold text-slate-800">Tambah Kegiatan</h2>
                <button type="button" id="closeAddModalBtn" class="text-2xl font-light text-slate-500">×</button>
            </div>

            <form id="activityForm" class="space-y-3">
                <div>
                    <label class="mb-1 block text-[0.72rem] font-extrabold uppercase tracking-[0.14em] text-slate-700">Pilih Sholat</label>

                    <div id="prayerChoiceWrapper" class="space-y-2">
                        <div class="space-y-2">
                            <button type="button" data-group="siang" class="prayer-group-btn w-full rounded-[14px] border border-slate-200 bg-slate-50 px-3 py-2 text-left text-sm font-bold text-slate-700 transition hover:border-[#0d6b4e] hover:bg-emerald-50">
                                <span class="block">Sholat</span>
                                <span class="mt-0.5 block text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-500">Zuhur / Ashar</span>
                            </button>

                            <div id="siangPrayerOptions" class="hidden grid grid-cols-2 gap-2">
                                <button type="button" data-option="Sholat Dzuhur" class="option-btn rounded-[12px] border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-bold text-slate-700 transition hover:border-[#0d6b4e] hover:bg-emerald-50">Sholat Dzuhur</button>
                                <button type="button" data-option="Sholat Ashar" class="option-btn rounded-[12px] border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-bold text-slate-700 transition hover:border-[#0d6b4e] hover:bg-emerald-50">Sholat Ashar</button>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <button type="button" data-group="triggered" class="prayer-group-btn w-full rounded-[14px] border border-slate-200 bg-slate-50 px-3 py-2 text-left text-sm font-bold text-slate-700 transition hover:border-[#0d6b4e] hover:bg-emerald-50">
                                <span class="block">Sholat</span>
                                <span class="mt-0.5 block text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-500">Subuh / Maghrib / Isya</span>
                            </button>

                            <div id="triggeredPrayerOptions" class="hidden grid grid-cols-2 gap-2">
                                <button type="button" data-option="Sholat Subuh" class="trigger-option rounded-[12px] border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-bold text-slate-700 transition opacity-60">Sholat Subuh</button>
                                <button type="button" data-option="Sholat Maghrib" class="trigger-option rounded-[12px] border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-bold text-slate-700 transition opacity-60">Sholat Maghrib</button>
                                <button type="button" data-option="Sholat Isya" class="trigger-option rounded-[12px] border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-bold text-slate-700 transition opacity-60">Sholat Isya</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Unggah Foto Kegiatan -->
                <div>
                    <label class="block text-xs font-extrabold text-slate-800 uppercase tracking-wide mb-1.5">
                        Foto Kegiatan <span class="font-semibold normal-case tracking-normal text-slate-500">(Opsional)</span>
                    </label>
                    <div class="flex items-center gap-3 p-2 bg-slate-50 border-2 border-slate-300 rounded-xl">
                        <label class="cursor-pointer bg-emerald-800 hover:bg-emerald-900 active:bg-emerald-950 text-white font-bold text-xs px-4 py-2 rounded-xl transition-all inline-flex items-center shrink-0 shadow-sm">
                            Choose File
                            <input id="imageInput" type="file" name="foto" accept="image/*" capture="environment" class="hidden" />
                        </label>
                        <span id="fileNameDisplay" class="text-xs font-semibold text-slate-400 truncate">
                            No file chosen
                        </span>
                    </div>
                </div>

                <div class="mt-3 flex items-center justify-between gap-3">
                    <button type="button" id="cancelAddModalBtn" class="flex-1 rounded-[12px] border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700">Batal</button>
                    <button type="submit" class="flex-1 rounded-[12px] bg-[#0d6b4e] px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-emerald-800/20">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <footer class="mt-auto py-10 text-center text-sm text-slate-500">
        © 2026 Tujuh Kebiasaan Anak Indonesia Hebat
    </footer>

    <script src="activity-db.js"></script>
    <script>
        const CATEGORY = 'ibadah';
        const ITEMS_PER_PAGE = 5;
        const addModal = document.getElementById('addModal');
        const successNotification = document.getElementById('successNotification');
        const openAddModalBtn = document.getElementById('openAddModalBtn');
        const closeAddModalBtn = document.getElementById('closeAddModalBtn');
        const cancelAddModalBtn = document.getElementById('cancelAddModalBtn');
        const historyList = document.getElementById('historyList');
        const pager = document.getElementById('pager');
        const activityForm = document.getElementById('activityForm');
        const imageInput = document.getElementById('imageInput');
        const fileNameDisplay = document.getElementById('fileNameDisplay');
        const optionButtons = document.querySelectorAll('.option-btn');
        const prayerGroupButtons = document.querySelectorAll('.prayer-group-btn');
        const prayerChoiceWrapper = document.getElementById('prayerChoiceWrapper');
        const siangPrayerOptions = document.getElementById('siangPrayerOptions');
        const triggeredPrayerOptions = document.getElementById('triggeredPrayerOptions');
        let currentPage = 1;
        let activityItems = [];
        let successNotificationTimer = null;

        function setAutoDateTime() {
            const now = new Date();
            return {
                date: ActivityDB.today(),
                time: now.toTimeString().slice(0, 5)
            };
        }

        function getActiveTriggeredPrayer() {
            const now = new Date();
            const minutesNow = now.getHours() * 60 + now.getMinutes();

            if (minutesNow >= 4 * 60 && minutesNow < 6 * 60) return 'Sholat Subuh';
            if (minutesNow >= 17 * 60 + 30 && minutesNow < 19 * 60) return 'Sholat Maghrib';
            if (minutesNow >= 19 * 60) return 'Sholat Isya';
            return null;
        }

        function setSelectedPrayer(option) {
            optionButtons.forEach(item => {
                const isSelected = item.dataset.option === option;
                item.classList.toggle('bg-emerald-100', isSelected);
                item.classList.toggle('border-emerald-500', isSelected);
                item.classList.toggle('text-emerald-700', isSelected);
                item.classList.toggle('opacity-60', !isSelected && item.classList.contains('trigger-option'));
            });

            document.querySelectorAll('.trigger-option').forEach(button => {
                const active = button.dataset.option === option;
                button.disabled = true;
                button.classList.toggle('bg-emerald-100', active);
                button.classList.toggle('border-emerald-500', active);
                button.classList.toggle('text-emerald-700', active);
                button.classList.toggle('opacity-60', !active);
                button.classList.toggle('cursor-not-allowed', true);
            });
        }

        function refreshPrayerButtons() {
            const activeTriggeredPrayer = getActiveTriggeredPrayer();
            const hasTriggeredPrayer = Boolean(activeTriggeredPrayer);

            prayerChoiceWrapper.classList.remove('hidden');

            siangPrayerOptions.classList.add('hidden');
            triggeredPrayerOptions.classList.add('hidden');

            if (hasTriggeredPrayer) {
                triggeredPrayerOptions.classList.remove('hidden');
                setSelectedPrayer(activeTriggeredPrayer);
            }
        }

        function openModal() {
            addModal.classList.remove('hidden');
            addModal.classList.add('flex');
            refreshPrayerButtons();
        }

        function closeModal() {
            addModal.classList.add('hidden');
            addModal.classList.remove('flex');
        }

        function formatDate(value) {
            const date = new Date(value + 'T00:00:00');
            return new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }).format(date);
        }

        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, char => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[char]));
        }

        function handleImageSelect() {
            const file = imageInput.files && imageInput.files[0];
            if (!file) {
                fileNameDisplay.textContent = 'No file chosen';
                fileNameDisplay.classList.add('text-slate-400');
                fileNameDisplay.classList.remove('text-slate-700');
                return;
            }

            fileNameDisplay.textContent = file.name;
            fileNameDisplay.classList.remove('text-slate-400');
            fileNameDisplay.classList.add('text-slate-700');
        }

        prayerGroupButtons.forEach(groupButton => {
            groupButton.addEventListener('click', function () {
                const group = groupButton.dataset.group;

                prayerGroupButtons.forEach(btn => {
                    btn.classList.remove('bg-emerald-100', 'border-emerald-500', 'text-emerald-700');
                });
                groupButton.classList.add('bg-emerald-100', 'border-emerald-500', 'text-emerald-700');

                if (group === 'siang') {
                    siangPrayerOptions.classList.remove('hidden');
                    triggeredPrayerOptions.classList.add('hidden');
                    optionButtons.forEach(item => {
                        const isTriggered = ['Sholat Subuh', 'Sholat Maghrib', 'Sholat Isya'].includes(item.dataset.option);
                        if (isTriggered) {
                            item.classList.remove('bg-emerald-100', 'border-emerald-500', 'text-emerald-700');
                        }
                    });
                }

                if (group === 'triggered') {
                    const activeTriggeredPrayer = getActiveTriggeredPrayer();
                    siangPrayerOptions.classList.add('hidden');
                    triggeredPrayerOptions.classList.remove('hidden');

                    if (activeTriggeredPrayer) {
                        setSelectedPrayer(activeTriggeredPrayer);
                    }
                }
            });
        });

        optionButtons.forEach(button => {
            button.addEventListener('click', function () {
                if (button.classList.contains('trigger-option')) {
                    return;
                }

                optionButtons.forEach(item => {
                    const isTriggered = ['Sholat Subuh', 'Sholat Maghrib', 'Sholat Isya'].includes(item.dataset.option);
                    if (!isTriggered) {
                        item.classList.remove('bg-emerald-100', 'border-emerald-500', 'text-emerald-700');
                    }
                });
                button.classList.add('bg-emerald-100', 'border-emerald-500', 'text-emerald-700');
            });
        });

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
                        <div class="mx-auto mb-2 flex h-14 w-14 items-center justify-center rounded-full bg-emerald-50">
                            <i class="fa-solid fa-mosque text-2xl text-emerald-600" aria-label="Ikon ibadah"></i>
                        </div>
                        <p class="text-lg font-extrabold text-slate-800">Kamu belum mengisi data</p>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">
                            Belum ada data ibadah. Klik tombol <span class="font-bold text-emerald-700">“Baru”</span> di atas untuk menambahkan kegiatan.
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

                            <p class="text-sm font-extrabold text-slate-800">${escapeHtml(item.option || 'Ibadah')}</p>
                            <p class="text-[11px] leading-relaxed text-slate-600">
                                ${item.note ? escapeHtml(item.note) : `Pelaksanaan ${escapeHtml(item.option || 'ibadah')} sesuai jadwal.`}
                            </p>
                        </div>

                        <div class="w-28 shrink-0 overflow-hidden rounded-[12px] border border-emerald-100 bg-white">
                            ${item.image ? `<img src="${item.image}" class="h-24 w-full object-cover" alt="Foto kegiatan ibadah" />` : '<div class="flex h-24 w-full items-center justify-center bg-[#edf9f0] text-2xl text-slate-400">🕌</div>'}
                        </div>
                    </div>
                </div>
            `).join('');

            renderPager(items.length);
        }

        activityForm.addEventListener('submit', async function (event) {
            event.preventDefault();

            const selectedPrayer = document.querySelector('.bg-emerald-100[data-option]')?.dataset.option || getActiveTriggeredPrayer() || 'Sholat Dzuhur';
            const file = imageInput.files && imageInput.files[0];
            const timestamp = setAutoDateTime();
            try {
                await ActivityDB.save(CATEGORY, {
                    date: timestamp.date,
                    option: selectedPrayer,
                    note: '',
                    image: file || null
                });
                currentPage = 1;
                await loadHistory();
                activityForm.reset();
                fileNameDisplay.textContent = 'No file chosen';
                fileNameDisplay.classList.add('text-slate-400');
                fileNameDisplay.classList.remove('text-slate-700');
                optionButtons.forEach(item => item.classList.remove('bg-emerald-100', 'border-emerald-500', 'text-emerald-700'));
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