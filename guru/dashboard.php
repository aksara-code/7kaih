<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
date_default_timezone_set('Asia/Jakarta');
require_once '../koneksi.php';

// Validasi Keamanan Akses Guru
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'guru') {
    header("Location: ../index.php");
    exit();
}

$guru_id   = $_SESSION['user_id'];
$guru_nama = $_SESSION['nama'];
$guru_role = $_SESSION['guru_role'] ?? '';

// Sesi lama memakai nilai "admin" untuk akun wali kelas.
if ($guru_role === 'admin') {
    $guru_role = 'wali-kelas';
    $_SESSION['guru_role'] = $guru_role;
}

if ($guru_role === 'super-user') {
    header("Location: ../admin/dashboard.php");
    exit();
}

if ($guru_role !== 'wali-kelas') {
    header("Location: ../logout.php");
    exit();
}

$siswa_list = [];
$nama_kelas_wali = '';

// Wali kelas hanya melihat siswa dari kelas yang diampunya.
$stmt_wali = $pdo->prepare("SELECT id, nama_kelas FROM kelas WHERE id_guru = :id_guru LIMIT 1");
$stmt_wali->execute(['id_guru' => $guru_id]);
$kelas_wali = $stmt_wali->fetch();

if ($kelas_wali) {
    $nama_kelas_wali = $kelas_wali['nama_kelas'];
    $stmt = $pdo->prepare("
        SELECT s.*, k.nama_kelas
        FROM siswa s
        LEFT JOIN kelas k ON s.id_kelas = k.id
        WHERE s.id_kelas = :id_kelas
        ORDER BY s.nama ASC
    ");
    $stmt->execute(['id_kelas' => $kelas_wali['id']]);
    $siswa_list = $stmt->fetchAll();
}

$habitStats = [];
$daysInMonth = (int) date('t');
$monthStart = date('Y-m-01');
$nextMonthStart = (new DateTimeImmutable($monthStart))->modify('+1 month')->format('Y-m-d');
$monthNames = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
];
$monthLabel = $monthNames[(int) date('n')] . ' ' . date('Y');

if (!empty($siswa_list)) {
    $studentIds = array_map('intval', array_column($siswa_list, 'id'));
    $studentPlaceholders = implode(',', array_fill(0, count($studentIds), '?'));
    $stmt_stats = $pdo->prepare(
        "SELECT id_siswa, kategori, COUNT(DISTINCT DATE(waktu_mulai)) AS filled_days
         FROM log_aktivitas
         WHERE id_siswa IN ($studentPlaceholders)
           AND kategori IN ('bangun', 'ibadah', 'olahraga', 'makan', 'belajar', 'tidur', 'bermasyarakat')
           AND waktu_mulai >= ? AND waktu_mulai < ?
         GROUP BY id_siswa, kategori"
    );
    $stmt_stats->execute(array_merge($studentIds, [$monthStart, $nextMonthStart]));

    foreach ($stmt_stats->fetchAll() as $stat) {
        $habitStats[(int) $stat['id_siswa']][$stat['kategori']] = (int) $stat['filled_days'];
    }
}

// Daftar 7 Kebiasaan Anak Indonesia Hebat
$list_kebiasaan = [
    ['kategori' => 'bangun', 'nama' => 'Bangun Pagi', 'icon' => 'fa-sun', 'color' => 'bg-amber-100 text-amber-800 border-amber-200'],
    ['kategori' => 'ibadah', 'nama' => 'Beribadah', 'icon' => 'fa-hands-praying', 'color' => 'bg-emerald-100 text-emerald-800 border-emerald-200'],
    ['kategori' => 'olahraga', 'nama' => 'Berolahraga', 'icon' => 'fa-person-running', 'color' => 'bg-blue-100 text-blue-800 border-blue-200'],
    ['kategori' => 'makan', 'nama' => 'Makan Sehat', 'icon' => 'fa-apple-whole', 'color' => 'bg-rose-100 text-rose-800 border-rose-200'],
    ['kategori' => 'belajar', 'nama' => 'Gemar Belajar', 'icon' => 'fa-book-open', 'color' => 'bg-purple-100 text-purple-800 border-purple-200'],
    ['kategori' => 'tidur', 'nama' => 'Tidur Cepat', 'icon' => 'fa-bed', 'color' => 'bg-indigo-100 text-indigo-800 border-indigo-200'],
    ['kategori' => 'bermasyarakat', 'nama' => 'Bermasyarakat', 'icon' => 'fa-handshake', 'color' => 'bg-teal-100 text-teal-800 border-teal-200'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Tenaga Pendidik</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 antialiased min-h-screen flex">

    <!-- Overlay Backdrop untuk Mobile Sidebar -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-slate-900/50 z-40 hidden lg:hidden transition-opacity duration-300"></div>

    <!-- SIDEBAR -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-emerald-900 text-white flex flex-col justify-between transition-transform duration-300 transform -translate-x-full lg:translate-x-0 lg:static lg:inset-0 shadow-2xl shrink-0">
        <div>
            <!-- Header Sidebar -->
            <div class="p-5 flex items-center justify-between border-b border-emerald-800/80">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white text-emerald-800 flex items-center justify-center font-black text-xl shadow">
                        <i class="fa-solid fa-seedling"></i>
                    </div> 
                    <div>
                        <h1 class="font-black text-sm leading-tight text-white">7 Kebiasaan</h1>
                        <p class="text-[11px] text-emerald-300 font-medium">Anak Indonesia Hebat</p>
                    </div>
                </div>
                <button id="close-sidebar" class="lg:hidden text-emerald-200 hover:text-white p-1 text-lg">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Menu Navigation -->
            <nav class="p-4 space-y-1.5">
                <div class="px-3 py-2 text-[10px] font-extrabold uppercase tracking-wider text-emerald-400">
                    Menu Utama
                </div>
                
                <a href="dashboard.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl bg-emerald-800 text-white font-bold text-xs shadow-sm transition-all">
                    <i class="fa-solid fa-chart-pie w-5 text-center text-emerald-300"></i>
                    <span>Dashboard</span>
                </a>

            </nav>
        </div>

        <!-- Profil & Logout -->
        <div class="p-4 border-t border-emerald-800/80 bg-emerald-950/40">
            <div class="flex items-center gap-3 mb-3 px-2">
                <div class="w-9 h-9 rounded-full bg-emerald-700 text-white flex items-center justify-center font-bold text-sm shrink-0">
                    <i class="fa-solid fa-user-tie"></i>
                </div>
                <div class="overflow-hidden">
                    <p class="text-xs font-extrabold text-white truncate"><?= htmlspecialchars($guru_nama) ?></p>
                    <span class="inline-block px-2 py-0.5 text-[9px] font-extrabold uppercase rounded bg-emerald-800 text-emerald-200 mt-0.5">
                        <?= 'Wali Kelas ' . htmlspecialchars($nama_kelas_wali) ?>
                    </span>
                </div>
            </div>
            <a href="../logout.php" onclick="return confirm('Yakin ingin keluar?')" class="flex items-center justify-center gap-2 w-full py-2.5 px-4 bg-red-600/90 hover:bg-red-600 text-white rounded-xl font-bold text-xs transition-all shadow">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Keluar Aplikasi</span>
            </a>
        </div>
    </aside>

    <!-- CONTENT WRAPPER -->
    <div class="flex-1 flex flex-col min-w-0 min-h-screen">

        <!-- TOP NAVBAR -->
        <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-sm">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 py-3.5 flex items-center justify-between">
                
                <div class="flex items-center gap-3">
                    <div>
                        <h1 class="font-extrabold text-base sm:text-lg text-slate-900 leading-tight">
                            <?= 'Kelas ' . htmlspecialchars($nama_kelas_wali) ?>
                        </h1>
                        <p class="text-xs text-slate-500 font-medium hidden sm:block">Aplikasi Monitoring 7 Kebiasaan</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="text-right hidden sm:block">
                        <span class="block text-xs font-bold text-slate-800"><?= htmlspecialchars($guru_nama) ?></span>
                        <span class="inline-block px-2 py-0.5 text-[10px] font-extrabold uppercase rounded bg-emerald-100 text-emerald-800 border border-emerald-200">
                            <?= 'Wali Kelas ' . htmlspecialchars($nama_kelas_wali) ?>
                        </span>
                    </div>
                    <button id="open-sidebar" type="button" aria-label="Buka menu" title="Buka menu" class="lg:hidden p-2 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 transition-all">
                        <i class="fa-solid fa-bars text-lg"></i>
                    </button>
                </div>

            </div>
        </header>

        <!-- MAIN CONTENT AREA -->
        <main class="flex-1 max-w-5xl w-full mx-auto p-4 sm:p-6 space-y-4">

            <!-- Identitas Guru -->
            <section class="flex items-center gap-4 rounded-2xl border border-slate-200 border-l-[6px] border-l-blue-900 bg-white p-5 shadow-sm">
                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-slate-200 text-slate-500">
                    <i class="fa-solid fa-user-tie text-3xl"></i>
                </div>
                <div class="min-w-0">
                    <h2 class="truncate text-lg font-extrabold text-slate-900 sm:text-xl">
                        <?= htmlspecialchars($guru_nama) ?>
                    </h2>
                    <p class="mt-1 text-sm font-bold text-blue-700">
                        <?= 'Wali Kelas ' . htmlspecialchars($nama_kelas_wali ?: 'Belum ditentukan') ?>
                    </p>
                </div>
            </section>

            <!-- Toolbar Pencarian & Filter (Sesuai Referensi Foto) -->
            <div class="flex items-center gap-3">
                <div class="relative flex-1">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                    <input type="text" id="searchInput" onkeyup="filterSiswa()" placeholder="Cari nama siswa..." class="w-full pl-10 pr-4 py-3 bg-white border border-slate-300 rounded-full text-xs font-bold placeholder-slate-400 focus:outline-none focus:border-emerald-600 shadow-sm transition-all">
                </div>
            </div>

            <!-- Banner Info Total Siswa (Sesuai Referensi Foto) -->
            <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 flex items-center gap-3 text-blue-900 shadow-sm">
                <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center shrink-0 font-bold text-sm">
                    <i class="fa-solid fa-circle-info"></i>
                </div>
                <p class="text-xs sm:text-sm font-bold leading-relaxed">
                    Menampilkan total <span id="totalSiswaCount"><?= count($siswa_list) ?></span> siswa di kelas ini.
                </p>
            </div>

            <!-- LIST DAFTAR SISWA (White Cards Layout Sesuai Referensi) -->
            <div id="siswaGrid" class="space-y-4">
                <?php if (!empty($siswa_list)): ?>
                    <?php foreach ($siswa_list as $s): ?>
                        <?php
                        $studentHabitStats = $habitStats[(int) $s['id']] ?? [];
                        $overallProgress = 0;
                        foreach ($list_kebiasaan as $habit) {
                            $daysRecorded = $studentHabitStats[$habit['kategori']] ?? 0;
                            $overallProgress += min(100, (int) round($daysRecorded / $daysInMonth * 100));
                        }
                        $overallProgress = (int) round($overallProgress / count($list_kebiasaan));
                        ?>
                        <div class="siswa-card bg-white rounded-2xl p-3 sm:p-5 border border-slate-200 shadow-sm hover:shadow-md transition-all space-y-3 sm:space-y-4" data-nama="<?= strtolower(htmlspecialchars($s['nama'])) ?>">
                            
                            <!-- Header Kartu: Avatar, Nama, NISN & Tombol Laporan -->
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3.5 min-w-0">
                                    <!-- Avatar Inisial Bulat -->
                                    <div class="w-12 h-12 rounded-full bg-blue-500 text-white flex items-center justify-center font-black text-lg shadow-sm shrink-0 uppercase">
                                        <?= mb_substr(trim($s['nama']), 0, 1) ?>
                                    </div>
                                    <div class="truncate">
                                        <h3 class="font-extrabold text-sm sm:text-base text-slate-900 truncate uppercase tracking-tight">
                                            <?= htmlspecialchars($s['nama']) ?>
                                        </h3>
                                        <p class="text-xs text-slate-500 font-semibold mt-0.5">
                                            NISN: <?= htmlspecialchars($s['nisn'] ?? '-') ?>
                                        </p>
                                    </div>
                                </div>

                                <!-- Tombol Laporan -->
                                <a href="laporan_siswa.php?id=<?= $s['id'] ?>" class="shrink-0 bg-emerald-700 hover:bg-emerald-800 text-white px-3.5 py-2 rounded-xl text-xs font-bold transition-all inline-flex items-center gap-1.5 shadow-sm">
                                    <i class="fa-solid fa-file-lines"></i>
                                    <span class="hidden sm:inline">Laporan</span>
                                </a>
                            </div>

                            <!-- Indikator Tingkat Kebiasaan (Progress Bar) -->
                            <div>
                                <div class="flex justify-between items-center text-[11px] font-extrabold text-slate-500 uppercase tracking-wider mb-1.5">
                                    <span>Progress Bulan Ini · <?= htmlspecialchars($monthLabel) ?></span>
                                    <span class="text-emerald-700 font-black"><?= $overallProgress ?>%</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                    <div class="bg-emerald-600 h-2 rounded-full" style="width: <?= $overallProgress ?>%"></div>
                                </div>
                            </div>

                            <!-- Statistik harian tiap kebiasaan untuk bulan berjalan -->
                            <div class="grid grid-cols-4 sm:grid-cols-4 lg:grid-cols-7 gap-1.5 sm:gap-2 pt-1">
                                <?php foreach ($list_kebiasaan as $kebiasaan):
                                    $daysRecorded = $studentHabitStats[$kebiasaan['kategori']] ?? 0;
                                    $habitProgress = min(100, (int) round($daysRecorded / $daysInMonth * 100));
                                ?>
                                    <div role="group" aria-label="<?= htmlspecialchars($kebiasaan['nama']) ?>: <?= $daysRecorded ?> dari <?= $daysInMonth ?> hari, <?= $habitProgress ?> persen" class="flex min-w-0 min-h-[112px] sm:min-h-[124px] flex-col items-center justify-between rounded-lg border p-1.5 sm:p-2 text-center <?= $kebiasaan['color'] ?>">
                                        <i class="fa-solid <?= $kebiasaan['icon'] ?> text-sm sm:text-base" aria-hidden="true"></i>
                                        <span class="min-h-[24px] text-[9px] sm:text-[10px] font-extrabold leading-tight line-clamp-2">
                                            <?= htmlspecialchars($kebiasaan['nama']) ?>
                                        </span>
                                        <span class="text-[9px] font-bold whitespace-nowrap"><?= $daysRecorded ?>/<?= $daysInMonth ?> hari</span>
                                        <span class="text-[10px] font-black"><?= $habitProgress ?>%</span>
                                        <span class="h-1 w-full overflow-hidden rounded-full bg-white/70" aria-hidden="true">
                                            <span class="block h-full rounded-full bg-emerald-700" style="width: <?= $habitProgress ?>%"></span>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="bg-white rounded-2xl p-8 text-center border border-slate-200">
                        <i class="fa-solid fa-user-slash text-4xl text-slate-300 mb-3"></i>
                        <p class="text-slate-500 font-bold text-sm">Belum ada data siswa di kelas ini.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pesan jika hasil pencarian kosong -->
            <div id="noResults" class="hidden bg-white rounded-2xl p-8 text-center border border-slate-200">
                <i class="fa-solid fa-magnifying-glass text-3xl text-slate-300 mb-2"></i>
                <p class="text-slate-500 font-bold text-xs">Siswa dengan nama tersebut tidak ditemukan.</p>
            </div>

        </main>

        <!-- FOOTER -->
        <footer class="text-center py-6 border-t border-slate-200/80 bg-white mt-auto">
            <p class="text-xs text-slate-500 font-bold">&copy; <?= date('Y') ?> Tujuh Kebiasaan Anak Indonesia Hebat</p>
        </footer>

    </div>

    <!-- JavaScript: Toggle Sidebar & Search Filter -->
    <script>
        // Sidebar Toggle
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const openBtn = document.getElementById('open-sidebar');
        const closeBtn = document.getElementById('close-sidebar');

        function toggleSidebar() {
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        if (openBtn) openBtn.addEventListener('click', toggleSidebar);
        if (closeBtn) closeBtn.addEventListener('click', toggleSidebar);
        if (overlay) overlay.addEventListener('click', toggleSidebar);

        // Filter Pencarian Siswa Real-Time
        function filterSiswa() {
            const query = document.getElementById('searchInput').value.toLowerCase();
            const cards = document.querySelectorAll('.siswa-card');
            let visibleCount = 0;

            cards.forEach(card => {
                const nama = card.getAttribute('data-nama');
                if (nama.includes(query)) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            document.getElementById('totalSiswaCount').innerText = visibleCount;
            const noResults = document.getElementById('noResults');
            if (visibleCount === 0 && cards.length > 0) {
                noResults.classList.remove('hidden');
            } else {
                noResults.classList.add('hidden');
            }
        }
    </script>
</body>
</html>