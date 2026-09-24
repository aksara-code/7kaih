<?php
session_start();
// Atur zona waktu ke WIB agar date() mengambil jam sekarang yang akurat
date_default_timezone_set('Asia/Jakarta');

require_once 'koneksi.php';

// Cek apakah siswa sudah login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'siswa') {
    header("Location: index.php");
    exit();
}

$siswa_id = $_SESSION['user_id'];

// Ambil notifikasi dari session (Pattern PRG) lalu hapus agar hanya muncul sekali
$error   = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);

// Proses Submit Form Log Makan Sehat
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $waktu_mulai = date('Y-m-d H:i:s'); // Otomatis timestamp waktu saat ini (WIB)
    $deskripsi   = trim($_POST['deskripsi'] ?? '');
    $kategori    = 'makan';

    // Validasi input
    if (empty($deskripsi)) {
        $error = 'Menu makanan sehat wajib diisi!';
    } else {
        $foto_name = null;

        // Proses Unggah Foto
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $file_tmp  = $_FILES['foto']['tmp_name'];
            $file_name = $_FILES['foto']['name'];
            $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed   = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($file_ext, $allowed)) {
                $foto_name  = 'makan' . $siswa_id . '_' . time() . '.' . $file_ext;
                $upload_dir = 'uploads/aktivitas/';

                // Buat direktori jika belum ada
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                if (!move_uploaded_file($file_tmp, $upload_dir . $foto_name)) {
                    $error = 'Gagal mengunggah foto makanan.';
                    $foto_name = null;
                }
            } else {
                $error = 'Format foto tidak valid. Gunakan format JPG, PNG, atau WEBP.';
            }
        }

        // Simpan ke Database
        if (empty($error)) {
            try {
                $sql = "INSERT INTO log_aktivitas (id_siswa, kategori, waktu_mulai, waktu_selesai, deskripsi, catatan_tambahan, foto) 
                        VALUES (:id_siswa, :kategori, :waktu_mulai, NULL, :deskripsi, NULL, :foto)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'id_siswa'    => $siswa_id,
                    'kategori'    => $kategori,
                    'waktu_mulai' => $waktu_mulai,
                    'deskripsi'   => $deskripsi,
                    'foto'        => $foto_name
                ]);

                // Simpan pesan sukses ke session dan redirect (PRG Pattern)
                $_SESSION['success'] = 'Catatan makan sehat & bergizi berhasil disimpan!';
                header("Location: makan_sehat.php");
                exit();

            } catch (\PDOException $e) {
                $error = 'Gagal menyimpan catatan: ' . $e->getMessage();
            }
        }
    }
}

// LOGIKA PAGINATION (MAX 5 DATA PER HALAMAN)
$limit = 5;
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

try {
    // 1. Hitung Total Data untuk Halaman
    $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM log_aktivitas WHERE id_siswa = :id_siswa AND kategori = 'makan'");
    $stmt_count->execute(['id_siswa' => $siswa_id]);
    $total_records = $stmt_count->fetchColumn();

    $total_pages = ceil($total_records / $limit);
    if ($total_pages < 1) $total_pages = 1;

    // Pastikan halaman tidak melebihi batas total halaman
    if ($page > $total_pages && $total_records > 0) {
        $page = $total_pages;
        $offset = ($page - 1) * $limit;
    }

    // 2. Ambil 5 Data Terbaru Berdasarkan Limit dan Offset
    $stmt_riwayat = $pdo->prepare("SELECT * FROM log_aktivitas WHERE id_siswa = :id_siswa AND kategori = 'makan' ORDER BY id DESC LIMIT :limit OFFSET :offset");
    $stmt_riwayat->bindValue(':id_siswa', $siswa_id, PDO::PARAM_INT);
    $stmt_riwayat->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt_riwayat->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt_riwayat->execute();
    $riwayat_list = $stmt_riwayat->fetchAll();

} catch (\PDOException $e) {
    $riwayat_list  = [];
    $total_records = 0;
    $total_pages   = 1;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Makan Sehat & Bergizi — 7 Kebiasaan</title>
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
<body class="bg-slate-100 min-h-screen flex flex-col justify-between antialiased text-slate-800 pb-10">

    <!-- Header Atas -->
    <div class="bg-emerald-800 text-white pt-12 pb-16 px-4 rounded-b-[2.5rem] shadow-lg relative overflow-hidden">
        <!-- Pattern Hiasan Tipis -->
        <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-emerald-700/50 rounded-full blur-xl pointer-events-none"></div>
        <div class="absolute -left-10 -top-10 w-40 h-40 bg-emerald-600/30 rounded-full blur-xl pointer-events-none"></div>

        <div class="max-w-xl mx-auto relative z-10 space-y-4">
            <!-- Navigasi Kembali ke Dashboard & Tombol + Baru -->
            <div class="flex items-center justify-between">
                <a href="dashboard.php" class="inline-flex items-center text-xs font-bold bg-emerald-700/60 hover:bg-emerald-700 px-3 py-2 rounded-xl text-emerald-100 transition-all">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Kembali ke Dashboard
                </a>
                
                <!-- Tombol + Baru -->
                <button onclick="toggleModal(true)" type="button" 
                    class="inline-flex items-center gap-1.5 bg-white text-emerald-800 hover:bg-emerald-50 active:bg-emerald-100 text-xs font-extrabold px-3.5 py-2 rounded-xl shadow-md transition-all active:scale-95 cursor-pointer">
                    <i class="fa-solid fa-plus text-xs"></i> Baru
                </button>
            </div>

            <!-- Logo + Judul Layout Horizontal -->
            <div class="flex items-center justify-center gap-3.5 pt-5 text-center">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-white p-1 shadow-md shrink-0 overflow-hidden">
                    <img src="img/logo_makan_sehat.png" alt="Logo Makan Sehat" class="w-full h-full object-contain">
                </div>
                <div class="text-left">
                    <h1 class="text-xl font-extrabold tracking-tight text-white leading-tight">
    Makan Sehat & Bergizi
</h1>
                    <p class="text-xs font-semibold text-emerald-100 mt-0.5">
                        Catat pola makan sehat dan gizi seimbangmu
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Container Utama Halaman -->
    <div class="w-full max-w-xl mx-auto px-4 -mt-8 mb-auto z-20 space-y-3">

        <!-- Pesan Notifikasi Error / Sukses -->
        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border-l-4 border-red-600 text-red-900 p-3.5 rounded-r-xl text-xs font-bold flex items-center gap-2.5 shadow-sm">
                <i class="fa-solid fa-circle-exclamation text-sm text-red-600 shrink-0"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="bg-emerald-100 border-l-4 border-emerald-600 text-emerald-900 p-3.5 rounded-r-xl text-xs font-bold flex items-center gap-2.5 shadow-sm">
                <i class="fa-solid fa-circle-check text-sm text-emerald-600 shrink-0"></i>
                <span><?= htmlspecialchars($success) ?></span>
            </div>
        <?php endif; ?>

        <!-- Section Tampilan Data / Empty State -->
        <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-xl shadow-slate-300/60 border border-slate-200">
            <!-- Judul Riwayat Kegiatan -->
            <div class="mb-4">
                <p class="text-[0.68rem] font-extrabold uppercase tracking-[0.18em] text-slate-500">Riwayat</p>
                <h2 class="text-xl font-extrabold text-slate-800">Makan Sehat &amp; Bergizi</h2>
            </div>

            <?php if (empty($riwayat_list)): ?>
                <!-- Tampilan Jika Siswa Belum Mengisi Data -->
                <div class="text-center py-6 px-3">
                    <div class="w-14 h-14 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-2">
                        <i class="fa-solid fa-utensils text-2xl text-emerald-600"></i>
                    </div>
                    <h3 class="text-xs sm:text-sm font-extrabold text-slate-800 mb-1">Kamu belum mengisi data</h3>
                    <p class="text-[11px] sm:text-xs text-slate-500 font-medium max-w-xs mx-auto leading-relaxed">
                        Belum ada catatan makan sehat & bergizi. Klik tombol <strong>"+ Baru"</strong> di bagian atas untuk menambahkan kegiatan.
                    </p>
                </div>
            <?php else: ?>
                <!-- Tampilan Daftar Riwayat Kegiatan -->
                <div class="space-y-3">
                    <?php foreach ($riwayat_list as $item): ?>
                        <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-2xl flex flex-row items-start justify-between gap-3">
                            
                            <!-- Bagian Kiri: Informasi Teks -->
                            <div class="flex-1 min-w-0 space-y-1">
                                <p class="text-[11px] font-extrabold text-emerald-800 flex items-center gap-1.5">
                                    <i class="fa-solid fa-calendar-day"></i>
                                    <span>
                                        <?= date('d M Y, H:i', strtotime($item['waktu_mulai'])) ?> WIB
                                    </span>
                                </p>
                                
                                <p class="text-xs sm:text-sm font-bold text-slate-800 break-words leading-relaxed">
                                    <?= nl2br(htmlspecialchars($item['deskripsi'])) ?>
                                </p>
                            </div>

                            <!-- Bagian Kanan: Foto Makanan -->
                            <?php if (!empty($item['foto']) && file_exists('uploads/aktivitas/' . $item['foto'])): ?>
                                <div class="shrink-0">
                                    <a href="uploads/aktivitas/<?= htmlspecialchars($item['foto']) ?>" target="_blank" title="Lihat Foto Makanan">
                                        <img src="uploads/aktivitas/<?= htmlspecialchars($item['foto']) ?>" 
                                             alt="Foto Makanan" 
                                             class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl object-cover border border-white shadow-sm hover:scale-105 transition-all duration-200">
                                    </a>
                                </div>
                            <?php endif; ?>

                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- KOMPONEN PAGINATION -->
                <?php if ($total_pages > 1): ?>
                    <div class="mt-5 pt-2">
                        <div class="flex items-center justify-between bg-slate-50 p-2 rounded-xl border border-slate-200/80 shadow-sm">
                            
                            <!-- Tombol Prev -->
                            <?php if ($page > 1): ?>
                                <a href="?page=<?= $page - 1 ?>" 
                                   class="inline-flex items-center gap-1 px-3 py-1.5 bg-white hover:bg-slate-100 text-slate-700 rounded-lg font-bold text-xs shadow-sm border border-slate-200 transition-all active:scale-95">
                                    <i class="fa-solid fa-chevron-left text-[10px]"></i> Prev
                                </a>
                            <?php else: ?>
                                <span class="inline-flex items-center gap-1 px-3 py-1.5 bg-slate-100 text-slate-300 rounded-lg font-bold text-xs cursor-not-allowed">
                                    <i class="fa-solid fa-chevron-left text-[10px]"></i> Prev
                                </span>
                            <?php endif; ?>

                            <!-- Badge Halaman Saat Ini -->
                            <div class="px-3 py-1 bg-slate-200/70 rounded-lg text-[11px] font-extrabold text-slate-700 tracking-wide">
                                Hal <?= $page ?> / <?= $total_pages ?>
                            </div>

                            <!-- Tombol Next -->
                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?= $page + 1 ?>" 
                                   class="inline-flex items-center gap-1 px-3 py-1.5 bg-white hover:bg-slate-100 text-slate-700 rounded-xl font-bold text-xs shadow-sm border border-slate-200 transition-all active:scale-95">
                                    Next <i class="fa-solid fa-chevron-right text-[10px]"></i>
                                </a>
                            <?php else: ?>
                                <span class="inline-flex items-center gap-1 px-3 py-1.5 bg-slate-100 text-slate-300 rounded-lg font-bold text-xs cursor-not-allowed">
                                    Next <i class="fa-solid fa-chevron-right text-[10px]"></i>
                                </span>
                            <?php endif; ?>

                        </div>
                    </div>
                <?php endif; ?>

            <?php endif; ?>
        </div>
    </div>

    <!-- POP-UP MODAL FORM -->
    <div id="modalForm" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all my-6">
            
            <!-- Header Modal -->
            <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-100">
                <h3 class="text-sm font-extrabold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-utensils text-emerald-700"></i> Tambah Catatan Makan Sehat
                </h3>
                <button onclick="toggleModal(false)" type="button" class="text-slate-400 hover:text-slate-600 transition-all p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Body Form Modal -->
            <form action="makan_sehat.php" method="POST" enctype="multipart/form-data" class="p-5 space-y-4 max-h-[75vh] overflow-y-auto">
                
                <!-- Deskripsi Makanan / Menu -->
                <div>
                    <label class="block text-xs font-extrabold text-slate-800 uppercase tracking-wide mb-1.5">
                        Menu / Deskripsi Makanan <span class="text-red-500">*</span>
                    </label>
                    <textarea name="deskripsi" rows="3" required placeholder="Contoh: Makan siang dengan menu gizi seimbang (nasi merah, dada ayam panggang, tumis kangkung, dan buah pisang)..."
                        class="w-full p-3 bg-slate-50 border-2 border-slate-300 rounded-xl text-xs font-semibold text-slate-900 placeholder:text-slate-400 focus:outline-none focus:bg-white focus:border-emerald-600 transition-all"></textarea>
                </div>

                <!-- Unggah Foto Makanan -->
                <div>
                    <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">
                         FOTO KEGIATAN <span class="text-slate-400 font-normal">(Opsional)</span>
                    </label>
                    <div class="flex items-center gap-3 p-2 bg-slate-50/50 border border-slate-200 rounded-2xl">
                        <label class="cursor-pointer bg-emerald-800 hover:bg-emerald-900 active:bg-emerald-950 text-white font-bold text-xs px-4 py-2 rounded-xl transition-all inline-flex items-center shrink-0 shadow-sm">
                            Choose File
                            <input type="file" name="foto" accept="image/*" class="hidden" onchange="updateFileName(this)">
                        </label>
                        <span id="fileNameDisplay" class="text-xs font-semibold text-slate-400 truncate">
                            No file chosen
                        </span>
                    </div>
                </div>

                <!-- Footer / Tombol Aksi Modal -->
                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                    <button onclick="toggleModal(false)" type="button" 
                        class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all">
                        Batal
                    </button>
                    <button type="submit" 
                        class="px-5 py-2.5 bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white font-extrabold text-xs rounded-xl shadow-md transition-all">
                        <i class="fa-solid fa-paper-plane mr-1"></i> Simpan Catatan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center py-5 mt-4">
        <p class="text-xs text-slate-500 font-bold">
            &copy; <?= date('Y') ?> Tujuh Kebiasaan Anak Indonesia Hebat
        </p>
    </footer>

    <!-- Script JavaScript untuk Control Pop-Up Modal & Status File -->
    <script>
        function toggleModal(show) {
            const modal = document.getElementById('modalForm');
            if (show) {
                modal.classList.remove('hidden');
            } else {
                modal.classList.add('hidden');
            }
        }

        function updateFileName(input) {
            const fileNameDisplay = document.getElementById('fileNameDisplay');
            if (input.files && input.files[0]) {
                fileNameDisplay.textContent = input.files[0].name;
                fileNameDisplay.classList.add('text-slate-700');
                fileNameDisplay.classList.remove('text-slate-400');
            } else {
                fileNameDisplay.textContent = 'No file chosen';
                fileNameDisplay.classList.add('text-slate-400');
                fileNameDisplay.classList.remove('text-slate-700');
            }
        }

        // Buka modal secara otomatis jika terdapat error saat mengirim form
        <?php if (!empty($error)): ?>
            toggleModal(true);
        <?php endif; ?>
    </script>
</body>
</html>