<?php
session_start();
require_once 'koneksi.php';

// Cek apakah siswa sudah login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'siswa') {
    header("Location: login.php");
    exit();
}

$siswa_id = $_SESSION['user_id'];
$error    = '';
$success  = '';

// Ambil data siswa saat ini untuk pre-fill form
try {
    $stmt = $pdo->prepare("SELECT s.*, k.nama_kelas FROM siswa s LEFT JOIN kelas k ON s.id_kelas = k.id WHERE s.id = :id LIMIT 1");
    $stmt->execute(['id' => $siswa_id]);
    $siswa = $stmt->fetch();

    if (!$siswa) {
        header("Location: login.php");
        exit();
    }
} catch (\PDOException $e) {
    die("Terjadi kesalahan sistem: " . $e->getMessage());
}

// Proses submit form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $alamat        = trim($_POST['alamat'] ?? '');
    $hobi          = trim($_POST['hobi'] ?? '');
    $cita_cita     = trim($_POST['cita_cita'] ?? '');
    $olahraga      = trim($_POST['olahraga'] ?? '');
    $makanan       = trim($_POST['makanan'] ?? '');
    $buah          = trim($_POST['buah'] ?? '');
    $jam_tidur     = $_POST['jam_tidur'] ?? null;
    $jam_bangun    = $_POST['jam_bangun'] ?? null;
    $mapel         = trim($_POST['mapel'] ?? '');
    $warna         = trim($_POST['warna'] ?? '');
    $keunikan_saya = trim($_POST['keunikan_saya'] ?? '');

    $foto_name = $siswa['foto']; // Default menggunakan foto lama

    // Proses upload foto profil jika ada berkas yang diunggah
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $file_tmp   = $_FILES['foto']['tmp_name'];
        $file_name  = $_FILES['foto']['name'];
        $file_ext   = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed    = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($file_ext, $allowed)) {
            $new_name = 'siswa_' . $siswa_id . '_' . time() . '.' . $file_ext;
            $upload_dir = 'uploads/';

            // Buat folder uploads jika belum ada
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            if (move_uploaded_file($file_tmp, $upload_dir . $new_name)) {
                $foto_name = $new_name;
            } else {
                $error = 'Gagal mengunggah foto profil.';
            }
        } else {
            $error = 'Format foto tidak valid. Gunakan JPG, PNG, atau WEBP.';
        }
    }

    if (empty($error)) {
        try {
            $sql = "UPDATE siswa SET 
                    alamat = :alamat,
                    hobi = :hobi,
                    cita_cita = :cita_cita,
                    olahraga = :olahraga,
                    makanan = :makanan,
                    buah = :buah,
                    jam_tidur = :jam_tidur,
                    jam_bangun = :jam_bangun,
                    mapel = :mapel,
                    warna = :warna,
                    keunikan_saya = :keunikan_saya,
                    foto = :foto
                    WHERE id = :id";

            $stmt_update = $pdo->prepare($sql);
            $stmt_update->execute([
                'alamat'        => $alamat,
                'hobi'          => $hobi,
                'cita_cita'     => $cita_cita,
                'olahraga'      => $olahraga,
                'makanan'       => $makanan,
                'buah'          => $buah,
                'jam_tidur'     => !empty($jam_tidur) ? $jam_tidur : null,
                'jam_bangun'    => !empty($jam_bangun) ? $jam_bangun : null,
                'mapel'         => $mapel,
                'warna'         => $warna,
                'keunikan_saya' => $keunikan_saya,
                'foto'          => $foto_name,
                'id'            => $siswa_id
            ]);

            $success = 'Data profil berhasil diperbarui!';
            
            // Refresh data siswa setelah update
            $stmt->execute(['id' => $siswa_id]);
            $siswa = $stmt->fetch();
        } catch (\PDOException $e) {
            $error = 'Gagal menyimpan perubahan: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Lengkapi Profil — 7 Kebiasaan</title>
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

    <!-- Header Atas (Hijau Kontras) -->
    <div class="bg-emerald-800 text-white pt-8 pb-16 px-4 rounded-b-[2.5rem] shadow-lg relative overflow-hidden">
        <div class="max-w-xl mx-auto text-center relative z-10">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-white text-emerald-800 shadow-md mb-2">
                <i class="fa-solid fa-address-card text-2xl"></i>
            </div>
            <h1 class="text-2xl font-extrabold tracking-tight text-white">
                Lengkapi Profil Saya
            </h1>
            <p class="text-xs font-semibold text-emerald-100 mt-1">
                Aplikasi Tujuh Kebiasaan Anak Indonesia Hebat
            </p>
        </div>
    </div>

    <!-- Container Utama -->
    <div class="w-full max-w-xl mx-auto px-4 -mt-10 mb-auto z-20">
        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-xl shadow-slate-300/60 border border-slate-200">
            
            <!-- Ringkasan Info Dasar (ReadOnly) -->
            <div class="mb-6 bg-emerald-50 border-2 border-emerald-200 rounded-2xl p-4 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-extrabold text-emerald-950"><?= htmlspecialchars($siswa['nama']) ?></h2>
                    <p class="text-xs font-bold text-emerald-700">NISN: <?= htmlspecialchars($siswa['nisn']) ?> | Kelas: <?= htmlspecialchars($siswa['nama_kelas'] ?? '-') ?></p>
                </div>
                <a href="logout.php" class="text-xs font-bold text-red-600 bg-white border border-red-200 px-3 py-1.5 rounded-lg shadow-sm hover:bg-red-50">
                    <i class="fa-solid fa-right-from-bracket mr-1"></i>Keluar
                </a>
            </div>

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

            <form action="lengkapi_profil.php" method="POST" enctype="multipart/form-data" class="space-y-5">
                
                <!-- Foto Profil -->
                <div class="text-center pb-2">
                    <label class="block text-xs font-extrabold text-slate-800 uppercase tracking-wide mb-3">
                        Foto Profil
                    </label>
                    <div class="flex flex-col items-center gap-3">
                        <div class="w-24 h-24 rounded-full overflow-hidden border-4 border-emerald-600 shadow-md bg-slate-100 flex items-center justify-center">
                            <?php if (!empty($siswa['foto']) && file_exists('uploads/' . $siswa['foto'])): ?>
                                <img id="previewFoto" src="uploads/<?= htmlspecialchars($siswa['foto']) ?>" alt="Foto Profil" class="w-full h-full object-cover">
                            <?php else: ?>
                                <img id="previewFoto" src="https://via.placeholder.com/150?text=Siswa" alt="Foto Profil" class="w-full h-full object-cover">
                            <?php endif; ?>
                        </div>
                        <label class="cursor-pointer bg-slate-100 border-2 border-slate-300 hover:bg-slate-200 text-slate-800 px-4 py-2 rounded-xl text-xs font-extrabold transition-all">
                            <i class="fa-solid fa-camera mr-1"></i> Pilih Foto
                            <input type="file" name="foto" accept="image/*" class="hidden" onchange="previewImage(event)">
                        </label>
                    </div>
                </div>

                <hr class="border-slate-200">

                <!-- Alamat Tempat Tinggal -->
                <div>
                    <label class="block text-xs font-extrabold text-slate-800 uppercase tracking-wide mb-1.5">
                        Alamat Tempat Tinggal
                    </label>
                    <textarea name="alamat" rows="2" placeholder="Masukkan alamat rumah lengkap"
                        class="w-full p-3 bg-slate-50 border-2 border-slate-300 rounded-xl text-sm font-semibold text-slate-900 focus:outline-none focus:bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-600/10 transition-all"><?= htmlspecialchars($siswa['alamat'] ?? '') ?></textarea>
                </div>

                <!-- Hobi & Cita-cita -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-extrabold text-slate-800 uppercase tracking-wide mb-1.5">
                            Hobi
                        </label>
                        <input type="text" name="hobi" placeholder="Contoh: Membaca, Melukis"
                            value="<?= htmlspecialchars($siswa['hobi'] ?? '') ?>"
                            class="w-full px-3.5 py-3 bg-slate-50 border-2 border-slate-300 rounded-xl text-sm font-semibold text-slate-900 focus:outline-none focus:bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-600/10 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-extrabold text-slate-800 uppercase tracking-wide mb-1.5">
                            Cita-cita
                        </label>
                        <input type="text" name="cita_cita" placeholder="Contoh: Dokter, Guru"
                            value="<?= htmlspecialchars($siswa['cita_cita'] ?? '') ?>"
                            class="w-full px-3.5 py-3 bg-slate-50 border-2 border-slate-300 rounded-xl text-sm font-semibold text-slate-900 focus:outline-none focus:bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-600/10 transition-all">
                    </div>
                </div>

                <!-- Olahraga & Kebiasaan Makanan -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-extrabold text-slate-800 uppercase tracking-wide mb-1.5">
                            Olahraga
                        </label>
                        <input type="text" name="olahraga" placeholder="Olahraga favorit"
                            value="<?= htmlspecialchars($siswa['olahraga'] ?? '') ?>"
                            class="w-full px-3.5 py-3 bg-slate-50 border-2 border-slate-300 rounded-xl text-sm font-semibold text-slate-900 focus:outline-none focus:bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-600/10 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-extrabold text-slate-800 uppercase tracking-wide mb-1.5">
                            Makanan Favorit
                        </label>
                        <input type="text" name="makanan" placeholder="Makanan favorit"
                            value="<?= htmlspecialchars($siswa['makanan'] ?? '') ?>"
                            class="w-full px-3.5 py-3 bg-slate-50 border-2 border-slate-300 rounded-xl text-sm font-semibold text-slate-900 focus:outline-none focus:bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-600/10 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-extrabold text-slate-800 uppercase tracking-wide mb-1.5">
                            Buah Favorit
                        </label>
                        <input type="text" name="buah" placeholder="Buah favorit"
                            value="<?= htmlspecialchars($siswa['buah'] ?? '') ?>"
                            class="w-full px-3.5 py-3 bg-slate-50 border-2 border-slate-300 rounded-xl text-sm font-semibold text-slate-900 focus:outline-none focus:bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-600/10 transition-all">
                    </div>
                </div>

                <!-- Jam Tidur & Jam Bangun (Waktu 7 Kebiasaan) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-emerald-50/50 p-4 rounded-2xl border border-emerald-100">
                    <div>
                        <label class="block text-xs font-extrabold text-emerald-900 uppercase tracking-wide mb-1.5">
                            <i class="fa-solid fa-moon text-indigo-600 mr-1"></i> Jam Biasa Tidur Malam
                        </label>
                        <input type="time" name="jam_tidur"
                            value="<?= htmlspecialchars($siswa['jam_tidur'] ?? '') ?>"
                            class="w-full px-3.5 py-3 bg-white border-2 border-slate-300 rounded-xl text-sm font-semibold text-slate-900 focus:outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-600/10 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-extrabold text-emerald-900 uppercase tracking-wide mb-1.5">
                            <i class="fa-solid fa-sun text-amber-500 mr-1"></i> Jam Biasa Bangun Pagi
                        </label>
                        <input type="time" name="jam_bangun"
                            value="<?= htmlspecialchars($siswa['jam_bangun'] ?? '') ?>"
                            class="w-full px-3.5 py-3 bg-white border-2 border-slate-300 rounded-xl text-sm font-semibold text-slate-900 focus:outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-600/10 transition-all">
                    </div>
                </div>

                <!-- Mata Pelajaran Favorit & Warna Favorit -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-extrabold text-slate-800 uppercase tracking-wide mb-1.5">
                            Mapel Favorit
                        </label>
                        <input type="text" name="mapel" placeholder="Pelajaran disukai"
                            value="<?= htmlspecialchars($siswa['mapel'] ?? '') ?>"
                            class="w-full px-3.5 py-3 bg-slate-50 border-2 border-slate-300 rounded-xl text-sm font-semibold text-slate-900 focus:outline-none focus:bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-600/10 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-extrabold text-slate-800 uppercase tracking-wide mb-1.5">
                            Warna Favorit
                        </label>
                        <input type="text" name="warna" placeholder="Warna yang disukai"
                            value="<?= htmlspecialchars($siswa['warna'] ?? '') ?>"
                            class="w-full px-3.5 py-3 bg-slate-50 border-2 border-slate-300 rounded-xl text-sm font-semibold text-slate-900 focus:outline-none focus:bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-600/10 transition-all">
                    </div>
                </div>

                <!-- Keunikan Saya -->
                <div>
                    <label class="block text-xs font-extrabold text-slate-800 uppercase tracking-wide mb-1.5">
                        Keunikan Saya
                    </label>
                    <textarea name="keunikan_saya" rows="3" placeholder="Ceritakan keunikan atau kelebihan khusus yang kamu miliki..."
                        class="w-full p-3 bg-slate-50 border-2 border-slate-300 rounded-xl text-sm font-semibold text-slate-900 focus:outline-none focus:bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-600/10 transition-all"><?= htmlspecialchars($siswa['keunikan_saya'] ?? '') ?></textarea>
                </div>

                <!-- Tombol Submit -->
                <button type="submit" 
                    class="w-full py-4 px-6 bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white font-extrabold text-sm sm:text-base rounded-xl shadow-lg shadow-emerald-800/30 transition-all duration-150 active:scale-[0.98]">
                    Simpan Perubahan
                </button>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center py-6 mt-4">
        <p class="text-xs text-slate-500 font-bold">
            &copy; <?= date('Y') ?> Tujuh Kebiasaan Anak Indonesia Hebat
        </p>
    </footer>

    <!-- Script Preview Gambar -->
    <script>
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function() {
                const output = document.getElementById('previewFoto');
                output.src = reader.result;
            }
            if(event.target.files[0]) {
                reader.readAsDataURL(event.target.files[0]);
            }
        }
    </script>
</body>
</html>