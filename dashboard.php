<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
require_once 'koneksi.php';

$hour = (int) date('G');
if ($hour >= 5 && $hour < 11) {
    $greeting = 'selamat pagi';
} elseif ($hour >= 11 && $hour < 15) {
    $greeting = 'selamat siang';
} elseif ($hour >= 15 && $hour < 18) {
    $greeting = 'selamat sore';
} else {
    $greeting = 'selamat malam';
}

$role = $_SESSION['role'] ?? '';
$user = [
    'nama'  => $_SESSION['nama'] ?? 'Siswa',
    'kelas' => $role === 'guru' ? 'Guru' : 'Siswa'
];

if (isset($_SESSION['user_id'])) {
    if ($role === 'siswa') {
        $stmt_user = $pdo->prepare("SELECT s.nama, k.nama_kelas AS kelas FROM siswa s LEFT JOIN kelas k ON s.id_kelas = k.id WHERE s.id = :id LIMIT 1");
    } elseif ($role === 'guru') {
        $stmt_user = $pdo->prepare("SELECT g.nama, k.nama_kelas AS kelas FROM guru g LEFT JOIN kelas k ON g.id_kelas = k.id WHERE g.id = :id LIMIT 1");
    }

    if (isset($stmt_user)) {
        $stmt_user->execute(['id' => $_SESSION['user_id']]);
        $profile = $stmt_user->fetch();

        if ($profile) {
            $user['nama'] = $profile['nama'];
            $user['kelas'] = $profile['kelas'] ?? $user['kelas'];
            $_SESSION['nama'] = $profile['nama'];
        }
    }
}

$completedHabitsToday = 0;
$streakDays = 0;

if ($role === 'siswa' && isset($_SESSION['user_id'])) {
    $habitCategories = ['bangun', 'tidur', 'ibadah', 'belajar', 'makan', 'olahraga', 'bermasyarakat'];
    $categoryList = "'" . implode("','", $habitCategories) . "'";
    $stmt_progress = $pdo->prepare(
        "SELECT DATE(waktu_mulai) AS activity_date, COUNT(DISTINCT kategori) AS completed_count
         FROM log_aktivitas
         WHERE id_siswa = :id_siswa AND kategori IN ($categoryList)
         GROUP BY DATE(waktu_mulai)
         ORDER BY activity_date DESC"
    );
    $stmt_progress->execute(['id_siswa' => $_SESSION['user_id']]);

    $dailyCompletions = [];
    foreach ($stmt_progress->fetchAll() as $dailyActivity) {
        $dailyCompletions[$dailyActivity['activity_date']] = (int) $dailyActivity['completed_count'];
    }

    $today = date('Y-m-d');
    $completedHabitsToday = $dailyCompletions[$today] ?? 0;
    $streakDate = new DateTimeImmutable($today);

    if ($completedHabitsToday < count($habitCategories)) {
        $streakDate = $streakDate->modify('-1 day');
    }

    while (($dailyCompletions[$streakDate->format('Y-m-d')] ?? 0) === count($habitCategories)) {
        $streakDays++;
        $streakDate = $streakDate->modify('-1 day');
    }
}

if (isset($_SESSION['success'])) {
    echo "<script>alert('" . $_SESSION['success'] . "');</script>";
    unset($_SESSION['success']);
}

// Data habit / 7 Kebiasaan Anak Indonesia Hebat
$habits = [
    [
        'title' => 'Bangun Pagi',
        'url'   => 'bangun_pagi.php',
        'icon'  => 'fa-sun',
        'bg'    => 'bg-amber-100',
        'text'  => 'text-amber-600',
        'badge' => 'Wajib'
    ],
    [
        'title' => 'Beribadah',
        'url'   => 'beribadah.php',
        'icon'  => 'fa-hands-praying',
        'bg'    => 'bg-emerald-100',
        'text'  => 'text-emerald-600',
        'badge' => ''
    ],
    [
        'title' => 'Gemar Belajar',
        'url'   => 'gemar_belajar.php',
        'icon'  => 'fa-book-open-reader',
        'bg'    => 'bg-blue-100',
        'text'  => 'text-blue-600',
        'badge' => ''
    ],
    [
        'title' => 'Makan Sehat',
        'url'   => 'makan_sehat.php',
        'icon'  => 'fa-apple-whole',
        'bg'    => 'bg-rose-100',
        'text'  => 'text-rose-600',
        'badge' => ''
    ],
    [
        'title' => 'Olahraga',
        'url'   => 'olahraga.php',
        'icon'  => 'fa-person-running',
        'bg'    => 'bg-orange-100',
        'text'  => 'text-orange-600',
        'badge' => ''
    ],
    [
        'title' => 'Bermasyarakat',
        'url'   => 'bermasyarakat.php',
        'icon'  => 'fa-people-group',
        'bg'    => 'bg-purple-100',
        'text'  => 'text-purple-600',
        'badge' => ''
    ],
    [
        'title' => 'Tidur Cepat',
        'url'   => 'tidur_cepat.php',
        'icon'  => 'fa-moon',
        'bg'    => 'bg-indigo-100',
        'text'  => 'text-indigo-600',
        'badge' => ''
    ],
    [
        'title' => 'Profil Saya',
        'url'   => 'lengkapi_profil.php',
        'icon'  => 'fa-user-gear',
        'bg'    => 'bg-slate-200',
        'text'  => 'text-slate-700',
        'badge' => ''
    ],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Dashboard — Tujuh Kebiasaan</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            -webkit-tap-highlight-color: transparent;
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen flex flex-col justify-between antialiased text-slate-800">

    <div class="w-full max-w-md mx-auto min-h-screen flex flex-col justify-between bg-slate-50 shadow-2xl relative pb-20">
        
        <!-- HEADER ATAS BERGAYA APP MOBILE (HIJAU EMERALD) -->
        <div class="bg-emerald-800 text-white pt-8 pb-16 px-5 rounded-b-[2.5rem] shadow-lg relative overflow-hidden">
            <!-- Pattern Hiasan background -->
            <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-emerald-700/50 rounded-full blur-xl pointer-events-none"></div>
            <div class="absolute -left-10 -top-10 w-40 h-40 bg-emerald-600/30 rounded-full blur-xl pointer-events-none"></div>

            <div class="relative z-10 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-emerald-200">Hai, <?= htmlspecialchars($greeting) ?></p>
                    <h1 class="text-xl font-extrabold text-white tracking-tight leading-tight">
                        <?= htmlspecialchars($user['nama'] ?? 'Siswa') ?>
                    </h1>
                    <?php if ($role === 'guru'): ?>
                    <span class="inline-block mt-1 text-[10px] bg-emerald-900/60 text-emerald-200 px-2.5 py-0.5 rounded-full font-bold uppercase tracking-wider">
                        Guru
                    </span>
                    <?php endif; ?>
                </div>
                <div class="flex items-center gap-2">
                    <button class="w-10 h-10 rounded-full bg-emerald-700/60 border border-emerald-600 flex items-center justify-center text-white hover:bg-emerald-700 transition">
                        <i class="fa-regular fa-bell text-base"></i>
                    </button>
                    <a href="logout.php" onclick="return confirm('Apakah Anda yakin ingin keluar?');" class="w-10 h-10 rounded-full bg-emerald-700/60 border border-emerald-600 flex items-center justify-center text-red-200 hover:bg-red-600 hover:text-white transition" title="Keluar">
                        <i class="fa-solid fa-power-off text-base"></i>
                    </a>
                </div>
            </div>

            <!-- KARTU RINGKASAN AKTIVITAS (MEMOTONG HEADER) -->
            <div class="mt-6 bg-emerald-900/80 backdrop-blur-md rounded-2xl p-4 border border-emerald-600/50 text-white shadow-xl">
                <div class="flex items-center justify-between pb-2 border-b border-emerald-700/60">
                    <span class="text-xs text-emerald-200 font-semibold">Progres Hari Ini</span>
                    <span class="text-xs font-extrabold text-amber-300">7 Kebiasaan</span>
                </div>
                <div class="mt-3 flex items-center justify-between">
                    <div>
                        <p class="text-2xl font-black text-white tracking-tight"><?= $completedHabitsToday ?> / 7</p>
                        <p class="text-[11px] text-emerald-200 font-medium mt-0.5">Kebiasaan Terisi</p>
                    </div>
                    <div class="text-right">
                        <span class="px-3 py-1 bg-emerald-500/30 border border-emerald-400/40 rounded-xl text-xs font-bold text-emerald-100 inline-flex items-center gap-1.5">
                            <i class="fa-solid fa-fire text-amber-400"></i> Streak <?= $streakDays ?> Hari
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- MAIN CONTENT (MENU GRID 7 KEBIASAAN) -->
        <div class="px-5 -mt-6 z-20 flex-1">
            
            <!-- SEARCH BAR / CARI FITUR -->
            <div class="mb-6">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-sm"></i>
                    </div>
                    <input type="text" id="searchHabit" placeholder="Cari kebiasaan..." 
                           class="w-full pl-11 pr-4 py-3 bg-white border border-slate-200 rounded-2xl text-xs font-semibold text-slate-800 placeholder:text-slate-400 shadow-sm focus:outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/10 transition-all">
                </div>
            </div>

            <!-- JUDUL SEKSI -->
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-extrabold text-slate-800 tracking-wide uppercase">Menu Kebiasaan</h2>
                <span class="text-xs font-bold text-emerald-700">7 Kebiasaan Utama</span>
            </div>

            <!-- GRID ICON UTAMA (GAYA BRIMO 4 KOLOM) -->
            <div class="grid grid-cols-4 gap-y-6 gap-x-3 text-center" id="habitGrid">
                <?php foreach ($habits as $habit): ?>
                    <a href="<?= $habit['url'] ?>" class="habit-card group flex flex-col items-center focus:outline-none">
                        <div class="relative w-14 h-14 rounded-2xl <?= $habit['bg'] ?> <?= $habit['text'] ?> flex items-center justify-center shadow-md shadow-slate-200 group-hover:scale-105 group-active:scale-95 transition-all duration-150">
                            <i class="fa-solid <?= $habit['icon'] ?> text-2xl"></i>
                            <?php if (!empty($habit['badge'])): ?>
                                <span class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full border-2 border-white"></span>
                            <?php endif; ?>
                        </div>
                        <span class="habit-title text-[11px] font-bold text-slate-700 mt-2 leading-tight group-hover:text-emerald-700 transition">
                            <?= htmlspecialchars($habit['title']) ?>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- CARD INSPIRASI MODERASI / MOTIVASI -->
            <div class="mt-8 bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200 rounded-2xl p-4 flex items-center gap-3 shadow-sm">
                <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-md">
                    <i class="fa-solid fa-quote-left text-sm"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-800">Kebiasaan Hari Ini, Karakter Masa Depan!</p>
                    <p class="text-[10px] text-slate-600 mt-0.5">Lakukan 7 kebiasaan baik ini setiap hari secara konsisten.</p>
                </div>
            </div>
        </div>

        <!-- FOOTER / BOTTOM NAVIGATION BAR -->
        <div class="fixed bottom-0 left-0 right-0 max-w-md mx-auto bg-white border-t border-slate-200 py-2 px-6 flex justify-around items-center z-30">
            <a href="dashboard.php" class="flex flex-col items-center text-emerald-700 font-bold">
                <i class="fa-solid fa-house text-lg"></i>
                <span class="text-[10px] mt-0.5">Beranda</span>
            </a>
            <a href="laporan_siswa.php" class="flex flex-col items-center text-slate-400 font-bold hover:text-emerald-700 transition">
                <i class="fa-solid fa-chart-line text-lg"></i>
                <span class="text-[10px] mt-0.5">Laporan</span>
            </a>
            <a href="lengkapi_profil.php" class="flex flex-col items-center text-slate-400 font-bold hover:text-emerald-700 transition">
                <i class="fa-solid fa-user text-lg"></i>
                <span class="text-[10px] mt-0.5">Profil</span>
            </a>
        </div>

    </div>

    <!-- SCRIPT CARI FITUR / SEARCH HABIT -->
    <script>
        const searchInput = document.getElementById('searchHabit');
        const habitCards = document.querySelectorAll('.habit-card');

        searchInput.addEventListener('input', function () {
            const filter = this.value.toLowerCase().trim();

            habitCards.forEach(card => {
                const title = card.querySelector('.habit-title').textContent.toLowerCase();
                if (title.includes(filter)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>