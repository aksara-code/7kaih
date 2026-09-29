<?php
session_start();

// 1. Kosongkan semua variabel session
$_SESSION = array();

// 2. Hapus cookie Session (PHPSESSID)
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

// 3. Hapus cookie "Remember Me" jika ada
if (isset($_COOKIE['remember_me'])) {
    setcookie('remember_me', '', time() - 3600, '/');
}

// 4. Hancurkan session
session_destroy();

// 5. Langsung alihkan ke halaman login (index.php) agar autofill browser langsung aktif
header("Location: index.php");
exit();