<?php
session_start();

// Hapus semua variabel session
$_SESSION = array();

// Hapus cookie session dari browser jika ada
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Hancurkan session
session_destroy();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - Kebiasaan Anak Indonesia Hebat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f1f5f3;
        }
    </style>
</head>
<body class="flex min-h-screen items-center justify-center bg-[#f1f5f3] p-4 text-slate-800 antialiased selection:bg-emerald-500 selection:text-white">

    <!-- Ambient Blur Background -->
    <div class="fixed -right-16 -top-16 h-64 w-64 rounded-full bg-emerald-400/20 blur-3xl pointer-events-none"></div>
    <div class="fixed -left-16 -bottom-16 h-64 w-64 rounded-full bg-emerald-300/20 blur-3xl pointer-events-none"></div>

    <!-- Card Container -->
    <div class="relative w-full max-w-md rounded-[32px] border border-slate-200/80 bg-white/95 p-8 shadow-[0_20px_40px_rgba(15,23,42,0.08)] backdrop-blur-sm text-center">
        
        <!-- Animated Icon Logout -->
        <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-3xl bg-emerald-50 text-emerald-700 shadow-inner ring-8 ring-emerald-50/50">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
        </div>

        <!-- Main Title & Message -->
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-800">Berhasil Keluar</h1>
        <p class="mt-2 text-xs font-medium text-slate-500 leading-relaxed">
            Sesi Anda telah diakhiri. Terima kasih telah mencatat kebiasaan baik hari ini!
        </p>

        <!-- Redirect Indicator -->
        <div class="mt-6 flex items-center justify-center gap-2.5 rounded-xl bg-slate-50 py-3 px-4 text-xs font-semibold text-slate-600 border border-slate-100">
            <svg class="h-4 w-4 animate-spin text-[#0c6d4d]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Mengalihkan ke halaman login...</span>
        </div>

        <!-- Manual Action Button -->
        <div class="mt-5">
            <a href="login.php" class="inline-flex w-full items-center justify-center rounded-xl bg-gradient-to-r from-[#0c6d4d] to-[#09573d] py-3.5 text-sm font-extrabold text-white shadow-lg shadow-emerald-900/20 hover:from-[#0a5d42] hover:to-[#074731] transition active:scale-[0.99]">
                Login Kembali
            </a>
        </div>
    </div>

    <!-- Auto Redirect Script -->
    <script>
        // Hapus sisa cache LocalStorage jika diperlukan (opsional)
        // localStorage.clear();

        // Mengalihkan pengguna otomatis ke halaman login setelah 2 detik
        setTimeout(() => {
            window.location.href = 'index.php';
        }, 2000);
    </script>
</body>
</html>