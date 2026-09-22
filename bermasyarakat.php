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

// Ambil Riwayat Aktivitas Bermasyarakat
try {
    $stmt_riwayat = $pdo->prepare("SELECT * FROM log_aktivitas WHERE id_siswa = :id_siswa AND kategori = 'bermasyarakat' ORDER BY waktu_mulai DESC");
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

    <!-- Header Atas -->
    <div class="bg-emerald-800 text-white pt-8 pb-20 px-4 rounded-b-[2.5rem] shadow-lg relative overflow-hidden">
        <!-- Pattern Hiasan Tipis -->
        <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-emerald-700/50 rounded-full blur-xl pointer-events-none"></div>
        <div class="absolute -left-10 -top-10 w-40 h-40 bg-emerald-600/30 rounded-full blur-xl pointer-events-none"></div>

        <div class="max-w-xl mx-auto relative z-10">
            <!-- Navigasi Kembali ke Dashboard & Tombol + Baru -->
            <div class="flex items-center justify-between mb-4">
                <a href="dashboard.php" class="inline-flex items-center text-xs font-bold bg-emerald-700/60 hover:bg-emerald-700 px-3 py-2 rounded-xl text-emerald-100 transition-all">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Kembali ke Dashboard
                </a>
                
                <!-- Tombol + Baru Mengarahkan ke Pop Up Form -->
                <button onclick="toggleModal(true)" type="button" 
                    class="inline-flex items-center gap-1.5 bg-white text-emerald-800 hover:bg-emerald-50 active:bg-emerald-100 text-xs font-extrabold px-3.5 py-2 rounded-xl shadow-md transition-all active:scale-95 cursor-pointer">
                    <i class="fa-solid fa-plus text-xs"></i> Baru
                </button>
            </div>

            <div class="text-center">
                <!-- Logo Header -->
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-white p-1 shadow-md mb-2 overflow-hidden">
                    <img src="logo_bermasyarakat.png" alt="Logo Bermasyarakat" class="w-full h-full object-contain">
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

    <!-- Container Utama Halaman -->
    <div class="w-full max-w-xl mx-auto px-4 -mt-10 mb-auto z-20 space-y-4">

        <!-- Pesan Notifikasi Error / Sukses -->
        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border-l-4 border-red-600 text-red-900 p-3.5 rounded-r-xl text-xs sm:text-sm font-bold flex items-center gap-3 shadow-sm">
                <i class="fa-solid fa-circle-exclamation text-base text-red-600 shrink-0"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="bg-emerald-100 border-l-4 border-emerald-600 text-emerald-900 p-3.5 rounded-r-xl text-xs sm:text-sm font-bold flex items-center gap-3 shadow-sm">
                <i class="fa-solid fa-circle-check text-base text-emerald-600 shrink-0"></i>
                <span><?= htmlspecialchars($success) ?></span>
            </div>
        <?php endif; ?>

        <!-- Section Tampilan Data / Empty State -->
        <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-xl shadow-slate-300/60 border border-slate-200">
            <?php if (empty($riwayat_list)): ?>
                <!-- Tampilan Jika Siswa Belum Mengisi Data -->
                <div class="text-center py-10 px-4">
                    <div class="w-20 h-20 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fa-solid fa-folder-open text-3xl text-emerald-600"></i>
                    </div>
                    <h3 class="text-sm font-extrabold text-slate-800 mb-1">Kamu belum mengisi data</h3>
                    <p class="text-xs text-slate-500 font-semibold max-w-xs mx-auto">
                        Belum ada catatan kegiatan bermasyarakat. Klik tombol <strong>"+ Baru"</strong> di bagian atas untuk menambahkan kegiatan.
                    </p>
                </div>
            <?php else: ?>
                <!-- Tampilan Daftar Riwayat Kegiatan (Kalimat di Kiri, Foto di Kanan) -->
                <div class="space-y-4">
                    <?php foreach ($riwayat_list as $item): ?>
                        <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl flex flex-row items-start justify-between gap-4">
                            
                            <!-- Bagian Kiri: Informasi Teks & Kalimat Memanjang ke Bawah -->
                            <div class="flex-1 min-w-0 space-y-1.5">
                                <p class="text-xs font-extrabold text-emerald-800 flex items-center gap-1.5">
                                    <i class="fa-solid fa-calendar-day"></i>
                                    <span>
                                        <?= date('d M Y, H:i', strtotime($item['waktu_mulai'])) ?>
                                        <?php if ($item['waktu_selesai']): ?>
                                            — <?= date('H:i', strtotime($item['waktu_selesai'])) ?>
                                        <?php endif; ?>
                                    </span>
                                </p>
                                
                                <p class="text-xs sm:text-sm font-bold text-slate-800 break-words leading-relaxed">
                                    <?= nl2br(htmlspecialchars($item['deskripsi'])) ?>
                                </p>

                                <?php if (!empty($item['catatan_tambahan'])): ?>
                                    <div class="pt-1">
                                        <p class="text-[11px] sm:text-xs text-slate-500 font-semibold italic break-words bg-slate-100/80 p-2 rounded-lg border-l-2 border-emerald-600">
                                            "<?= nl2br(htmlspecialchars($item['catatan_tambahan'])) ?>"
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Bagian Kanan: Foto Kegiatan -->
                            <?php if (!empty($item['foto']) && file_exists('uploads/aktivitas/' . $item['foto'])): ?>
                                <div class="shrink-0">
                                    <a href="uploads/aktivitas/<?= htmlspecialchars($item['foto']) ?>" target="_blank" title="Lihat Foto Dokumentasi">
                                        <img src="uploads/aktivitas/<?= htmlspecialchars($item['foto']) ?>" 
                                             alt="Foto Kegiatan" 
                                             class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl object-cover border-2 border-white shadow-md hover:scale-105 hover:shadow-lg transition-all duration-200">
                                    </a>
                                </div>
                            <?php endif; ?>

                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- POP-UP MODAL FORM -->
    <div id="modalForm" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all my-8">
            
            <!-- Header Modal -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                <h3 class="text-base font-extrabold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square text-emerald-700"></i> Tambah Kegiatan Bermasyarakat
                </h3>
                <button onclick="toggleModal(false)" type="button" class="text-slate-400 hover:text-slate-600 transition-all p-1">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>

            <!-- Body Form Modal -->
            <form action="bermasyarakat.php" method="POST" enctype="multipart/form-data" class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                
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

                <!-- Footer / Tombol Aksi Modal -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button onclick="toggleModal(false)" type="button" 
                        class="px-5 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all">
                        Batal
                    </button>
                    <button type="submit" 
                        class="px-6 py-3 bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white font-extrabold text-xs rounded-xl shadow-lg shadow-emerald-800/30 transition-all">
                        <i class="fa-solid fa-paper-plane mr-1.5"></i> Simpan Catatan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center py-6 mt-6">
        <p class="text-xs text-slate-500 font-bold">
            &copy; <?= date('Y') ?> Tujuh Kebiasaan Anak Indonesia Hebat
        </p>
    </footer>

    <!-- Script JavaScript untuk Control Pop-Up Modal & Preview Foto -->
    <script>
        function toggleModal(show) {
            const modal = document.getElementById('modalForm');
            if (show) {
                modal.classList.remove('hidden');
            } else {
                modal.classList.add('hidden');
            }
        }

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

        // Buka modal secara otomatis jika terdapat error saat mengirim form
        <?php if (!empty($error)): ?>
            toggleModal(true);
        <?php endif; ?>
    </script>
</body>
</html>