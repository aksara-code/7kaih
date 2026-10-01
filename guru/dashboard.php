<?php
session_start();
require_once '../koneksi.php';

// Validasi Keamanan Akses Guru
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'guru') {
    header("Location: ../index.php");
    exit();
}

$guru_id   = $_SESSION['user_id'];
$guru_nama = $_SESSION['nama'];
$guru_role = $_SESSION['guru_role'] ?? 'admin';

$siswa_list = [];
$kelas_list = [];
$selected_kelas = $_GET['kelas_id'] ?? null;
$nama_kelas_wali = '';

if ($guru_role === 'super-user') {
    // TIM 7 KAIH: Mengambil semua daftar kelas untuk dropdown filter
    $stmt_kelas = $pdo->query("SELECT * FROM kelas ORDER BY nama_kelas ASC");
    $kelas_list = $stmt_kelas->fetchAll();

    if (!empty($selected_kelas)) {
        $stmt = $pdo->prepare("
            SELECT s.*, k.nama_kelas 
            FROM siswa s 
            LEFT JOIN kelas k ON s.id_kelas = k.id 
            WHERE s.id_kelas = :kelas_id 
            ORDER BY s.nama ASC
        ");
        $stmt->execute(['kelas_id' => $selected_kelas]);
    } else {
        $stmt = $pdo->query("
            SELECT s.*, k.nama_kelas 
            FROM siswa s 
            LEFT JOIN kelas k ON s.id_kelas = k.id 
            ORDER BY k.nama_kelas ASC, s.nama ASC
        ");
    }
    $siswa_list = $stmt->fetchAll();

} else {
    // WALI KELAS: Mencari kelas yang diampu berdasarkan id_guru di tabel kelas
    $stmt_wali = $pdo->prepare("SELECT id, nama_kelas FROM kelas WHERE id_guru = :id_guru LIMIT 1");
    $stmt_wali->execute(['id_guru' => $guru_id]);
    $kelas_wali = $stmt_wali->fetch();

    if ($kelas_wali) {
        $id_kelas_wali   = $kelas_wali['id'];
        $nama_kelas_wali = $kelas_wali['nama_kelas'];

        // Mengambil hanya siswa di kelas binaannya
        $stmt = $pdo->prepare("
            SELECT s.*, k.nama_kelas 
            FROM siswa s 
            LEFT JOIN kelas k ON s.id_kelas = k.id 
            WHERE s.id_kelas = :id_kelas 
            ORDER BY s.nama ASC
        ");
        $stmt->execute(['id_kelas' => $id_kelas_wali]);
        $siswa_list = $stmt->fetchAll();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Tenaga Pendidik</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-100 min-h-screen text-slate-800 flex flex-col justify-between antialiased pb-12">

    <header class="bg-emerald-800 text-white shadow-lg">
        <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white text-emerald-800 flex items-center justify-center font-black text-xl shadow">
                    <i class="fa-solid fa-seedling"></i>
                </div>
                <div>
                    <h1 class="font-black text-lg leading-tight">Dashboard Tenaga Pendidik</h1>
                    <p class="text-xs text-emerald-200 font-medium">Aplikasi Tujuh Kebiasaan</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div class="text-right hidden sm:block">
                    <span class="block text-xs font-bold text-emerald-200"><?= htmlspecialchars($guru_nama) ?></span>
                    <span class="inline-block px-2 py-0.5 text-[10px] font-extrabold uppercase rounded bg-emerald-700 text-emerald-100">
                        <?= $guru_role === 'super-user' ? 'Tim 7 KAIH' : 'Wali Kelas ' . htmlspecialchars($nama_kelas_wali) ?>
                    </span>
                </div>
                <a href="../logout.php" onclick="return confirm('Yakin ingin keluar?')" class="bg-red-600 hover:bg-red-700 text-white p-2.5 rounded-xl font-bold text-xs shadow">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 mt-6 w-full mb-auto">

        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200 mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-black text-slate-900">Selamat Datang, <?= htmlspecialchars($guru_nama) ?></h2>
                <p class="text-xs text-slate-500 font-semibold mt-0.5">
                    <?= $guru_role === 'super-user' 
                        ? 'Akses Tim 7 KAIH: Memantau seluruh data siswa di semua kelas.' 
                        : 'Akses Wali Kelas: Memantau siswa kelas ' . htmlspecialchars($nama_kelas_wali) . '.' ?>
                </p>
            </div>

            <!-- Filter Khusus Super-User -->
            <?php if ($guru_role === 'super-user'): ?>
                <form method="GET" action="" class="w-full sm:w-auto flex items-center gap-2">
                    <label for="kelas_id" class="text-xs font-bold text-slate-700 shrink-0">Filter Kelas:</label>
                    <select name="kelas_id" id="kelas_id" onchange="this.form.submit()" class="bg-slate-50 border-2 border-slate-300 rounded-xl px-3 py-2 text-xs font-bold focus:outline-none focus:border-emerald-600">
                        <option value="">-- Semua Kelas --</option>
                        <?php foreach ($kelas_list as $k): ?>
                            <option value="<?= $k['id'] ?>" <?= ($selected_kelas == $k['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($k['nama_kelas']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            <?php endif; ?>
        </div>

        <!-- Tabel Siswa -->
        <div class="bg-white rounded-3xl shadow-xl border border-slate-200 overflow-hidden">
            <div class="p-5 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                <h3 class="font-extrabold text-sm text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-users text-emerald-700"></i> Daftar Siswa 
                    <span class="text-xs bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full font-bold">
                        <?= count($siswa_list) ?> Siswa
                    </span>
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs sm:text-sm">
                    <thead>
                        <tr class="bg-slate-100 text-slate-700 uppercase font-extrabold border-b border-slate-200">
                            <th class="p-4 w-12 text-center">No</th>
                            <th class="p-4">Nama Siswa</th>
                            <th class="p-4">NISN</th>
                            <th class="p-4">Kelas</th>
                            <th class="p-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-semibold text-slate-700">
                        <?php if (!empty($siswa_list)): ?>
                            <?php foreach ($siswa_list as $index => $s): ?>
                                <tr class="hover:bg-slate-50">
                                    <td class="p-4 text-center font-bold text-slate-400"><?= $index + 1 ?></td>
                                    <td class="p-4 font-bold text-slate-900"><?= htmlspecialchars($s['nama']) ?></td>
                                    <td class="p-4"><?= htmlspecialchars($s['nisn'] ?? '-') ?></td>
                                    <td class="p-4">
                                        <span class="bg-slate-100 border border-slate-300 text-slate-700 px-2.5 py-1 rounded-lg text-xs font-bold">
                                            <?= htmlspecialchars($s['nama_kelas'] ?? 'Belum Diatur') ?>
                                        </span>
                                    </td>
                                    <td class="p-4 text-center">
                                        <a href="../laporan_siswa.php?id=<?= $s['id'] ?>" class="bg-emerald-700 hover:bg-emerald-800 text-white px-3 py-1.5 rounded-xl text-xs font-bold transition-all inline-flex items-center gap-1.5">
                                            <i class="fa-solid fa-file-lines"></i>
                                            <span>Laporan</span>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="p-8 text-center text-slate-400 font-medium">
                                    Data siswa tidak ditemukan untuk kelas ini.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <footer class="text-center py-6 mt-8">
        <p class="text-xs text-slate-500 font-bold">&copy; <?= date('Y') ?> Tujuh Kebiasaan Anak Indonesia Hebat</p>
    </footer>

</body>
</html>