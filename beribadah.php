<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beribadah</title>
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
                <span class="text-[2.1rem]" aria-label="logo ibadah">🕌</span>
            </div>
            <h1 class="mt-7 text-[2.5rem] font-extrabold tracking-[-0.05em] text-white">Beribadah</h1>
            <p class="mt-1 text-lg font-medium text-white/85">Aplikasi Tujuh Kebiasaan Anak Indonesia Hebat</p>
        </div>
    </div>

    <div class="relative z-20 mx-auto -mt-16 w-full max-w-[460px] px-4">
        <div class="rounded-[30px] bg-white p-5 shadow-[0_20px_40px_rgba(15,23,42,0.10)] border border-slate-100">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <p class="text-[0.68rem] font-extrabold uppercase tracking-[0.18em] text-slate-500">Riwayat</p>
                    <h2 class="text-xl font-extrabold text-slate-800">Kegiatan Beribadah</h2>
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
                    <label class="mb-2 block text-[0.72rem] font-extrabold uppercase tracking-[0.14em] text-slate-700">Pilihan Ibadah</label>
                    <div id="prayerOptions" class="grid grid-cols-2 gap-2">
                        <button type="button" data-prayer="Subuh" class="prayer-option rounded-[12px] border border-slate-200 bg-slate-50 px-3 py-3 text-sm font-bold text-slate-700 transition hover:border-[#0d6b4e] hover:bg-emerald-50 disabled:cursor-not-allowed disabled:opacity-50">Sholat Subuh</button>
                        <button type="button" data-prayer="Dzuhur" class="prayer-option rounded-[12px] border border-slate-200 bg-slate-50 px-3 py-3 text-sm font-bold text-slate-700 transition hover:border-[#0d6b4e] hover:bg-emerald-50 disabled:cursor-not-allowed disabled:opacity-50">Sholat Dzuhur</button>
                        <button type="button" data-prayer="Ashar" class="prayer-option rounded-[12px] border border-slate-200 bg-slate-50 px-3 py-3 text-sm font-bold text-slate-700 transition hover:border-[#0d6b4e] hover:bg-emerald-50 disabled:cursor-not-allowed disabled:opacity-50">Sholat Ashar</button>
                        <button type="button" data-prayer="Maghrib" class="prayer-option rounded-[12px] border border-slate-200 bg-slate-50 px-3 py-3 text-sm font-bold text-slate-700 transition hover:border-[#0d6b4e] hover:bg-emerald-50 disabled:cursor-not-allowed disabled:opacity-50">Sholat Maghrib</button>
                        <button type="button" data-prayer="Isya" class="prayer-option rounded-[12px] border border-slate-200 bg-slate-50 px-3 py-3 text-sm font-bold text-slate-700 transition hover:border-[#0d6b4e] hover:bg-emerald-50 disabled:cursor-not-allowed disabled:opacity-50">Sholat Isya</button>
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-[0.72rem] font-extrabold uppercase tracking-[0.14em] text-slate-700">Catatan Ibadah</label>
                    <textarea id="activityNote" rows="3" placeholder="Tuliskan pengalaman ibadah hari ini..." class="w-full rounded-[12px] border border-slate-200 bg-slate-50 px-3 py-3 text-base font-medium text-slate-700 focus:border-[#0d6b4e] focus:bg-white focus:outline-none focus:ring-4 focus:ring-emerald-100"></textarea>
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
        const STORAGE_KEY = 'beribadah_history';
        const addModal = document.getElementById('addModal');
        const openAddModalBtn = document.getElementById('openAddModalBtn');
        const closeAddModalBtn = document.getElementById('closeAddModalBtn');
        const cancelAddModalBtn = document.getElementById('cancelAddModalBtn');
        const historyList = document.getElementById('historyList');
        const activityForm = document.getElementById('activityForm');
        const entryDate = document.getElementById('entryDate');
        const entryTime = document.getElementById('entryTime');
        const prayerOptions = document.querySelectorAll('.prayer-option');

        function setAutoDateTime() {
            const now = new Date();
            entryDate.value = now.toISOString().split('T')[0];
            entryTime.value = now.toTimeString().slice(0, 5);
        }

        function getPrayerLabel(timeString) {
            const [hours, minutes] = timeString.split(':').map(Number);
            const totalMinutes = hours * 60 + minutes;

            if (totalMinutes >= 0 && totalMinutes < 5 * 60) {
                return 'Sholat Subuh';
            }
            if (totalMinutes >= 5 * 60 && totalMinutes < 12 * 60) {
                return 'Sholat Dzuhur';
            }
            if (totalMinutes >= 12 * 60 && totalMinutes < 15 * 60) {
                return 'Sholat Ashar';
            }
            if (totalMinutes >= 15 * 60 && totalMinutes < 18 * 60) {
                return 'Sholat Maghrib';
            }
            if (totalMinutes >= 18 * 60) {
                return 'Sholat Isya';
            }
            return 'Sholat';
        }

        function syncPrayerSelection() {
            const currentPrayer = getPrayerLabel(entryTime.value || new Date().toTimeString().slice(0, 5));
            prayerOptions.forEach(button => {
                const isCurrent = button.dataset.prayer === currentPrayer.replace('Sholat ', '');
                const isSelected = button.classList.contains('selected');

                button.disabled = !isCurrent && !isSelected;
                button.classList.toggle('bg-emerald-100', isCurrent || isSelected);
                button.classList.toggle('border-emerald-500', isCurrent || isSelected);
                button.classList.toggle('text-emerald-700', isCurrent || isSelected);
                button.classList.toggle('selected', isSelected);

                if (isCurrent && !isSelected) {
                    button.classList.add('selected');
                    button.disabled = false;
                }
            });
        }

        function openModal() {
            setAutoDateTime();
            syncPrayerSelection();
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

        function renderHistory() {
            const items = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');

            if (!items.length) {
                historyList.innerHTML = `
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4 text-center text-sm font-medium text-slate-500">
                        Belum ada riwayat ibadah.
                    </div>
                `;
                return;
            }

            historyList.innerHTML = items.map(item => `
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-bold text-slate-800">${formatDate(item.date)}</p>
                            <p class="text-xs text-slate-500">${item.time} • ${item.summary}</p>
                            ${item.note ? `<p class="mt-1 text-xs text-slate-600">${item.note}</p>` : ''}
                        </div>
                        <span class="rounded-full bg-emerald-100 px-2 py-1 text-[10px] font-bold uppercase tracking-[0.12em] text-emerald-700">Tercatat</span>
                    </div>
                </div>
            `).join('');
        }

        prayerOptions.forEach(button => {
            button.addEventListener('click', () => {
                if (button.disabled) return;
                prayerOptions.forEach(item => {
                    item.classList.remove('selected', 'bg-emerald-100', 'border-emerald-500', 'text-emerald-700');
                    item.disabled = item !== button;
                });
                button.classList.add('selected', 'bg-emerald-100', 'border-emerald-500', 'text-emerald-700');
            });
        });

        activityForm.addEventListener('submit', function (event) {
            event.preventDefault();

            const selectedPrayer = document.querySelector('.prayer-option.selected')?.dataset.prayer || getPrayerLabel(entryTime.value || new Date().toTimeString().slice(0, 5)).replace('Sholat ', '');
            const currentTime = entryTime.value || new Date().toTimeString().slice(0, 5);

            const entry = {
                date: entryDate.value,
                time: currentTime,
                summary: `Sholat ${selectedPrayer}`,
                note: document.getElementById('activityNote').value.trim()
            };

            const items = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
            items.unshift(entry);
            localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
            renderHistory();
            activityForm.reset();
            closeModal();
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
