<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
require_once 'koneksi.php';

$habitCategories = [
    'bangun' => ['title' => 'Bangun Pagi', 'icon' => 'fa-sun'],
    'ibadah' => ['title' => 'Beribadah', 'icon' => 'fa-hands-praying'],
    'belajar' => ['title' => 'Gemar Belajar', 'icon' => 'fa-book-open-reader'],
    'makan' => ['title' => 'Makan Sehat', 'icon' => 'fa-apple-whole'],
    'olahraga' => ['title' => 'Olahraga', 'icon' => 'fa-person-running'],
    'bermasyarakat' => ['title' => 'Bermasyarakat', 'icon' => 'fa-people-group'],
    'tidur' => ['title' => 'Tidur Cepat', 'icon' => 'fa-moon'],
];
$activitiesByCategory = array_fill_keys(array_keys($habitCategories), []);

// 1. Cek Autentikasi Pengguna
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id   = $_SESSION['user_id'];
$user_role = $_SESSION['role'] ?? 'siswa';

// 2. Penentuan ID Siswa yang akan ditampilkan
$siswa_id = null;

if ($user_role === 'siswa') {
    // Jika siswa, tampilkan data dirinya sendiri
    $siswa_id = $user_id;
} else {
    // Jika guru/admin, ambil dari parameter GET ?id=...
    $siswa_id = isset($_GET['id']) ? intval($_GET['id']) : null;
}

// 3. Ambil daftar semua siswa (Khusus untuk dropdown Guru)
$daftar_siswa = [];
if ($user_role === 'guru') {
    $stmt_list = $pdo->query("SELECT id, nama, nisn FROM siswa ORDER BY nama ASC");
    $daftar_siswa = $stmt_list->fetchAll();
    
    // Jika guru belum memilih siswa, pilih siswa pertama secara otomatis
    if (!$siswa_id && !empty($daftar_siswa)) {
        $siswa_id = $daftar_siswa[0]['id'];
    }
}

// 4. Ambil Data Detail Siswa
$siswa = null;
if ($siswa_id) {
    // Jika ada tabel kelas, kita BISA LEFT JOIN. Di sini menggunakan SELECT standar dengan fallback
    $stmt = $pdo->prepare("
        SELECT s.*, k.nama_kelas 
        FROM siswa s 
        LEFT JOIN kelas k ON s.id_kelas = k.id 
        WHERE s.id = :id 
        LIMIT 1
    ");
    $stmt->execute(['id' => $siswa_id]);
    $siswa = $stmt->fetch();

        if ($siswa) {
            $categoryPlaceholders = implode(', ', array_fill(0, count($habitCategories), '?'));
            $stmt_activities = $pdo->prepare(
                "SELECT kategori, waktu_mulai, deskripsi, catatan_tambahan, foto
                 FROM log_aktivitas
                 WHERE id_siswa = ? AND kategori IN ($categoryPlaceholders)
                 ORDER BY waktu_mulai DESC, id DESC"
            );
            $stmt_activities->execute(array_merge([(int) $siswa_id], array_keys($habitCategories)));

            foreach ($stmt_activities->fetchAll() as $activity) {
                $activitiesByCategory[$activity['kategori']][] = $activity;
            }
        }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Profil & Kebiasaan Siswa — Aplikasi Tujuh Kebiasaan</title>
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

        /* Optimization untuk Fitur Cetak/Print */
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background-color: #ffffff !important;
                color: #000000 !important;
            }
            .print-shadow-none {
                box-shadow: none !important;
                border: 1px solid #e2e8f0 !important;
            }
            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen text-slate-800 flex flex-col justify-between antialiased pb-12">

    <!-- Navbar / Action Top Bar (Tidak ikut tercetak) -->
    <header class="bg-emerald-800 text-white pt-8 pb-16 px-4 rounded-b-[2.5rem] shadow-lg relative overflow-hidden no-print">
        <div class="max-w-5xl mx-auto relative z-10">
            <div class="mb-4 flex items-center justify-between gap-3">
                <a href="dashboard.php" class="inline-flex items-center text-xs font-bold bg-emerald-700/60 hover:bg-emerald-700 px-3 py-2 rounded-xl text-emerald-100 transition-all">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Kembali ke Beranda
                </a>
                <button onclick="window.print()" class="bg-white text-emerald-800 hover:bg-emerald-50 px-4 py-2.5 rounded-xl font-extrabold text-xs sm:text-sm flex items-center gap-2 shadow transition-all">
                    <i class="fa-solid fa-print"></i>
                    <span>Cetak Laporan</span>
                </button>
            </div>
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-white text-emerald-800 shadow-md mb-2">
                    <i class="fa-solid fa-file-lines text-2xl"></i>
                </div>
                <h1 class="text-2xl font-extrabold tracking-tight text-white">Laporan Data Siswa</h1>
                <p class="text-xs font-semibold text-emerald-100 mt-1">Aplikasi Tujuh Kebiasaan Anak Indonesia Hebat</p>
            </div>
        </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 mt-6 w-full mb-auto">

        <!-- Selector Siswa untuk Guru (Tidak ikut tercetak) -->
        <?php if ($user_role === 'guru' && !empty($daftar_siswa)): ?>
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 mb-6 no-print flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <label  for="select_siswa" class="text-xs font-extrabold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-users text-emerald-700"></i> Pilih Siswa:
                </label>
                <select id="select_siswa" onchange="location = this.value;" class="w-full sm:w-80 bg-slate-50 border-2 border-slate-300 rounded-xl px-3 py-2 text-sm font-semibold text-slate-800 focus:outline-none focus:border-emerald-600">
                    <?php foreach ($daftar_siswa as $ds): ?>
                        <option value="laporan_siswa.php?id=<?= $ds['id'] ?>" <?= ($ds['id'] == $siswa_id) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($ds['nama']) ?> (NISN: <?= htmlspecialchars($ds['nisn'] ?? '-') ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>

        <?php if ($siswa): ?>
            <!-- Kartu Laporan Cetak Utama -->
            <div class="bg-white rounded-3xl p-6 sm:p-10 shadow-xl border border-slate-200 print-shadow-none">
                
                <!-- Kop Laporan untuk Hasil Cetak -->
                <div class="border-b-2 border-emerald-800 pb-6 mb-8 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-2xl bg-emerald-800 text-white flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-seedling text-3xl"></i>
                        </div>
                        <div>
                            <h2 class="text-xl sm:text-2xl font-black text-slate-900 uppercase tracking-tight">Laporan Profil Siswa</h2>
                            <p class="text-xs sm:text-sm font-semibold text-emerald-700">Tujuh Kebiasaan Anak Indonesia Hebat</p>
                            <p class="text-xs text-slate-500 font-medium mt-0.5">Tanggal Cetak: <?= date('d F Y') ?></p>
                        </div>
                    </div>
                </div>

                <!-- Section Profil Atas -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-start mb-8">
                    <!-- Foto Siswa -->
                    <div class="flex flex-col items-center justify-center text-center">
                        <div class="w-40 h-40 sm:w-48 sm:h-48 rounded-2xl bg-slate-100 border-4 border-emerald-700/20 overflow-hidden shadow-md flex items-center justify-center relative">
                            <?php if (!empty($siswa['foto']) && file_exists('uploads/' . $siswa['foto'])): ?>
                                <img src="uploads/<?= htmlspecialchars($siswa['foto']) ?>" alt="Foto Profil" class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="text-slate-400 flex flex-col items-center">
                                    <i class="fa-solid fa-user-astronaut text-6xl mb-2 text-emerald-700/40"></i>
                                    <span class="text-xs font-bold">Tanpa Foto</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Informasi Identitas Diri -->
                    <div class="md:col-span-2 space-y-4">
                        <h3 class="text-2xl font-black text-slate-900 border-b border-slate-200 pb-2">
                            <?= htmlspecialchars($siswa['nama']) ?>
                        </h3>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs sm:text-sm">
                            <div class="bg-slate-50 p-3 rounded-xl border border-slate-200">
                                <span class="block text-[10px] font-extrabold text-slate-600 uppercase tracking-wider">NISN</span>
                                <span class="font-bold text-slate-900"><?= htmlspecialchars($siswa['nisn'] ?? '-') ?></span>
                            </div>

                            <div class="bg-slate-50 p-3 rounded-xl border border-slate-200">
                                <span class="block text-[10px] font-extrabold text-slate-600 uppercase tracking-wider">Kelas</span>
                                <span class="font-bold text-slate-900"><?= htmlspecialchars($siswa['nama_kelas'] ?? 'Belum Diatur') ?></span>
                            </div>

                            <div class="bg-slate-50 p-3 rounded-xl border border-slate-200">
                                <span class="block text-[10px] font-extrabold text-slate-600 uppercase tracking-wider">Tanggal Lahir</span>
                                <span class="font-bold text-slate-900">
                                    <?= !empty($siswa['tgl_lahir']) ? date('d-m-Y', strtotime($siswa['tgl_lahir'])) : '-' ?>
                                </span>
                            </div>

                            <div class="bg-slate-50 p-3 rounded-xl border border-slate-200">
                                <span class="block text-[10px] font-extrabold text-slate-600 uppercase tracking-wider">Alamat</span>
                                <span class="font-bold text-slate-900 line-clamp-2"><?= htmlspecialchars($siswa['alamat'] ?? '-') ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section Grid Detail Kebiasaan & Preferensi -->
                <div class="space-y-6">
                    <h4 class="text-base font-extrabold text-emerald-800 uppercase tracking-wider border-b-2 border-emerald-800 pb-2 flex items-center gap-2">
                        <i class="fa-solid fa-list-check"></i> Kebiasaan & Aktivitas Siswa
                    </h4>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php foreach ($habitCategories as $category => $habit): ?>
                            <div class="p-4 bg-emerald-50/50 rounded-2xl border border-emerald-200/80">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-700 text-white flex items-center justify-center text-xs">
                                        <i class="fa-solid <?= htmlspecialchars($habit['icon']) ?>"></i>
                                    </div>
                                    <span class="text-xs font-extrabold text-slate-800 uppercase"><?= htmlspecialchars($habit['title']) ?></span>
                                </div>
                                <div class="space-y-3">
                                    <?php foreach ($activitiesByCategory[$category] as $activity): ?>
                                        <div class="border-t border-emerald-200/80 pt-3 first:border-t-0 first:pt-0">
                                            <?php if (!empty($activity['waktu_mulai'])): ?>
                                                <p class="text-[10px] font-bold text-slate-500"><?= date('d-m-Y H:i', strtotime($activity['waktu_mulai'])) ?> WIB</p>
                                            <?php endif; ?>
                                            <?php if (!empty($activity['deskripsi'])): ?>
                                                <p class="mt-1 text-sm font-semibold text-slate-800 whitespace-pre-line"><?= nl2br(htmlspecialchars($activity['deskripsi'], ENT_QUOTES, 'UTF-8')) ?></p>
                                            <?php endif; ?>
                                            <?php if (!empty($activity['catatan_tambahan'])): ?>
                                                <p class="mt-1 text-xs text-slate-600 whitespace-pre-line"><span class="font-bold">Catatan:</span> <?= nl2br(htmlspecialchars($activity['catatan_tambahan'], ENT_QUOTES, 'UTF-8')) ?></p>
                                            <?php endif; ?>
                                            <?php
                                            $photoName = !empty($activity['foto']) ? basename((string) $activity['foto']) : '';
                                            $photoPath = __DIR__ . '/uploads/aktivitas/' . $photoName;
                                            ?>
                                            <?php if ($photoName !== '' && is_file($photoPath)): ?>
                                                <img src="uploads/aktivitas/<?= rawurlencode($photoName) ?>" alt="Foto <?= htmlspecialchars($habit['title']) ?>" class="mt-2 h-32 w-full rounded-xl object-cover">
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                </div>

                <!-- Kolom Tanda Tangan Cetak (Hanya Muncul di Hasil Print) -->
                <div class="hidden print:grid grid-cols-2 gap-8 mt-16 pt-8 text-center text-xs font-bold text-slate-800">
                    <div>
                        <p>Orang Tua / Wali Siswa</p>
                        <div class="h-20"></div>
                        <p class="border-t border-slate-400 inline-block px-8">( .................................... )</p>
                    </div>
                    <div>
                        <p>Guru Pembimbing</p>
                        <div class="h-20"></div>
                        <p class="border-t border-slate-400 inline-block px-8">( .................................... )</p>
                    </div>
                </div>

            </div>
        <?php else: ?>
            <div class="bg-white rounded-3xl p-8 text-center border border-slate-200 shadow-md">
                <i class="fa-solid fa-user-slash text-5xl text-slate-300 mb-4"></i>
                <h3 class="text-lg font-bold text-slate-800">Data Siswa Tidak Ditemukan</h3>
                <p class="text-xs text-slate-500 mt-1">Silakan pilih siswa lain dari menu drop-down di atas.</p>
            </div>
        <?php endif; ?>

    </main>

    <!-- Footer Page (Hanya Muncul di Layar) -->
    <footer class="text-center py-6 no-print mt-8">
        <p class="text-xs text-slate-500 font-bold">
            &copy; <?= date('Y') ?> Tujuh Kebiasaan Anak Indonesia Hebat
        </p>
    </footer>

</body>
</html>