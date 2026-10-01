<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once '../koneksi.php';

// Validasi Keamanan Akses Super User (Tim 7 KAIH)
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'guru' || ($_SESSION['guru_role'] ?? '') !== 'super-user') {
    header("Location: ../index.php");
    exit();
}

$guru_id   = $_SESSION['user_id'];
$guru_nama = $_SESSION['nama'];
$guru_role = $_SESSION['guru_role'];

// Mengambil semua daftar kelas untuk dropdown filter
$stmt_kelas = $pdo->query("SELECT * FROM kelas ORDER BY nama_kelas ASC");
$kelas_list = $stmt_kelas->fetchAll();

$selected_kelas = $_GET['kelas_id'] ?? null;

// Mengambil data siswa berdasarkan filter kelas
if (!empty($selected_kelas)) {
    $stmt = $pdo->prepare("
        SELECT s.*, k.nama_kelas 
        FROM siswa s 
        LEFT JOIN kelas k ON s.id_kelas = k.id 
        WHERE s.id_kelas = :kelas_id 
        ORDER BY s.nama ASC
    ");
    $stmt->execute(['kelas_id' => $selected_kelas]);
} else {
    $stmt = $pdo->query("
        SELECT s.*, k.nama_kelas 
        FROM siswa s 
        LEFT JOIN kelas k ON s.id_kelas = k.id 
        ORDER BY k.nama_kelas ASC, s.nama ASC
    ");
}
$siswa_list = $stmt->fetchAll();

// Hitung Ringkasan Statistik untuk Tim 7 KAIH
$total_siswa = count($siswa_list);
$total_kelas = count($kelas_list);

// Daftar 7 Kebiasaan Anak Indonesia Hebat
$list_kebiasaan = [
    ['nama' => 'Bangun Pagi', 'icon' => 'fa-sun', 'color' => 'bg-amber-100 text-amber-800 border-amber-200'],
    ['nama' => 'Beribadah', 'icon' => 'fa-hands-praying', 'color' => 'bg-emerald-100 text-emerald-800 border-emerald-200'],
    ['nama' => 'Berolahraga', 'icon' => 'fa-person-running', 'color' => 'bg-blue-100 text-blue-800 border-blue-200'],
    ['nama' => 'Makan Sehat', 'icon' => 'fa-apple-whole', 'color' => 'bg-rose-100 text-rose-800 border-rose-200'],
    ['nama' => 'Gemar Membaca', 'icon' => 'fa-book-open', 'color' => 'bg-purple-100 text-purple-800 border-purple-200'],
    ['nama' => 'Istirahat Cukup', 'icon' => 'fa-bed', 'color' => 'bg-indigo-100 text-indigo-800 border-indigo-200'],
    ['nama' => 'Bermasyarakat', 'icon' => 'fa-handshake', 'color' => 'bg-teal-100 text-teal-800 border-teal-200'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Tim 7 KAIH</title>
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
                
                <a href="index.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl bg-emerald-800 text-white font-bold text-xs shadow-sm transition-all">
                    <i class="fa-solid fa-chart-pie w-5 text-center text-emerald-300"></i>
                    <span>Dashboard Tim 7 KAIH</span>
                </a>

                <div class="px-3 pt-4 pb-2 text-[10px] font-extrabold uppercase tracking-wider text-emerald-400">
                    Akses Khusus
                </div>
                <a href="#" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-emerald-200 hover:bg-emerald-800/60 hover:text-white font-bold text-xs transition-all">
                    <i class="fa-solid fa-school w-5 text-center"></i>
                    <span>Kelola Data Kelas</span>
                </a>
            </nav>
        </div>

        <!-- Profil & Logout -->
        <div class="p-4 border-t border-emerald-800/80 bg-emerald-950/40">
            <div class="flex items-center gap-3 mb-3 px-2">
                <div class="w-9 h-9 rounded-full bg-emerald-700 text-white flex items-center justify-center font-bold text-sm shrink-0">
                    <i class="fa-solid fa-user-shield"></i>
                </div>
                <div class="overflow-hidden">
                    <p class="text-xs font-extrabold text-white truncate"><?= htmlspecialchars($guru_nama) ?></p>
                    <span class="inline-block px-2 py-0.5 text-[9px] font-extrabold uppercase rounded bg-emerald-800 text-emerald-200 mt-0.5">
                        Tim 7 KAIH
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
                            Dashboard Tim 7 KAIH
                        </h1>
                        <p class="text-xs text-slate-500 font-medium hidden sm:block">Pemantauan & Evaluasi Kebiasaan Siswa</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="text-right hidden sm:block">
                        <span class="block text-xs font-bold text-slate-800"><?= htmlspecialchars($guru_nama) ?></span>
                        <span class="inline-block px-2 py-0.5 text-[10px] font-extrabold uppercase rounded bg-emerald-100 text-emerald-800 border border-emerald-200">
                            Tim 7 KAIH
                        </span>
                    </div>
                    <button id="open-sidebar" type="button" class="lg:hidden p-2 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 transition-all">
                        <i class="fa-solid fa-bars text-lg"></i>
                    </button>
                </div>

            </div>
        </header>

        <!-- MAIN CONTENT AREA -->
        <main class="flex-1 max-w-5xl w-full mx-auto p-4 sm:p-6 space-y-4">

            <!-- Identitas Super User -->
            <section class="flex items-center gap-4 rounded-2xl border border-slate-200 border-l-[6px] border-l-emerald-800 bg-white p-5 shadow-sm">
                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-800">
                    <i class="fa-solid fa-user-shield text-3xl"></i>
                </div>
                <div class="min-w-0">
                    <h2 class="truncate text-lg font-extrabold text-slate-900 sm:text-xl">
                        <?= htmlspecialchars($guru_nama) ?>
                    </h2>
                    <p class="mt-1 text-sm font-bold text-emerald-700">
                        Tim 7 Kebiasaan Anak Indonesia Hebat
                    </p>
                </div>
            </section>

            <!-- Kartu Statistik Singkat -->
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center text-lg font-black">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div>
                        <span class="block text-xs text-slate-500 font-bold">Total Siswa Terdata</span>
                        <span class="text-lg font-black text-slate-900"><?= $total_siswa ?> Siswa</span>
                    </div>
                </div>
                <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-lg font-black">
                        <i class="fa-solid fa-school"></i>
                    </div>
                    <div>
                        <span class="block text-xs text-slate-500 font-bold">Total Kelas</span>
                        <span class="text-lg font-black text-slate-900"><?= $total_kelas ?> Kelas</span>
                    </div>
                </div>
            </div>

            <!-- Filter Tampilan Kelas -->
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3">
                <span class="text-xs font-bold text-slate-700">Filter Tampilan Kelas:</span>
                <form method="GET" action="" class="w-full sm:w-auto">
                    <select name="kelas_id" id="kelas_id" onchange="this.form.submit()" class="w-full sm:w-auto bg-slate-50 border-2 border-slate-300 rounded-xl px-3 py-2 text-xs font-bold focus:outline-none focus:border-emerald-600">
                        <option value="">-- Semua Kelas --</option>
                        <?php foreach ($kelas_list as $k): ?>
                            <option value="<?= $k['id'] ?>" <?= ($selected_kelas == $k['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($k['nama_kelas']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>

            <!-- Toolbar Pencarian -->
            <div class="flex items-center gap-3">
                <div class="relative flex-1">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                    <input type="text" id="searchInput" onkeyup="filterSiswa()" placeholder="Cari nama siswa..." class="w-full pl-10 pr-4 py-3 bg-white border border-slate-300 rounded-full text-xs font-bold placeholder-slate-400 focus:outline-none focus:border-emerald-600 shadow-sm transition-all">
                </div>
            </div>

            <!-- Banner Info Total Siswa -->
            <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 flex items-center gap-3 text-blue-900 shadow-sm">
                <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center shrink-0 font-bold text-sm">
                    <i class="fa-solid fa-circle-info"></i>
                </div>
                <p class="text-xs sm:text-sm font-bold leading-relaxed">
                    Menampilkan total <span id="totalSiswaCount"><?= count($siswa_list) ?></span> siswa pada tampilan ini.
                </p>
            </div>

            <!-- LIST DAFTAR SISWA -->
            <div id="siswaGrid" class="space-y-4">
                <?php if (!empty($siswa_list)): ?>
                    <?php foreach ($siswa_list as $s): ?>
                        <div class="siswa-card bg-white rounded-2xl p-5 border border-slate-200 shadow-sm hover:shadow-md transition-all space-y-4" data-nama="<?= strtolower(htmlspecialchars($s['nama'])) ?>">
                            
                            <!-- Header Kartu: Avatar, Nama, NISN & Kelas -->
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3.5 min-w-0">
                                    <div class="w-12 h-12 rounded-full bg-blue-500 text-white flex items-center justify-center font-black text-lg shadow-sm shrink-0 uppercase">
                                        <?= mb_substr(trim($s['nama']), 0, 1) ?>
                                    </div>
                                    <div class="truncate">
                                        <h3 class="font-extrabold text-sm sm:text-base text-slate-900 truncate uppercase tracking-tight">
                                            <?= htmlspecialchars($s['nama']) ?>
                                        </h3>
                                        <p class="text-xs text-slate-500 font-semibold mt-0.5">
                                            NISN: <?= htmlspecialchars($s['nisn'] ?? '-') ?> &bull; 
                                            <span class="text-emerald-700 font-bold"><?= htmlspecialchars($s['nama_kelas'] ?? 'Tanpa Kelas') ?></span>
                                        </p>
                                    </div>
                                </div>

                                <!-- Tombol Laporan -->
                                <a href="../laporan_siswa.php?id=<?= $s['id'] ?>" class="shrink-0 bg-emerald-700 hover:bg-emerald-800 text-white px-3.5 py-2 rounded-xl text-xs font-bold transition-all inline-flex items-center gap-1.5 shadow-sm">
                                    <i class="fa-solid fa-file-lines"></i>
                                    <span class="hidden sm:inline">Laporan</span>
                                </a>
                            </div>

                            <!-- Indikator Tingkat Kebiasaan -->
                            <div>
                                <div class="flex justify-between items-center text-[11px] font-extrabold text-slate-500 uppercase tracking-wider mb-1.5">
                                    <span>Tingkat Kepatuhan 7 Kebiasaan</span>
                                    <span class="text-emerald-700 font-black">100%</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                    <div class="bg-emerald-600 h-2 rounded-full w-full"></div>
                                </div>
                            </div>

                            <!-- List Grid 7 Kebiasaan -->
                            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-2 pt-1">
                                <?php foreach ($list_kebiasaan as $kebiasaan): ?>
                                    <div class="flex flex-col items-center justify-center p-2 rounded-xl border <?= $kebiasaan['color'] ?> text-center transition-all hover:scale-[1.02]">
                                        <i class="fa-solid <?= $kebiasaan['icon'] ?> text-base mb-1"></i>
                                        <span class="text-[10px] font-extrabold leading-tight line-clamp-1">
                                            <?= $kebiasaan['nama'] ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="bg-white rounded-2xl p-8 text-center border border-slate-200">
                        <i class="fa-solid fa-user-slash text-4xl text-slate-300 mb-3"></i>
                        <p class="text-slate-500 font-bold text-sm">Belum ada data siswa untuk kelas yang dipilih.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pesan jika pencarian kosong -->
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