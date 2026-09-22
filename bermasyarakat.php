<?php
session_start();
require_once 'koneksi.php';

// Cek apakah siswa sudah login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'siswa') {
    header("Location: index.php");
    exit();
}

$siswa_id = $_SESSION['user_id'];
$error    = '';
$success  = '';

// Proses Submit Form Log Bermasyarakat
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $waktu_mulai      = $_POST['waktu_mulai'] ?? '';
    $waktu_selesai    = !empty($_POST['waktu_selesai']) ? $_POST['waktu_selesai'] : null;
    $deskripsi        = trim($_POST['deskripsi'] ?? '');
    $catatan_tambahan = trim($_POST['catatan_tambahan'] ?? '');
    $kategori         = 'bermasyarakat';

    // Validasi input
    if (empty($waktu_mulai)) {
        $error = 'Waktu mulai kegiatan wajib diisi!';
    } elseif (empty($deskripsi)) {
        $error = 'Deskripsi kegiatan bermasyarakat wajib diisi!';
    } else {
        $foto_name = null;

        // Proses Unggah Foto
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $file_tmp  = $_FILES['foto']['tmp_name'];
            $file_name = $_FILES['foto']['name'];
            $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed   = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($file_ext, $allowed)) {
                $foto_name  = 'masyarakat_' . $siswa_id . '_' . time() . '.' . $file_ext;
                $upload_dir = 'uploads/aktivitas/';

                // Buat direktori jika belum ada
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                if (!move_uploaded_file($file_tmp, $upload_dir . $foto_name)) {
                    $error = 'Gagal mengunggah foto kegiatan.';
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
                        VALUES (:id_siswa, :kategori, :waktu_mulai, :waktu_selesai, :deskripsi, :catatan_tambahan, :foto)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'id_siswa'         => $siswa_id,
                    'kategori'         => $kategori,
                    'waktu_mulai'      => $waktu_mulai,
                    'waktu_selesai'    => $waktu_selesai,
                    'deskripsi'        => $deskripsi,
                    'catatan_tambahan' => $catatan_tambahan,
                    'foto'             => $foto_name
                ]);

                $success = 'Catatan kegiatan bermasyarakat berhasil disimpan!';
            } catch (\PDOException $e) {
                $error = 'Gagal menyimpan catatan: ' . $e->getMessage();
            }
        }
    }
}

// Ambil 5 Riwayat Aktivitas Bermasyarakat Terakhir
try {
    $stmt_riwayat = $pdo->prepare("SELECT * FROM log_aktivitas WHERE id_siswa = :id_siswa AND kategori = 'bermasyarakat' ORDER BY waktu_mulai DESC LIMIT 5");
    $stmt_riwayat->execute(['id_siswa' => $siswa_id]);
    $riwayat_list = $stmt_riwayat->fetchAll();
} catch (\PDOException $e) {
    $riwayat_list = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Kegiatan Bermasyarakat — 7 Kebiasaan</title>
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

    <!-- Header Atas (Hijau Emerald Kontras) -->
    <div class="bg-emerald-800 text-white pt-8 pb-20 px-4 rounded-b-[2.5rem] shadow-lg relative overflow-hidden">
        <!-- Pattern Hiasan Tipis -->
        <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-emerald-700/50 rounded-full blur-xl pointer-events-none"></div>
        <div class="absolute -left-10 -top-10 w-40 h-40 bg-emerald-600/30 rounded-full blur-xl pointer-events-none"></div>

        <div class="max-w-xl mx-auto relative z-10">
            <!-- Navigasi Kembali ke Dashboard -->
            <div class="flex items-center justify-between mb-4">
                <a href="dashboard.php" class="inline-flex items-center text-xs font-bold bg-emerald-700/60 hover:bg-emerald-700 px-3 py-2 rounded-xl text-emerald-100 transition-all">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Kembali ke Dashboard
                </a>
                <span class="text-xs font-bold bg-emerald-900/60 px-3 py-1.5 rounded-lg text-emerald-200">
                    Siswa
                </span>
            </div>

            <div class="text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-white text-emerald-800 shadow-md mb-2">
                    <i class="fa-solid fa-people-hold text-3xl"></i>
                </div>
                <h1 class="text-2xl font-extrabold tracking-tight text-white">
                    Kegiatan Bermasyarakat
                </h1>
                <p class="text-xs font-semibold text-emerald-100 mt-1">
                    Catat aksi sosial dan kepedulian lingkunganmu hari ini
                </p>
            </div>
        </div>
    </div>

    <!-- Container Utama Form -->
    <div class="w-full max-w-xl mx-auto px-4 -mt-12 mb-auto z-20">
        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-xl shadow-slate-300/60 border border-slate-200">
            
            <!-- Pesan Notifikasi Error / Sukses -->
            <?php if (!empty($error)): ?>
                <div class="mb-5 bg-red-100 border-l-4 border-red-600 text-red-900 p-3.5 rounded-r-xl text-xs sm:text-sm font-bold flex items-center gap-3">
                    <i class="fa-solid fa-circle-exclamation text-base text-red-600 shrink-0"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="mb-5 bg-emerald-100 border-l-4 border-emerald-600 text-emerald-900 p-3.5 rounded-r-xl text-xs sm:text-sm font-bold flex items-center gap-3">
                    <i class="fa-solid fa-circle-check text-base text-emerald-600 shrink-0"></i>
                    <span><?= htmlspecialchars($success) ?></span>
                </div>
            <?php endif; ?>

            <form action="bermasyarakat.php" method="POST" enctype="multipart/form-data" class="space-y-5">
                
                <!-- Waktu Mulai & Selesai -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-extrabold text-slate-800 uppercase tracking-wide mb-1.5">
                            Waktu Mulai <span class="text-red-500">*</span>
                        </label>
                        <input type="datetime-local" name="waktu_mulai" required
                            value="<?= date('Y-m-d\TH:i') ?>"
                            class="w-full px-3.5 py-3 bg-slate-50 border-2 border-slate-300 rounded-xl text-sm font-semibold text-slate-900 focus:outline-none focus:bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-600/10 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-extrabold text-slate-800 uppercase tracking-wide mb-1.5">
                            Waktu Selesai <span class="text-slate-400 font-normal">(Opsional)</span>
                        </label>
                        <input type="datetime-local" name="waktu_selesai"
                            class="w-full px-3.5 py-3 bg-slate-50 border-2 border-slate-300 rounded-xl text-sm font-semibold text-slate-900 focus:outline-none focus:bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-600/10 transition-all">
                    </div>
                </div>

                <!-- Deskripsi Kegiatan -->
                <div>
                    <label class="block text-xs font-extrabold text-slate-800 uppercase tracking-wide mb-1.5">
                        Deskripsi Kegiatan <span class="text-red-500">*</span>
                    </label>
                    <textarea name="deskripsi" rows="3" required placeholder="Contoh: Ikut kerja bakti membersihkan selokan warga di lingkungan RT 02..."
                        class="w-full p-3.5 bg-slate-50 border-2 border-slate-300 rounded-xl text-sm font-semibold text-slate-900 placeholder:text-slate-400 focus:outline-none focus:bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-600/10 transition-all"></textarea>
                </div>

                <!-- Catatan Tambahan -->
                <div>
                    <label class="block text-xs font-extrabold text-slate-800 uppercase tracking-wide mb-1.5">
                        Catatan Tambahan <span class="text-slate-400 font-normal">(Opsional)</span>
                    </label>
                    <textarea name="catatan_tambahan" rows="2" placeholder="Contoh: Kegiatan dilakukan bersama tetangga, dipimpin oleh Pak RT..."
                        class="w-full p-3.5 bg-slate-50 border-2 border-slate-300 rounded-xl text-sm font-semibold text-slate-900 placeholder:text-slate-400 focus:outline-none focus:bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-600/10 transition-all"></textarea>
                </div>

                <!-- Unggah Foto Kegiatan -->
                <div>
                    <label class="block text-xs font-extrabold text-slate-800 uppercase tracking-wide mb-1.5">
                        Unggah Foto Dokumentasi <span class="text-slate-400 font-normal">(Opsional)</span>
                    </label>
                    <div class="flex items-center justify-center w-full">
                        <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-slate-300 border-dashed rounded-2xl cursor-pointer bg-slate-50 hover:bg-slate-100 transition-all relative overflow-hidden">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6" id="uploadPlaceholder">
                                <i class="fa-solid fa-cloud-arrow-up text-2xl text-emerald-700 mb-1"></i>
                                <p class="text-xs font-bold text-slate-700">Klik untuk upload foto</p>
                                <p class="text-[10px] font-semibold text-slate-500">PNG, JPG, JPEG, atau WEBP</p>
                            </div>
                            <img id="previewFotoAktivitas" class="hidden absolute inset-0 w-full h-full object-cover">
                            <input type="file" name="foto" accept="image/*" class="hidden" onchange="previewAktivitasFoto(event)">
                        </label>
                    </div>
                </div>

                <!-- Tombol Submit -->
                <button type="submit" 
                    class="w-full py-4 px-6 bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white font-extrabold text-sm sm:text-base rounded-xl shadow-lg shadow-emerald-800/30 transition-all duration-150 active:scale-[0.98]">
                    <i class="fa-solid fa-paper-plane mr-2"></i> Simpan Catatan Kegiatan
                </button>
            </form>
        </div>

        <!-- Section Riwayat Kegiatan Bermasyarakat -->
        <div class="mt-8 bg-white rounded-3xl p-6 shadow-lg border border-slate-200">
            <h2 class="text-base font-extrabold text-slate-800 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-emerald-700"></i> Riwayat Kegiatan Terakhir
            </h2>

            <?php if (empty($riwayat_list)): ?>
                <div class="text-center py-6 text-slate-500">
                    <i class="fa-solid fa-folder-open text-3xl mb-2 text-slate-300"></i>
                    <p class="text-xs font-bold">Belum ada riwayat kegiatan bermasyarakat.</p>
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($riwayat_list as $item): ?>
                        <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                            <div class="space-y-1">
                                <p class="text-xs font-extrabold text-emerald-800">
                                    <i class="fa-solid fa-calendar-day mr-1"></i>
                                    <?= date('d M Y, H:i', strtotime($item['waktu_mulai'])) ?>
                                    <?php if ($item['waktu_selesai']): ?>
                                        — <?= date('H:i', strtotime($item['waktu_selesai'])) ?>
                                    <?php endif; ?>
                                </p>
                                <p class="text-sm font-bold text-slate-800 line-clamp-2">
                                    <?= htmlspecialchars($item['deskripsi']) ?>
                                </p>
                                <?php if (!empty($item['catatan_tambahan'])): ?>
                                    <p class="text-xs text-slate-500 font-semibold italic">
                                        "<?= htmlspecialchars($item['catatan_tambahan']) ?>"
                                    </p>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($item['foto']) && file_exists('uploads/aktivitas/' . $item['foto'])): ?>
                                <a href="uploads/aktivitas/<?= htmlspecialchars($item['foto']) ?>" target="_blank" class="shrink-0">
                                    <img src="uploads/aktivitas/<?= htmlspecialchars($item['foto']) ?>" class="w-12 h-12 rounded-xl object-cover border border-slate-300 shadow-sm hover:scale-105 transition-all">
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center py-6 mt-6">
        <p class="text-xs text-slate-500 font-bold">
            &copy; <?= date('Y') ?> Tujuh Kebiasaan Anak Indonesia Hebat
        </p>
    </footer>

    <!-- Script Preview Gambar Upload -->
    <script>
        function previewAktivitasFoto(event) {
            const reader = new FileReader();
            const output = document.getElementById('previewFotoAktivitas');
            const placeholder = document.getElementById('uploadPlaceholder');

            reader.onload = function() {
                output.src = reader.result;
                output.classList.remove('hidden');
                placeholder.classList.add('hidden');
            }

            if (event.target.files[0]) {
                reader.readAsDataURL(event.target.files[0]);
            }
        }
    </script>
</body>
</html>