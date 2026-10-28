<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
require_once 'koneksi.php';

// 1. Cek Autentikasi Pengguna
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id   = $_SESSION['user_id'];
$user_role = $_SESSION['role'] ?? 'siswa';
$dashboardUrl = $user_role === 'guru'
    ? (($_SESSION['guru_role'] ?? '') === 'super-user' ? 'admin/dashboard.php' : 'guru/dashboard.php')
    : 'dashboard.php';

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

// 4. Ambil Data Detail Siswa (Foto, Nama, NISN)
$siswa = null;
if ($siswa_id) {
    $stmt = $pdo->prepare("SELECT id, nama, nisn, foto FROM siswa WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $siswa_id]);
    $siswa = $stmt->fetch();
}

// 5. Pengaturan Filter Bulan
$daftar_nama_bulan = [
    1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
];

// Ambil bulan dari parameter GET (format: YYYY-MM), default ke bulan berjalan saat ini
$selected_bulan = $_GET['bulan'] ?? date('Y-m');
if (!preg_match('/^\d{4}-\d{2}$/', $selected_bulan)) {
    $selected_bulan = date('Y-m');
}

list($tahun_pilih, $bulan_pilih) = explode('-', $selected_bulan);
$tahun_pilih = (int) $tahun_pilih;
$bulan_pilih = (int) $bulan_pilih;

// Menentukan rentang tanggal awal dan akhir bulan yang dipilih
$awal_bulan = sprintf('%04d-%02d-01 00:00:00', $tahun_pilih, $bulan_pilih);
$jumlah_hari_dalam_bulan = (int) date('t', strtotime($awal_bulan));

$is_current_month = ($selected_bulan === date('Y-m'));
if ($is_current_month) {
    $akhir_bulan = date('Y-m-d H:i:s');
    $hari_berjalan = (int) date('j');
} else {
    $akhir_bulan = sprintf('%04d-%02d-%02d 23:59:59', $tahun_pilih, $bulan_pilih, $jumlah_hari_dalam_bulan);
    $hari_berjalan = $jumlah_hari_dalam_bulan;
}

$tanggal_laporan = $daftar_nama_bulan[$bulan_pilih] . ' ' . $tahun_pilih;

// Generate Opsi Pilihan Bulan (Misalnya 6 bulan ke belakang)
$opsi_bulan = [];
for ($i = 0; $i < 6; $i++) {
    $time = strtotime("-$i months");
    $val = date('Y-m', $time);
    $label = $daftar_nama_bulan[(int)date('n', $time)] . ' ' . date('Y', $time);
    $opsi_bulan[$val] = $label;
}

// 6. Rekap pengisian kebiasaan siswa berdasarkan bulan yang dipilih
$kategori_kebiasaan = [
    [
        'kode' => 'bangun',
        'nama' => 'Bangun Pagi Tepat Waktu',
        'ikon' => 'fa-sun',
        'kategori' => 'Kedisiplinan',
    ],
    [
        'kode' => 'ibadah',
        'nama' => 'Beribadah Tepat Waktu',
        'ikon' => 'fa-hands-praying',
        'kategori' => 'Spiritual',
    ],
    [
        'kode' => 'belajar',
        'nama' => 'Gemar Belajar',
        'ikon' => 'fa-book-open-reader',
        'kategori' => 'Literasi',
    ],
    [
        'kode' => 'makan',
        'nama' => 'Makan Sehat dan Bergizi',
        'ikon' => 'fa-apple-whole',
        'kategori' => 'Kesehatan',
    ],
    [
        'kode' => 'olahraga',
        'nama' => 'Berolahraga',
        'ikon' => 'fa-person-running',
        'kategori' => 'Kesehatan',
    ],
    [
        'kode' => 'bermasyarakat',
        'nama' => 'Bermasyarakat',
        'ikon' => 'fa-people-group',
        'kategori' => 'Sosial',
    ],
    [
        'kode' => 'tidur',
        'nama' => 'Tidur Cepat',
        'ikon' => 'fa-moon',
        'kategori' => 'Kedisiplinan',
    ],
];

$daftar_kebiasaan = [];

if ($siswa) {
    $total_per_kategori = array_fill_keys(array_column($kategori_kebiasaan, 'kode'), 0);

    foreach ($kategori_kebiasaan as $kb) {
        $kode_Cari = $kb['kode'];
        
        if ($kode_Cari === 'bangun' || $kode_Cari === 'tidur') {
            // 1. BANGUN PAGI & TIDUR CEPAT: Dihitung per hari (unik 1 kali per hari)
            $stmt_rekap = $pdo->prepare(
                "SELECT COUNT(DISTINCT DATE(waktu_mulai)) AS total
                 FROM log_aktivitas
                 WHERE id_siswa = ?
                   AND (kategori = ? OR kategori LIKE ?)
                   AND waktu_mulai >= ?
                   AND waktu_mulai <= ?"
            );
            $pattern_kategori = "%" . $kode_Cari . "%";
            $stmt_rekap->execute([(int) $siswa_id, $kode_Cari, $pattern_kategori, $awal_bulan, $akhir_bulan]);
            
        } elseif ($kode_Cari === 'ibadah') {
            // 2. IBADAH: Dihitung per hari unik, dan mengecualikan yang "Tidak Sesuai Jam"
            $stmt_rekap = $pdo->prepare(
                "SELECT COUNT(DISTINCT DATE(waktu_mulai)) AS total
                 FROM log_aktivitas
                 WHERE id_siswa = ?
                   AND (kategori = ? OR kategori LIKE ?)
                   AND waktu_mulai >= ?
                   AND waktu_mulai <= ?
                   AND deskripsi NOT LIKE '%Tidak Sesuai Jam%'"
            );
            $pattern_kategori = "%" . $kode_Cari . "%";
            $stmt_rekap->execute([(int) $siswa_id, $kode_Cari, $pattern_kategori, $awal_bulan, $akhir_bulan]);
            
        } else {
            // 3. MAKAN, OLAHRAGA, BERMASYARAKAT, BELAJAR: Dihitung akumulatif per kegiatan masuk meskipun berulang di hari yang sama
            $stmt_rekap = $pdo->prepare(
                "SELECT COUNT(*) AS total
                 FROM log_aktivitas
                 WHERE id_siswa = ?
                   AND (kategori = ? OR kategori LIKE ?)
                   AND waktu_mulai >= ?
                   AND waktu_mulai <= ?"
            );
            $pattern_kategori = "%" . $kode_Cari . "%";
            $stmt_rekap->execute([(int) $siswa_id, $kode_Cari, $pattern_kategori, $awal_bulan, $akhir_bulan]);
        }
        
        $res = $stmt_rekap->fetch();
        if ($res) {
            $total_per_kategori[$kode_Cari] = (int) $res['total'];
        }
    }

    // B. CEK STATUS TERAKHIR: Hanya untuk menentukan teks peringatan kuning (apakah aktivitas terakhirnya terlambat/kesiangan)
    $stmt_terlambat = $pdo->prepare(
        "SELECT kategori, deskripsi 
         FROM log_aktivitas 
         WHERE id_siswa = ? 
           AND (kategori IN ('bangun', 'tidur') OR kategori LIKE '%bangun%' OR kategori LIKE '%tidur%')
           AND waktu_mulai >= ? 
           AND waktu_mulai <= ? 
         ORDER BY waktu_mulai DESC, id DESC"
    );
    $stmt_terlambat->execute([(int) $siswa_id, $awal_bulan, $akhir_bulan]);
    
    $status_terlambat_terakhir = [];
    foreach ($stmt_terlambat->fetchAll() as $row) {
        $kat_lower = strtolower($row['kategori']);
        $desc_lower = strtolower($row['deskripsi']);

        if ((strpos($kat_lower, 'bangun') !== false) && !isset($status_terlambat_terakhir['bangun'])) {
            if (strpos($desc_lower, 'kesiangan') !== false || strpos($desc_lower, 'terlambat') !== false) {
                $status_terlambat_terakhir['bangun'] = true;
            } else {
                $status_terlambat_terakhir['bangun'] = false;
            }
        }
        if ((strpos($kat_lower, 'tidur') !== false) && !isset($status_terlambat_terakhir['tidur'])) {
            if (strpos($desc_lower, 'terlambat') !== false) {
                $status_terlambat_terakhir['tidur'] = true;
            } else {
                $status_terlambat_terakhir['tidur'] = false;
            }
        }
    }

    foreach ($kategori_kebiasaan as $kebiasaan) {
        $total = $total_per_kategori[$kebiasaan['kode']];
        $persen = min(100, (int) round(($total / $jumlah_hari_dalam_bulan) * 100));

        if ($total === 0) {
            $status = 'Belum Ada Data';
        } elseif ($persen >= 80) {
            $status = 'Sangat Baik';
        } elseif ($persen >= 60) {
            $status = 'Baik';
        } else {
            $status = 'Perlu Ditingkatkan';
        }

        // Keterangan kuning hanya muncul jika entri paling akhir berstatus terlambat/kesiangan
        $keterangan_waktu = '';
        if ($kebiasaan['kode'] === 'bangun' && !empty($status_terlambat_terakhir['bangun'])) {
            $keterangan_waktu = '<span class="text-amber-600 font-extrabold text-[11px] block mt-1"><i class="fa-solid fa-triangle-exclamation mr-1"></i> terdeteksi bangun kesiangan</span>';
        } elseif ($kebiasaan['kode'] === 'tidur' && !empty($status_terlambat_terakhir['tidur'])) {
            $keterangan_waktu = '<span class="text-amber-600 font-extrabold text-[11px] block mt-1"><i class="fa-solid fa-triangle-exclamation mr-1"></i> terdeteksi tidur terlambat</span>';
        }

        $daftar_kebiasaan[] = array_merge($kebiasaan, [
            'total' => $total,
            'persen' => $persen,
            'status' => $status,
            'keterangan_waktu' => $keterangan_waktu,
        ]);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Profil Siswa — Aplikasi Tujuh Kebiasaan</title>
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

        /* Perbaikan CSS Print agar tidak kosong / blank */
        @media print {
            body, html {
                background-color: #ffffff !important;
                color: #000000 !important;
                width: 100% !important;
                height: auto !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            main {
                margin: 0 !important;
                padding: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
            }
            .print-shadow-none {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .print-break-inside-avoid {
                break-inside: avoid;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen text-slate-800 flex flex-col justify-between antialiased pb-12">

    <!-- Navbar / Header Top Bar (Tidak ikut tercetak) -->
    <header class="bg-emerald-800 text-white pt-8 pb-16 px-4 rounded-b-[2.5rem] shadow-lg relative overflow-hidden no-print">
        <div class="max-w-5xl mx-auto relative z-10">
            <div class="mb-4 flex items-center justify-between gap-3">
                <a href="<?= htmlspecialchars($dashboardUrl) ?>" class="inline-flex items-center text-xs font-bold bg-emerald-700/60 hover:bg-emerald-700 px-3 py-2 rounded-xl text-emerald-100 transition-all">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Kembali ke Beranda
                </a>
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

        <!-- Selector Guru & Filter Bulan (Tidak ikut tercetak) -->
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 mb-6 no-print flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
            
            <!-- Jika Guru, Tampilkan Dropdown Pilih Siswa -->
            <?php if ($user_role === 'guru' && !empty($daftar_siswa)): ?>
                <div class="flex-1 flex flex-col sm:flex-row items-start sm:items-center gap-2">
                    <label for="select_siswa" class="text-xs font-extrabold text-slate-700 uppercase tracking-wider flex items-center gap-1.5 shrink-0">
                        <i class="fa-solid fa-users text-emerald-700"></i> Siswa:
                    </label>
                    <select id="select_siswa" onchange="location = this.value;" class="w-full bg-slate-50 border-2 border-slate-300 rounded-xl px-3 py-2 text-sm font-semibold text-slate-800 focus:outline-none focus:border-emerald-600">
                        <?php foreach ($daftar_siswa as $ds): ?>
                            <option value="laporan_siswa.php?id=<?= $ds['id'] ?>&bulan=<?= $selected_bulan ?>" <?= ($ds['id'] == $siswa_id) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($ds['nama']) ?> (NISN: <?= htmlspecialchars($ds['nisn'] ?? '-') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($siswa): ?>
            <!-- Kartu Laporan Cetak Utama -->
            <div class="bg-white rounded-3xl p-6 sm:p-10 shadow-xl border border-slate-200 print-shadow-none">
                
                <!-- Kop Laporan -->
                <div class="border-b-2 border-emerald-800 pb-6 mb-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-2xl bg-emerald-800 text-white flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-seedling text-3xl"></i>
                        </div>
                        <div>
                            <h2 class="text-xl sm:text-2xl font-black text-slate-900 uppercase tracking-tight">Laporan Rekapitulasi</h2>
                            <p class="text-xs sm:text-sm font-semibold text-emerald-700">Tujuh Kebiasaan Anak Indonesia Hebat</p>
                            <p class="text-xs text-slate-500 font-medium mt-0.5">Periode Bulan: <span class="font-bold text-slate-700"><?= htmlspecialchars($tanggal_laporan) ?></span></p>
                        </div>
                    </div>
                    <div class="text-right sm:text-right text-xs text-slate-400">
                        <span>Dicetak pada: <?= date('d/m/Y H:i') ?> WIB</span>
                    </div>
                </div>

                <!-- Section Profil Atas (Foto, Nama, dan NISN) -->
                <div class="flex items-center gap-4 sm:gap-6 mb-10 bg-slate-50/80 p-4 sm:p-6 rounded-2xl border border-slate-200 print-shadow-none">
                    <!-- Foto Siswa -->
                    <div class="flex shrink-0 items-center justify-center text-center">
                        <div class="w-24 h-24 sm:w-36 sm:h-36 rounded-2xl bg-white border-4 border-emerald-700/20 overflow-hidden shadow-sm flex items-center justify-center relative">
                            <?php if (!empty($siswa['foto']) && file_exists('uploads/' . $siswa['foto'])): ?>
                                <img src="uploads/<?= htmlspecialchars($siswa['foto']) ?>" alt="Foto Profil" class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="text-slate-400 flex flex-col items-center">
                                    <i class="fa-solid fa-user-astronaut text-5xl mb-1 text-emerald-700/40"></i>
                                    <span class="text-[10px] font-bold uppercase tracking-wider">Tanpa Foto</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Informasi Identitas Diri -->
                    <div class="min-w-0 flex-1 space-y-3">
                        <div>
                            <span class="text-[11px] font-extrabold uppercase tracking-wider text-emerald-800">Nama Lengkap Siswa</span>
                            <h3 class="text-xl sm:text-2xl font-black text-slate-900 border-b border-slate-200 pb-1.5">
                                <?= htmlspecialchars($siswa['nama']) ?>
                            </h3>
                        </div>
                        
                        <div>
                            <span class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Nomor Induk Siswa Nasional (NISN)</span>
                            <span class="text-base font-bold text-slate-800"><?= htmlspecialchars($siswa['nisn'] ?? '-') ?></span>
                        </div>
                    </div>
                </div>

                <!-- Dropdown Filter Bulan (Untuk Siswa dan Guru) -->
                <div class="no-print mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 rounded-2xl border border-emerald-100 bg-emerald-50/60 p-4">
                    <label for="select_bulan" class="text-xs font-extrabold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-calendar-days text-emerald-700"></i>
                        <span>Periode Bulan</span>
                    </label>
                    <select id="select_bulan" onchange="location = this.value;" class="w-full sm:w-64 bg-white border border-slate-300 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-800 shadow-sm focus:outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100">
                        <?php foreach ($opsi_bulan as $val => $label): ?>
                            <option value="laporan_siswa.php?id=<?= $siswa_id ?>&bulan=<?= $val ?>" <?= ($val === $selected_bulan) ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Section Ringkasan 7 Kebiasaan Siswa -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between gap-3 border-b border-slate-200 pb-3">
                        <h3 class="text-base sm:text-lg font-black text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-star text-amber-500"></i> Rekapitulasi 7 Kebiasaan Anak
                        </h3>
                        <button onclick="window.print()" class="no-print bg-emerald-700 text-white hover:bg-emerald-800 px-5 py-2.5 rounded-xl font-extrabold text-xs sm:text-sm flex items-center gap-2 shadow-md transition-all active:scale-95 shrink-0">
                            <i class="fa-solid fa-print"></i>
                            <span>Cetak Laporan</span>
                        </button>
                    </div>

                    <!-- Grid Card Kebiasaan -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                        <?php foreach ($daftar_kebiasaan as $kb): ?>
                            <div class="p-4 rounded-2xl border border-slate-200 bg-white shadow-sm flex flex-col justify-between hover:border-emerald-300 transition-all print-break-inside-avoid print-shadow-none">
                                <div class="flex items-start justify-between gap-3 mb-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-800 flex items-center justify-center shrink-0 border border-emerald-100">
                                            <i class="fa-solid <?= $kb['ikon'] ?> text-lg"></i>
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-extrabold text-slate-800 leading-snug">
                                                <?= htmlspecialchars($kb['nama']) ?>
                                            </h4>
                                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                                <?= htmlspecialchars($kb['kategori']) ?>
                                            </span>
                                            <!-- Peringatan kuning dinamis berdasarkan status entri terakhir -->
                                            <?= $kb['keterangan_waktu'] ?? '' ?>
                                        </div>
                                    </div>
                                    <span class="px-2.5 py-1 rounded-lg text-[11px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-200 shrink-0">
                                        <?= htmlspecialchars($kb['status']) ?>
                                    </span>
                                </div>

                                <!-- Progress Bar & Stats -->
                                <div class="space-y-1.5 pt-2 border-t border-slate-100">
                                    <div class="flex justify-between text-xs font-bold">
                                        <span class="text-slate-500">Capaian Rutinitas:</span>
                                        <span class="text-slate-800"><?= $kb['total'] ?>/<?= $jumlah_hari_dalam_bulan ?> hari (<?= $kb['persen'] ?>%)</span>
                                    </div>
                                    <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-emerald-600 rounded-full" style="width: <?= $kb['persen'] ?>%;"></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Kolom Tanda Tangan Cetak (Hanya Muncul saat Diprint) -->
                <div class="hidden print:grid grid-cols-2 gap-8 mt-12 pt-6 text-center text-xs font-bold text-slate-800">
                    <div>
                        <p>Orang Tua / Wali Siswa</p>
                        <div class="h-16"></div>
                        <p class="border-t border-slate-400 inline-block px-8">( .................................... )</p>
                    </div>
                    <div>
                        <p>Guru Pembimbing</p>
                        <div class="h-16"></div>
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

    <!-- Footer Page (Hanya Muncul di Layar Monitor) -->
    <footer class="text-center py-6 no-print mt-8">
        <p class="text-xs text-slate-500 font-bold">
            &copy; <?= date('Y') ?> Tujuh Kebiasaan Anak Indonesia Hebat
        </p>
    </footer>

</body>
</html>