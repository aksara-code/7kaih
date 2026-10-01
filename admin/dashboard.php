<?php
session_start();
require_once '../koneksi.php';

if (
    !isset($_SESSION['user_id'])
    || ($_SESSION['role'] ?? '') !== 'guru'
    || ($_SESSION['guru_role'] ?? '') !== 'super-user'
) {
    header('Location: ../index.php');
    exit();
}

$adminNama = $_SESSION['nama'] ?? 'Super Admin';
$grades = ['X', 'XI', 'XII'];
$selectedGrade = $_GET['tingkat'] ?? '';
$selectedGrade = in_array($selectedGrade, $grades, true) ? $selectedGrade : '';
$selectedClass = strtoupper(trim($_GET['kelas'] ?? ''));
$classNames = [];
$classCounts = [];
$classIdsByName = [];

foreach ($grades as $grade) {
    for ($number = 1; $number <= 8; $number++) {
        $className = $grade . $number;
        $classCounts[$className] = 0;
        if ($grade === $selectedGrade) {
            $classNames[] = $className;
        }
    }
}

if (!in_array($selectedClass, $classNames, true)) {
    $selectedClass = '';
}

$stmtClasses = $pdo->query("SELECT id, nama_kelas FROM kelas");
foreach ($stmtClasses->fetchAll() as $class) {
    $classKey = strtoupper(preg_replace('/\s+/', '', $class['nama_kelas']));
    if (isset($classCounts[$classKey])) {
        $classIdsByName[$classKey] = (int) $class['id'];
    }
}

$stmtCounts = $pdo->query(
    "SELECT UPPER(REPLACE(k.nama_kelas, ' ', '')) AS kelas_key, COUNT(DISTINCT s.id) AS jumlah
     FROM kelas k
     LEFT JOIN siswa s ON s.id_kelas = k.id
     GROUP BY kelas_key"
);
foreach ($stmtCounts->fetchAll() as $row) {
    if (isset($classCounts[$row['kelas_key']])) {
        $classCounts[$row['kelas_key']] += (int) $row['jumlah'];
    }
}

$gradeCounts = array_fill_keys($grades, 0);
foreach ($classCounts as $className => $count) {
    $grade = preg_replace('/[0-9]+$/', '', $className);
    $gradeCounts[$grade] += $count;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Super Admin — Tujuh Kebiasaan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-100 min-h-screen text-slate-800 flex flex-col antialiased">
    <header class="bg-emerald-800 text-white shadow-lg">
        <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white text-emerald-800 flex items-center justify-center text-xl shadow">
                    <i class="fa-solid fa-seedling"></i>
                </div>
                <div>
                    <h1 class="font-black text-lg leading-tight">Dashboard Super Admin</h1>
                    <p class="text-xs text-emerald-200 font-medium">Aplikasi Tujuh Kebiasaan</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="text-right hidden sm:block">
                    <span class="block text-xs font-bold text-emerald-100"><?= htmlspecialchars($adminNama) ?></span>
                    <span class="inline-block px-2 py-0.5 text-[10px] font-extrabold uppercase rounded bg-emerald-700 text-emerald-100">Super Admin</span>
                </div>
                <a href="../logout.php" onclick="return confirm('Yakin ingin keluar?')" aria-label="Keluar" class="bg-red-600 hover:bg-red-700 text-white p-2.5 rounded-xl font-bold text-xs shadow">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 py-6 w-full flex-1">
        <section class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-lg font-black text-slate-900">Selamat datang, <?= htmlspecialchars($adminNama) ?></h2>
                    <p class="text-xs text-slate-500 font-semibold mt-1">Pilih jenjang, lalu kelas untuk melihat rekap siswa.</p>
                </div>
                <div class="flex flex-wrap gap-2 text-xs font-bold">
                    <span class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-3 py-2 rounded-xl">
                        <i class="fa-solid fa-users mr-1.5"></i><?= array_sum($classCounts) ?> siswa terdata
                    </span>
                    <?php if ($selectedGrade !== ''): ?>
                        <span class="bg-slate-100 border border-slate-200 text-slate-700 px-3 py-2 rounded-xl">
                            <?= htmlspecialchars($selectedGrade) ?> · <?= $gradeCounts[$selectedGrade] ?> siswa
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <?php if ($selectedGrade === ''): ?>
            <section aria-labelledby="grade-heading">
                <div class="mb-3">
                    <h3 id="grade-heading" class="font-extrabold text-sm text-slate-800">Pilih jenjang kelas</h3>
                    <p class="text-xs text-slate-500 mt-1">Rekap seluruh siswa dikelompokkan berdasarkan tingkat.</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <?php foreach ($grades as $grade): ?>
                        <a href="?tingkat=<?= urlencode($grade) ?>" class="group bg-white border border-slate-200 rounded-2xl p-5 shadow-sm hover:border-emerald-500 hover:shadow-md transition">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <span class="text-xs font-bold uppercase text-emerald-700">Jenjang</span>
                                    <h4 class="text-2xl font-black text-slate-900 mt-1">Kelas <?= htmlspecialchars($grade) ?></h4>
                                    <p class="text-xs font-semibold text-slate-500 mt-2">8 kelompok kelas · <?= $gradeCounts[$grade] ?> siswa</p>
                                </div>
                                <span class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center group-hover:bg-emerald-700 group-hover:text-white transition">
                                    <i class="fa-solid fa-arrow-right"></i>
                                </span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php else: ?>
            <nav class="flex items-center gap-2 text-xs font-bold mb-5" aria-label="Navigasi kelas">
                <a href="dashboard.php" class="text-emerald-700 hover:text-emerald-900"><i class="fa-solid fa-house mr-1"></i>Semua jenjang</a>
                <i class="fa-solid fa-chevron-right text-[9px] text-slate-400"></i>
                <?php if ($selectedClass !== ''): ?>
                    <a href="?tingkat=<?= urlencode($selectedGrade) ?>" class="text-emerald-700 hover:text-emerald-900">Kelas <?= htmlspecialchars($selectedGrade) ?></a>
                    <i class="fa-solid fa-chevron-right text-[9px] text-slate-400"></i>
                    <span class="text-slate-500"><?= htmlspecialchars($selectedClass) ?></span>
                <?php else: ?>
                    <span class="text-slate-500">Kelas <?= htmlspecialchars($selectedGrade) ?></span>
                <?php endif; ?>
            </nav>

            <section aria-labelledby="class-heading" class="mb-6">
                <div class="mb-3">
                    <h3 id="class-heading" class="font-extrabold text-sm text-slate-800">
                        <?= $selectedClass !== '' ? 'Rekap kelas ' . htmlspecialchars($selectedClass) : 'Pilih nomor kelas · ' . htmlspecialchars($selectedGrade) ?>
                    </h3>
                    <?php if ($selectedClass === ''): ?>
                        <p class="text-xs text-slate-500 mt-1">Pilih salah satu kelas untuk membuka daftar dan laporan siswa.</p>
                    <?php endif; ?>
                </div>

                <?php if ($selectedClass === ''): ?>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <?php foreach ($classNames as $className): ?>
                            <?php $classId = $classIdsByName[$className] ?? null; ?>
                            <a href="../guru/dashboard.php?kelas=<?= urlencode($className) ?>" class="group bg-white border border-slate-200 rounded-xl p-4 shadow-sm hover:border-emerald-500 hover:shadow-md transition">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-lg font-black text-slate-900"><?= htmlspecialchars($className) ?></span>
                                    <i class="fa-solid fa-arrow-up-right-from-square text-emerald-700 text-xs"></i>
                                </div>
                                <span class="block text-xs font-semibold text-slate-500 mt-1">
                                    <?= $classCounts[$className] ?> siswa · Buka kelas
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <?php $classId = $classIdsByName[$selectedClass] ?? null; ?>
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                        <?php if ($classId): ?>
                            <p class="text-sm font-semibold text-slate-600">Kelas <?= htmlspecialchars($selectedClass) ?> terhubung ke dashboard wali kelas.</p>
                            <a href="../guru/dashboard.php?kelas_id=<?= $classId ?>" class="mt-4 inline-flex items-center gap-2 bg-emerald-700 hover:bg-emerald-800 text-white px-4 py-2.5 rounded-xl text-sm font-bold transition">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i>Buka dashboard kelas
                            </a>
                        <?php else: ?>
                            <p class="text-sm font-semibold text-slate-600">Kelas <?= htmlspecialchars($selectedClass) ?> belum terdaftar di database.</p>
                            <p class="text-xs text-slate-500 mt-1">Tambahkan kelas dan wali kelasnya sebelum membuka rekap.</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>
    </main>

    <footer class="text-center py-6 mt-4">
        <p class="text-xs text-slate-500 font-bold">&copy; <?= date('Y') ?> Tujuh Kebiasaan Anak Indonesia Hebat</p>
    </footer>
</body>
</html>