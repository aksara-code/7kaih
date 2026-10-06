<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
require_once '../koneksi.php';

if (
    !isset($_SESSION['user_id'])
    || ($_SESSION['role'] ?? '') !== 'guru'
    || ($_SESSION['guru_role'] ?? '') !== 'super-user'
) {
    header('Location: ../index.php');
    exit();
}

function escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function redirectToClass(int $classId): void
{
    header('Location: kelola_kelas.php?id=' . $classId);
    exit();
}

$classId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$classId || $classId < 1) {
    header('Location: dashboard.php');
    exit();
}

$classQuery = $pdo->prepare(
    'SELECT k.id, k.nama_kelas, k.id_guru, g.nama AS nama_guru, g.nip, g.username
     FROM kelas k
     LEFT JOIN guru g ON g.id = k.id_guru
     WHERE k.id = :id
     LIMIT 1'
);
$classQuery->execute(['id' => $classId]);
$kelas = $classQuery->fetch();
if (!$kelas) {
    http_response_code(404);
    exit('Kelas tidak ditemukan.');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];
$message = $_SESSION['class_message'] ?? '';
$messageType = $_SESSION['class_message_type'] ?? 'success';
unset($_SESSION['class_message'], $_SESSION['class_message_type']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postedToken = $_POST['csrf_token'] ?? '';
    if (!is_string($postedToken) || !hash_equals($csrfToken, $postedToken)) {
        $message = 'Permintaan tidak valid. Muat ulang halaman dan coba lagi.';
        $messageType = 'error';
    } else {
        $action = $_POST['action'] ?? '';
        try {
            if ($action === 'add_teacher') {
                $name = trim($_POST['nama'] ?? '');
                $nip = trim($_POST['nip'] ?? '');
                $username = trim($_POST['username'] ?? '');
                $password = $_POST['password'] ?? '';
                if ($name === '' || $nip === '' || !is_string($password) || strlen($password) < 6) {
                    throw new RuntimeException('Nama, NIP, dan password minimal 6 karakter wajib diisi.');
                }
                if (!empty($kelas['id_guru'])) {
                    throw new RuntimeException('Kelas sudah memiliki wali. Lepas wali saat ini sebelum menambahkan penggantinya.');
                }
                $alreadyAssigned = $pdo->prepare('SELECT id FROM kelas WHERE id_guru = :guru_id LIMIT 1');
                $alreadyAssigned->execute(['guru_id' => (int) $_SESSION['user_id']]);
                $pdo->beginTransaction();
                $stmt = $pdo->prepare('INSERT INTO guru (nip, nama, username, password) VALUES (:nip, :nama, :username, :password)');
                $stmt->execute([
                    'nip' => $nip,
                    'nama' => $name,
                    'username' => $username !== '' ? $username : null,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                ]);
                $teacherId = (int) $pdo->lastInsertId();
                $stmt = $pdo->prepare('UPDATE kelas SET id_guru = :guru_id WHERE id = :kelas_id AND id_guru IS NULL');
                $stmt->execute(['guru_id' => $teacherId, 'kelas_id' => $classId]);
                if ($stmt->rowCount() !== 1) {
                    throw new RuntimeException('Wali kelas berubah. Muat ulang halaman lalu coba lagi.');
                }
                $pdo->commit();
                $message = 'Akun guru berhasil dibuat dan ditugaskan sebagai wali kelas.';
            } elseif ($action === 'update_teacher') {
                $teacherId = filter_input(INPUT_POST, 'guru_id', FILTER_VALIDATE_INT);
                $name = trim($_POST['nama'] ?? '');
                $nip = trim($_POST['nip'] ?? '');
                $username = trim($_POST['username'] ?? '');
                $password = $_POST['password'] ?? '';
                if (!$teacherId || (int) $kelas['id_guru'] !== $teacherId || $name === '' || $nip === '') {
                    throw new RuntimeException('Data guru wali tidak valid.');
                }
                if (!is_string($password) || ($password !== '' && strlen($password) < 6)) {
                    throw new RuntimeException('Password baru harus kosong atau minimal 6 karakter.');
                }
                if ($password !== '') {
                    $stmt = $pdo->prepare('UPDATE guru SET nama = :nama, nip = :nip, username = :username, password = :password WHERE id = :id');
                    $stmt->execute([
                        'nama' => $name,
                        'nip' => $nip,
                        'username' => $username !== '' ? $username : null,
                        'password' => password_hash($password, PASSWORD_DEFAULT),
                        'id' => $teacherId,
                    ]);
                } else {
                    $stmt = $pdo->prepare('UPDATE guru SET nama = :nama, nip = :nip, username = :username WHERE id = :id');
                    $stmt->execute([
                        'nama' => $name,
                        'nip' => $nip,
                        'username' => $username !== '' ? $username : null,
                        'id' => $teacherId,
                    ]);
                }
                $message = 'Data guru berhasil diperbarui.';
            } elseif ($action === 'remove_teacher') {
                $teacherId = filter_input(INPUT_POST, 'guru_id', FILTER_VALIDATE_INT);
                $stmt = $pdo->prepare('UPDATE kelas SET id_guru = NULL WHERE id = :kelas_id AND id_guru = :guru_id');
                $stmt->execute(['kelas_id' => $classId, 'guru_id' => $teacherId]);
                if ($stmt->rowCount() !== 1) {
                    throw new RuntimeException('Guru tersebut bukan wali kelas ini.');
                }
                $message = 'Guru berhasil dilepas dari kelas. Akun guru tetap tersimpan.';
            } else {
                throw new RuntimeException('Aksi tidak dikenal.');
            }
            $messageType = 'success';
        } catch (RuntimeException $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $message = $exception->getMessage();
            $messageType = 'error';
        } catch (PDOException $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log($exception->getMessage());
            $message = 'Gagal menyimpan perubahan. Pastikan NISN, NIP, dan username belum digunakan.';
            $messageType = 'error';
        }
        $_SESSION['class_message'] = $message;
        $_SESSION['class_message_type'] = $messageType;
        redirectToClass((int) $classId);
    }
}

$studentsQuery = $pdo->prepare('SELECT id, nama, nisn FROM siswa WHERE id_kelas = :id ORDER BY nama ASC');
$studentsQuery->execute(['id' => $classId]);
$students = $studentsQuery->fetchAll();
$habitCategories = [
    'bangun' => 'Bangun Pagi',
    'ibadah' => 'Beribadah',
    'belajar' => 'Gemar Belajar',
    'makan' => 'Makan Sehat',
    'olahraga' => 'Olahraga',
    'bermasyarakat' => 'Bermasyarakat',
    'tidur' => 'Tidur Cepat',
];
$classProgress = array_fill_keys(array_keys($habitCategories), 0);
$studentProgress = [];
$today = date('Y-m-d');
$progressQuery = $pdo->prepare(
    'SELECT la.id_siswa, la.kategori
     FROM log_aktivitas la
     INNER JOIN siswa s ON s.id = la.id_siswa
     WHERE s.id_kelas = :id_kelas AND la.waktu_mulai >= :start_date AND la.waktu_mulai < :end_date
     GROUP BY la.id_siswa, la.kategori'
);
$progressQuery->execute([
    'id_kelas' => $classId,
    'start_date' => $today . ' 00:00:00',
    'end_date' => date('Y-m-d', strtotime($today . ' +1 day')) . ' 00:00:00',
]);
foreach ($progressQuery->fetchAll() as $activity) {
    $category = $activity['kategori'];
    if (!array_key_exists($category, $classProgress)) {
        continue;
    }
    $classProgress[$category]++;
    $studentProgress[(int) $activity['id_siswa']] = ($studentProgress[(int) $activity['id_siswa']] ?? 0) + 1;
}
$studentCount = count($students);
$totalPossible = $studentCount * count($habitCategories);
$totalCompleted = array_sum($classProgress);
$progressPercent = $totalPossible > 0 ? (int) round(($totalCompleted / $totalPossible) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kelas <?= escape($kelas['nama_kelas']) ?> — Tujuh Kebiasaan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-100 min-h-screen text-slate-800 antialiased">
    <header class="bg-emerald-800 text-white shadow-lg">
        <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between gap-4">
            <div>
                <a href="dashboard.php" class="text-xs text-emerald-100 hover:text-white font-bold"><i class="fa-solid fa-arrow-left mr-1"></i>Dashboard Super Admin</a>
                <h1 class="font-black text-xl mt-1">Kelola Kelas <?= escape($kelas['nama_kelas']) ?></h1>
            </div>
            <a href="../logout.php" class="bg-red-600 hover:bg-red-700 text-white p-2.5 rounded-xl" aria-label="Keluar"><i class="fa-solid fa-right-from-bracket"></i></a>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 py-6 space-y-6">
        <?php if ($message !== ''): ?>
            <div role="status" class="<?= $messageType === 'error' ? 'bg-red-50 border-red-200 text-red-800' : 'bg-emerald-50 border-emerald-200 text-emerald-800' ?> border rounded-xl px-4 py-3 text-sm font-bold">
                <?= escape($message) ?>
            </div>
        <?php endif; ?>

        <section class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="font-black text-lg text-slate-900">Guru Wali</h2>
                    <p class="text-sm text-slate-500 mt-1"><?= $kelas['nama_guru'] ? 'Wali saat ini: ' . escape($kelas['nama_guru']) : 'Belum ada guru yang ditugaskan sebagai wali kelas.' ?></p>
                </div>
                <span class="text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 px-3 py-2 rounded-lg">Kelas <?= escape($kelas['nama_kelas']) ?></span>
            </div>

            <?php if ($kelas['id_guru']): ?>
                <div class="mt-5 grid md:grid-cols-[1fr_auto] gap-4 items-end">
                    <form method="post" class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        <input type="hidden" name="csrf_token" value="<?= escape($csrfToken) ?>">
                        <input type="hidden" name="action" value="update_teacher">
                        <input type="hidden" name="guru_id" value="<?= (int) $kelas['id_guru'] ?>">
                        <label class="text-xs font-bold text-slate-600">Nama
                            <input name="nama" required value="<?= escape($kelas['nama_guru']) ?>" class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                        </label>
                        <label class="text-xs font-bold text-slate-600">NIP
                            <input name="nip" required value="<?= escape($kelas['nip'] ?? '') ?>" class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                        </label>
                        <label class="text-xs font-bold text-slate-600">Username
                            <input name="username" value="<?= escape($kelas['username'] ?? '') ?>" class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                        </label>
                        <label class="text-xs font-bold text-slate-600">Password baru (opsional)
                            <input name="password" type="password" minlength="6" autocomplete="new-password" class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                        </label>
                        <button class="sm:col-span-2 lg:col-span-4 justify-self-start bg-emerald-700 hover:bg-emerald-800 text-white px-4 py-2 rounded-lg text-sm font-bold"><i class="fa-solid fa-floppy-disk mr-1"></i>Simpan perubahan guru</button>
                    </form>
                    <form method="post" onsubmit="return confirm('Lepaskan guru ini dari kelas? Akun tetap tersimpan.');">
                        <input type="hidden" name="csrf_token" value="<?= escape($csrfToken) ?>">
                        <input type="hidden" name="action" value="remove_teacher">
                        <input type="hidden" name="guru_id" value="<?= (int) $kelas['id_guru'] ?>">
                        <button class="bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 px-4 py-2 rounded-lg text-sm font-bold"><i class="fa-solid fa-user-minus mr-1"></i>Lepas dari kelas</button>
                    </form>
                </div>
            <?php else: ?>
                <form method="post" class="mt-5 grid sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
                    <input type="hidden" name="csrf_token" value="<?= escape($csrfToken) ?>">
                    <input type="hidden" name="action" value="add_teacher">
                    <label class="text-xs font-bold text-slate-600">Nama guru
                        <input name="nama" required class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                    </label>
                    <label class="text-xs font-bold text-slate-600">NIP
                        <input name="nip" required class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                    </label>
                    <label class="text-xs font-bold text-slate-600">Username (opsional)
                        <input name="username" class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                    </label>
                    <label class="text-xs font-bold text-slate-600">Password (min. 6 karakter)
                        <input name="password" type="password" minlength="6" required autocomplete="new-password" class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                    </label>
                    <button class="bg-emerald-700 hover:bg-emerald-800 text-white px-4 py-2 rounded-lg text-sm font-bold"><i class="fa-solid fa-user-plus mr-1"></i>Tambah guru</button>
                </form>
            <?php endif; ?>
        </section>

        <section class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
                <div>
                    <h2 class="font-black text-lg text-slate-900">Progres Kelas <?= escape($kelas['nama_kelas']) ?></h2>
                    <p class="text-sm text-slate-500 mt-1">Rekap pengisian 7 kegiatan hari ini, <?= date('d-m-Y') ?>.</p>
                </div>
                <span class="text-sm font-extrabold text-emerald-800"><?= $totalCompleted ?> / <?= $totalPossible ?> kegiatan terisi</span>
            </div>
            <div class="mt-4 h-3 overflow-hidden rounded-full bg-slate-100" role="progressbar" aria-label="Progres kegiatan kelas" aria-valuenow="<?= $progressPercent ?>" aria-valuemin="0" aria-valuemax="100">
                <div class="h-full rounded-full bg-emerald-600 transition-all" style="width: <?= $progressPercent ?>%"></div>
            </div>
            <p class="mt-2 text-right text-xs font-bold text-slate-500"><?= $progressPercent ?>% dari target kelas</p>

            <div class="mt-6 grid md:grid-cols-[minmax(0,1fr)_minmax(0,1.5fr)] gap-8 items-center">
                <div class="mx-auto w-full max-w-xs">
                    <canvas id="classProgressChart" aria-label="Donut progres keseluruhan kelas"></canvas>
                </div>
                <div class="min-w-0">
                    <h3 class="font-extrabold text-sm text-slate-800 mb-3">Pengisian per kegiatan</h3>
                    <canvas id="habitProgressChart" aria-label="Chart siswa yang sudah mengisi tiap kegiatan"></canvas>
                </div>
            </div>
        </section>

        <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-200">
                <h2 class="font-black text-lg text-slate-900">Siswa Kelas <?= escape($kelas['nama_kelas']) ?></h2>
                <p class="text-sm text-slate-500 mt-1"><?= $studentCount ?> siswa terdaftar di database</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-5 py-3">No</th>
                            <th class="px-5 py-3">Nama Siswa</th>
                            <th class="px-5 py-3">NISN</th>
                            <th class="px-5 py-3">Progres Hari Ini</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($students as $index => $student): ?>
                            <?php $completed = $studentProgress[(int) $student['id']] ?? 0; ?>
                            <tr>
                                <td class="px-5 py-4 text-slate-400 font-bold"><?= $index + 1 ?></td>
                                <td class="px-5 py-4 font-bold text-slate-900"><?= escape($student['nama']) ?></td>
                                <td class="px-5 py-4 text-slate-600"><?= escape($student['nisn'] ?? '-') ?></td>
                                <td class="px-5 py-4 min-w-48">
                                    <div class="flex items-center gap-3">
                                        <div class="h-2 flex-1 rounded-full bg-slate-100 overflow-hidden">
                                            <div class="h-full rounded-full bg-emerald-600" style="width: <?= (int) round(($completed / 7) * 100) ?>%"></div>
                                        </div>
                                        <span class="w-8 text-right text-xs font-extrabold text-slate-700"><?= $completed ?>/7</span>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$students): ?>
                            <tr><td colspan="4" class="px-5 py-10 text-center text-sm text-slate-500">Belum ada siswa terdaftar di kelas ini.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
    <script>
        const habitLabels = <?= json_encode(array_values($habitCategories), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        const habitCounts = <?= json_encode(array_values($classProgress)) ?>;
        const studentCount = <?= $studentCount ?>;
        const totalCompleted = <?= $totalCompleted ?>;
        const totalPossible = <?= $totalPossible ?>;

        new Chart(document.getElementById('classProgressChart'), {
            type: 'doughnut',
            data: {
                labels: ['Terisi', 'Belum terisi'],
                datasets: [{
                    data: [totalCompleted, Math.max(totalPossible - totalCompleted, 0)],
                    backgroundColor: ['#059669', '#e2e8f0'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                cutout: '72%',
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, padding: 18 } },
                    tooltip: { enabled: totalPossible > 0 }
                }
            }
        });

        new Chart(document.getElementById('habitProgressChart'), {
            type: 'bar',
            data: {
                labels: habitLabels,
                datasets: [{
                    label: 'Siswa mengisi',
                    data: habitCounts,
                    backgroundColor: ['#f59e0b', '#10b981', '#3b82f6', '#f43f5e', '#f97316', '#14b8a6', '#6366f1'],
                    borderRadius: 5,
                    maxBarThickness: 28
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                scales: {
                    x: { beginAtZero: true, max: Math.max(studentCount, 1), ticks: { precision: 0 }, grid: { color: '#e2e8f0' } },
                    y: { grid: { display: false } }
                },
                plugins: { legend: { display: false } }
            }
        });
    </script>
</body>
</html>