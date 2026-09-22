<?php
session_start();
require_once 'koneksi.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');
    $password   = $_POST['password'] ?? '';
    $remember   = isset($_POST['remember']);

    if (empty($identifier) || empty($password)) {
        $error = 'Silakan isi NISN/Username dan password!';
    } else {
        // Cek tabel siswa
        $stmt = $pdo->prepare("SELECT * FROM siswa WHERE nisn = :identifier LIMIT 1");
        $stmt->execute(['identifier' => $identifier]);
        $user = $stmt->fetch();
        $role = 'siswa';

        // Jika bukan siswa, cek tabel guru
        if (!$user) {
            $stmt = $pdo->prepare("SELECT * FROM guru WHERE username = :identifier OR nip = :identifier LIMIT 1");
            $stmt->execute(['identifier' => $identifier]);
            $user = $stmt->fetch();
            $role = 'guru';
        }

        if ($user) {
            $password_valid = false;
            
            if (password_verify($password, $user['password'])) {
                $password_valid = true;
            } elseif ($password === $user['password']) {
                $password_valid = true;
            }

            if ($password_valid) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role']    = $role;
                $_SESSION['nama']    = $user['nama'];

                if ($remember) {
                    $token = bin2hex(random_bytes(16));
                    setcookie('remember_me', $token, time() + (86400 * 30), "/");
                }

                header("Location: dashboard.php");
                exit();
            } else {
                $error = 'Password yang dimasukkan salah!';
            }
        } else {
            $error = 'NISN atau Username tidak ditemukan!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Masuk — Aplikasi Tujuh Kebiasaan</title>
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

    <!-- Section Latar Atas Hijau Pekat (Header Mobile App Style) -->
    <div class="bg-emerald-800 text-white pt-10 pb-20 px-4 rounded-b-[2.5rem] shadow-lg relative overflow-hidden">
        <!-- Pattern Hiasan Tipis -->
        <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-emerald-700/50 rounded-full blur-xl pointer-events-none"></div>
        <div class="absolute -left-10 -top-10 w-40 h-40 bg-emerald-600/30 rounded-full blur-xl pointer-events-none"></div>

        <div class="max-w-md mx-auto text-center relative z-10">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-white text-emerald-800 shadow-lg mb-3">
                <i class="fa-solid fa-seedling text-3xl"></i>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                Selamat Datang
            </h1>
            <p class="text-xs sm:text-sm font-semibold text-emerald-100 mt-1">
                Aplikasi Tujuh Kebiasaan Anak Indonesia Hebat
            </p>
        </div>
    </div>

    <!-- Container Form Utama (Menumpuk ke atas header) -->
    <div class="w-full max-w-md mx-auto px-4 -mt-12 mb-auto z-20">
        
        <!-- Kartu Form Login Putih Kontras -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-xl shadow-slate-300/60 border border-slate-200">
            
            <!-- Pesan Error -->
            <?php if (!empty($error)): ?>
                <div class="mb-5 bg-red-100 border-l-4 border-red-600 text-red-900 p-3.5 rounded-r-xl text-xs sm:text-sm font-bold flex items-center gap-3">
                    <i class="fa-solid fa-triangle-exclamation text-base text-red-600 shrink-0"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="space-y-5">
                
                <!-- Field Input Identifier -->
                <div>
                    <label class="block text-xs font-extrabold text-slate-800 uppercase tracking-wide mb-2">
                        NISN / Username / NIP
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <i class="fa-solid fa-user text-base"></i>
                        </div>
                        <input type="text" name="identifier" required placeholder="Masukkan NISN atau Username"
                            value="<?= htmlspecialchars($_POST['identifier'] ?? '') ?>"
                            class="w-full pl-11 pr-4 py-3.5 bg-slate-50 border-2 border-slate-300 rounded-xl text-base sm:text-sm font-semibold text-slate-900 placeholder:text-slate-400 focus:outline-none focus:bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-600/10 transition-all">
                    </div>
                </div>

                <!-- Field Input Password dengan Peak Password -->
                <div>
                    <label class="block text-xs font-extrabold text-slate-800 uppercase tracking-wide mb-2">
                        Kata Sandi
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <i class="fa-solid fa-lock text-base"></i>
                        </div>
                        <input type="password" id="passwordInput" name="password" required placeholder="••••••••"
                            class="w-full pl-11 pr-12 py-3.5 bg-slate-50 border-2 border-slate-300 rounded-xl text-base sm:text-sm font-semibold text-slate-900 placeholder:text-slate-400 focus:outline-none focus:bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-600/10 transition-all">
                        <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-emerald-700 focus:outline-none min-w-[44px] justify-center">
                            <i class="fa-solid fa-eye text-base" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Opsi Ingat Saya & Lupa Password -->
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2.5 cursor-pointer select-none">
                        <input type="checkbox" name="remember" class="w-4 h-4 text-emerald-700 border-2 border-slate-400 rounded focus:ring-emerald-600">
                        <span class="text-xs font-bold text-slate-700">Ingat saya</span>
                    </label>
                    <a href="#" class="text-xs font-bold text-emerald-700 hover:text-emerald-900 underline underline-offset-2">
                        Lupa password?
                    </a>
                </div>

                <!-- Tombol Submit -->
                <button type="submit" 
                    class="w-full py-4 px-6 bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white font-extrabold text-sm sm:text-base rounded-xl shadow-lg shadow-emerald-800/30 transition-all duration-150 active:scale-[0.98]">
                    Masuk Sekarang
                </button>
            </form>

            <!-- Link Registrasi Siswa -->
            <div class="mt-6 pt-5 border-t border-slate-200 text-center">
                <p class="text-xs text-slate-600 font-bold">
                    Belum punya akun? 
                    <a href="register.php" class="text-emerald-700 font-extrabold hover:text-emerald-900 underline ml-1">
                        Daftar sebagai Siswa
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

    <!-- JavaScript Peak Password -->
    <script>
        const passwordInput = document.getElementById('passwordInput');
        const togglePassword = document.getElementById('togglePassword');
        const eyeIcon = document.getElementById('eyeIcon');

        togglePassword.addEventListener('click', function () {
            const isPassword = passwordInput.getAttribute('type') === 'password';
            passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
            
            eyeIcon.classList.toggle('fa-eye', !isPassword);
            eyeIcon.classList.toggle('fa-eye-slash', isPassword);
        });
    </script>
</body>
</html>