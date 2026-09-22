<?php
session_start();
require_once 'koneksi.php';

// Jika pengguna sudah login, langsung arahkan ke lengkapi profil / dashboard
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'siswa') {
        header("Location: lengkapi_profil.php");
    } else {
        header("Location: dashboard.php");
    }
    exit();
}

$error = '';

// Ambil data kelas dari database untuk pilihan dropdown
try {
    $stmt_kelas = $pdo->query("SELECT id, nama_kelas FROM kelas ORDER BY nama_kelas ASC");
    $daftar_kelas = $stmt_kelas->fetchAll();
} catch (\PDOException $e) {
    $daftar_kelas = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama       = trim($_POST['nama'] ?? '');
    $nisn       = trim($_POST['nisn'] ?? '');
    $id_kelas   = $_POST['id_kelas'] ?? '';
    $tgl_lahir  = $_POST['tgl_lahir'] ?? null;
    $password   = $_POST['password'] ?? '';
    $confirm_pw = $_POST['confirm_password'] ?? '';

    // Validasi input wajib
    if (empty($nama) || empty($nisn) || empty($id_kelas) || empty($password) || empty($confirm_pw)) {
        $error = 'Mohon lengkapi semua kolom yang wajib diisi!';
    } elseif ($password !== $confirm_pw) {
        $error = 'Konfirmasi kata sandi tidak cocok!';
    } elseif (strlen($password) < 6) {
        $error = 'Kata sandi minimal terdiri dari 6 karakter!';
    } else {
        // Cek apakah NISN sudah terdaftar
        $stmt_check = $pdo->prepare("SELECT id FROM siswa WHERE nisn = :nisn LIMIT 1");
        $stmt_check->execute(['nisn' => $nisn]);

        if ($stmt_check->fetch()) {
            $error = 'NISN sudah terdaftar! Silakan login atau periksa NISN Anda.';
        } else {
            // Hash password untuk keamanan
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            try {
                $sql = "INSERT INTO siswa (nama, nisn, id_kelas, tgl_lahir, password) 
                        VALUES (:nama, :nisn, :id_kelas, :tgl_lahir, :password)";
                $stmt_insert = $pdo->prepare($sql);
                $stmt_insert->execute([
                    'nama'      => $nama,
                    'nisn'      => $nisn,
                    'id_kelas'  => $id_kelas,
                    'tgl_lahir' => !empty($tgl_lahir) ? $tgl_lahir : null,
                    'password'  => $hashed_password
                ]);

                // Ambil ID siswa yang baru saja dibuat
                $new_siswa_id = $pdo->lastInsertId();

                // OTOMATIS LOGIN SISWA
                $_SESSION['user_id'] = $new_siswa_id;
                $_SESSION['role']    = 'siswa';
                $_SESSION['nama']    = $nama;

                // LANGSUNG DIALIHKAN KE HALAMAN LENGKAPI PROFIL
                header("Location: lengkapi_profil.php");
                exit();

            } catch (\PDOException $e) {
                $error = 'Gagal mendaftar. Terjadi kesalahan pada sistem.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Pendaftaran Siswa — Tujuh Kebiasaan</title>
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

    <!-- Header Atas (Hijau Kontras) -->
    <div class="bg-emerald-800 text-white pt-8 pb-16 px-4 rounded-b-[2.5rem] shadow-lg relative overflow-hidden">
        <div class="max-w-md mx-auto text-center relative z-10">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-white text-emerald-800 shadow-md mb-2">
                <i class="fa-solid fa-user-plus text-2xl"></i>
            </div>
            <h1 class="text-2xl font-extrabold tracking-tight text-white">
                Pendaftaran Siswa
            </h1>
            <p class="text-xs font-semibold text-emerald-100 mt-1">
                Aplikasi Tujuh Kebiasaan Anak Indonesia Hebat
            </p>
        </div>
    </div>

    <!-- Form Registrasi Utama -->
    <div class="w-full max-w-md mx-auto px-4 -mt-10 mb-auto z-20">
        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-xl shadow-slate-300/60 border border-slate-200">
            
            <!-- Pesan Error -->
            <?php if (!empty($error)): ?>
                <div class="mb-5 bg-red-100 border-l-4 border-red-600 text-red-900 p-3.5 rounded-r-xl text-xs sm:text-sm font-bold flex items-center gap-3">
                    <i class="fa-solid fa-circle-exclamation text-base text-red-600 shrink-0"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST" class="space-y-4">
                
                <!-- Nama Lengkap -->
                <div>
                    <label class="block text-xs font-extrabold text-slate-800 uppercase tracking-wide mb-1.5">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <i class="fa-solid fa-id-card text-sm"></i>
                        </div>
                        <input type="text" name="nama" required placeholder="Masukkan nama lengkap"
                            value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>"
                            class="w-full pl-10 pr-4 py-3 bg-slate-50 border-2 border-slate-300 rounded-xl text-sm font-semibold text-slate-900 focus:outline-none focus:bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-600/10 transition-all">
                    </div>
                </div>

                <!-- NISN -->
                <div>
                    <label class="block text-xs font-extrabold text-slate-800 uppercase tracking-wide mb-1.5">
                        NISN <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <i class="fa-solid fa-hashtag text-sm"></i>
                        </div>
                        <input type="text" name="nisn" required placeholder="Masukkan Nomor NISN"
                            value="<?= htmlspecialchars($_POST['nisn'] ?? '') ?>"
                            class="w-full pl-10 pr-4 py-3 bg-slate-50 border-2 border-slate-300 rounded-xl text-sm font-semibold text-slate-900 focus:outline-none focus:bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-600/10 transition-all">
                    </div>
                </div>

                <!-- Pilih Kelas -->
                <div>
                    <label class="block text-xs font-extrabold text-slate-800 uppercase tracking-wide mb-1.5">
                        Kelas <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <i class="fa-solid fa-school text-sm"></i>
                        </div>
                        <select name="id_kelas" required
                            class="w-full pl-10 pr-8 py-3 bg-slate-50 border-2 border-slate-300 rounded-xl text-sm font-semibold text-slate-900 focus:outline-none focus:bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-600/10 transition-all appearance-none">
                            <option value="">-- Pilih Kelas --</option>
                            <?php foreach ($daftar_kelas as $k): ?>
                                <option value="<?= $k['id'] ?>" <?= (($_POST['id_kelas'] ?? '') == $k['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($k['nama_kelas']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-500">
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>

                <!-- Tanggal Lahir -->
                <div>
                    <label class="block text-xs font-extrabold text-slate-800 uppercase tracking-wide mb-1.5">
                        Tanggal Lahir
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <i class="fa-solid fa-calendar-day text-sm"></i>
                        </div>
                        <input type="date" name="tgl_lahir"
                            value="<?= htmlspecialchars($_POST['tgl_lahir'] ?? '') ?>"
                            class="w-full pl-10 pr-4 py-3 bg-slate-50 border-2 border-slate-300 rounded-xl text-sm font-semibold text-slate-900 focus:outline-none focus:bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-600/10 transition-all">
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-xs font-extrabold text-slate-800 uppercase tracking-wide mb-1.5">
                        Kata Sandi <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <i class="fa-solid fa-lock text-sm"></i>
                        </div>
                        <input type="password" id="regPassword" name="password" required placeholder="Minimal 6 karakter"
                            class="w-full pl-10 pr-12 py-3 bg-slate-50 border-2 border-slate-300 rounded-xl text-sm font-semibold text-slate-900 focus:outline-none focus:bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-600/10 transition-all">
                        <button type="button" onclick="togglePass('regPassword', 'eyeIcon1')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-500 hover:text-emerald-700 min-w-[40px] justify-center">
                            <i class="fa-solid fa-eye text-sm" id="eyeIcon1"></i>
                        </button>
                    </div>
                </div>

                <!-- Konfirmasi Password -->
                <div>
                    <label class="block text-xs font-extrabold text-slate-800 uppercase tracking-wide mb-1.5">
                        Konfirmasi Kata Sandi <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <i class="fa-solid fa-shield-halved text-sm"></i>
                        </div>
                        <input type="password" id="confirmPassword" name="confirm_password" required placeholder="Ulangi kata sandi"
                            class="w-full pl-10 pr-12 py-3 bg-slate-50 border-2 border-slate-300 rounded-xl text-sm font-semibold text-slate-900 focus:outline-none focus:bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-600/10 transition-all">
                        <button type="button" onclick="togglePass('confirmPassword', 'eyeIcon2')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-500 hover:text-emerald-700 min-w-[40px] justify-center">
                            <i class="fa-solid fa-eye text-sm" id="eyeIcon2"></i>
                        </button>
                    </div>
                </div>

                <!-- Tombol Submit -->
                <button type="submit" 
                    class="w-full mt-2 py-3.5 px-6 bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white font-extrabold text-sm rounded-xl shadow-lg shadow-emerald-800/30 transition-all duration-150 active:scale-[0.98]">
                    Lanjut Isi Profil <i class="fa-solid fa-arrow-right ml-2"></i>
                </button>
            </form>

            <!-- Link Kembali ke Login -->
            <div class="mt-6 pt-4 border-t border-slate-200 text-center">
                <p class="text-xs text-slate-600 font-bold">
                    Sudah memiliki akun? 
                    <a href="login.php" class="text-emerald-700 font-extrabold hover:text-emerald-900 underline ml-1">
                        Masuk di Sini
                    </a>
                </p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center py-6">
        <p class="text-xs text-slate-500 font-bold">
            &copy; <?= date('Y') ?> Tujuh Kebiasaan Anak Indonesia Hebat
        </p>
    </footer>

    <!-- Script Peak Password -->
    <script>
        function togglePass(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            const isPassword = input.type === 'password';
            
            input.type = isPassword ? 'text' : 'password';
            icon.classList.toggle('fa-eye', !isPassword);
            icon.classList.toggle('fa-eye-slash', isPassword);
        }
    </script>
</body>
</html>