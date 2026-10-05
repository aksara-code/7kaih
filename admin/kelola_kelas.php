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
            if ($action === 'add_student') {
                $name = trim($_POST['nama'] ?? '');
                $nisn = trim($_POST['nisn'] ?? '');
                $password = $_POST['password'] ?? '';
                if ($name === '' || $nisn === '' || !is_string($password) || strlen($password) < 6) {
                    throw new RuntimeException('Nama, NISN, dan password minimal 6 karakter wajib diisi.');
                }
                $stmt = $pdo->prepare('INSERT INTO siswa (nama, nisn, password, id_kelas) VALUES (:nama, :nisn, :password, :id_kelas)');
                $stmt->execute([
                    'nama' => $name,
                    'nisn' => $nisn,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'id_kelas' => $classId,
                ]);
                $message = 'Siswa berhasil ditambahkan ke kelas.';
            } elseif ($action === 'assign_student') {
                $studentId = filter_input(INPUT_POST, 'siswa_id', FILTER_VALIDATE_INT);
                if (!$studentId || $studentId < 1) {
                    throw new RuntimeException('Pilih siswa yang akan dimasukkan ke kelas.');
                }
                $stmt = $pdo->prepare('UPDATE siswa SET id_kelas = :kelas_id WHERE id = :siswa_id AND id_kelas IS NULL');
                $stmt->execute(['kelas_id' => $classId, 'siswa_id' => $studentId]);
                if ($stmt->rowCount() !== 1) {
                    throw new RuntimeException('Siswa tidak ditemukan atau sudah berada di kelas lain.');
                }
                $message = 'Siswa berhasil dimasukkan ke kelas.';
            } elseif ($action === 'update_student') {
                $studentId = filter_input(INPUT_POST, 'siswa_id', FILTER_VALIDATE_INT);
                $name = trim($_POST['nama'] ?? '');
                $nisn = trim($_POST['nisn'] ?? '');
                $password = $_POST['password'] ?? '';
                if (!$studentId || $studentId < 1 || $name === '' || $nisn === '') {
                    throw new RuntimeException('Nama dan NISN siswa wajib diisi.');
                }
                if (!is_string($password) || ($password !== '' && strlen($password) < 6)) {
                    throw new RuntimeException('Password baru harus kosong atau minimal 6 karakter.');
                }
                if ($password !== '') {
                    $stmt = $pdo->prepare('UPDATE siswa SET nama = :nama, nisn = :nisn, password = :password WHERE id = :id AND id_kelas = :kelas_id');
                    $stmt->execute([
                        'nama' => $name,
                        'nisn' => $nisn,
                        'password' => password_hash($password, PASSWORD_DEFAULT),
                        'id' => $studentId,
                        'kelas_id' => $classId,
                    ]);
                } else {
                    $stmt = $pdo->prepare('UPDATE siswa SET nama = :nama, nisn = :nisn WHERE id = :id AND id_kelas = :kelas_id');
                    $stmt->execute(['nama' => $name, 'nisn' => $nisn, 'id' => $studentId, 'kelas_id' => $classId]);
                }
                if ($stmt->rowCount() === 0) {
                    $check = $pdo->prepare('SELECT id FROM siswa WHERE id = :id AND id_kelas = :kelas_id');
                    $check->execute(['id' => $studentId, 'kelas_id' => $classId]);
                    if (!$check->fetch()) {
                        throw new RuntimeException('Siswa tidak ditemukan di kelas ini.');
                    }
                }
                $message = 'Data siswa berhasil diperbarui.';
            } elseif ($action === 'remove_student') {
                $studentId = filter_input(INPUT_POST, 'siswa_id', FILTER_VALIDATE_INT);
                $stmt = $pdo->prepare('UPDATE siswa SET id_kelas = NULL WHERE id = :id AND id_kelas = :kelas_id');
                $stmt->execute(['id' => $studentId, 'kelas_id' => $classId]);
                if ($stmt->rowCount() !== 1) {
                    throw new RuntimeException('Siswa tidak ditemukan di kelas ini.');
                }
                $message = 'Siswa dilepas dari kelas. Akun dan riwayat kegiatannya tetap tersimpan.';
            } elseif ($action === 'add_teacher') {
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
$unassignedQuery = $pdo->query('SELECT id, nama, nisn FROM siswa WHERE id_kelas IS NULL ORDER BY nama ASC');
$unassignedStudents = $unassignedQuery->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kelas <?= escape($kelas['nama_kelas']) ?> — Tujuh Kebiasaan</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
            <div class="flex items-center justify-between gap-3 mb-4">
                <div>
                    <h2 class="font-black text-lg text-slate-900">Siswa Kelas <?= escape($kelas['nama_kelas']) ?></h2>
                    <p class="text-sm text-slate-500 mt-1"><?= count($students) ?> siswa terdaftar</p>
                </div>
            </div>

            <div class="border-y border-slate-200 py-5 mb-5">
                <h3 class="font-extrabold text-sm text-slate-800 mb-3">Tambah siswa baru</h3>
                <form method="post" class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
                    <input type="hidden" name="csrf_token" value="<?= escape($csrfToken) ?>">
                    <input type="hidden" name="action" value="add_student">
                    <label class="text-xs font-bold text-slate-600">Nama siswa
                        <input name="nama" required class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                    </label>
                    <label class="text-xs font-bold text-slate-600">NISN
                        <input name="nisn" required class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                    </label>
                    <label class="text-xs font-bold text-slate-600">Password (min. 6 karakter)
                        <input name="password" type="password" minlength="6" required autocomplete="new-password" class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                    </label>
                    <button class="bg-emerald-700 hover:bg-emerald-800 text-white px-4 py-2 rounded-lg text-sm font-bold"><i class="fa-solid fa-user-plus mr-1"></i>Tambah siswa</button>
                </form>
                <?php if ($unassignedStudents): ?>
                    <form method="post" class="mt-4 flex flex-col sm:flex-row gap-2">
                        <input type="hidden" name="csrf_token" value="<?= escape($csrfToken) ?>">
                        <input type="hidden" name="action" value="assign_student">
                        <label for="siswa_id" class="sr-only">Pilih siswa yang belum memiliki kelas</label>
                        <select id="siswa_id" name="siswa_id" required class="min-w-0 flex-1 border border-slate-300 rounded-lg px-3 py-2 text-sm">
                            <option value="">Masukkan siswa yang belum memiliki kelas</option>
                            <?php foreach ($unassignedStudents as $student): ?>
                                <option value="<?= (int) $student['id'] ?>"><?= escape($student['nama']) ?> · NISN <?= escape($student['nisn'] ?? '-') ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-bold">Masukkan ke kelas</button>
                    </form>
                <?php endif; ?>
            </div>

            <div class="space-y-3">
                <?php foreach ($students as $student): ?>
                    <article id="student-<?= (int) $student['id'] ?>" class="border border-slate-200 rounded-xl p-4 scroll-mt-6">
                        <form method="post" class="grid sm:grid-cols-2 lg:grid-cols-[1fr_1fr_1fr_auto] gap-3 items-end">
                            <input type="hidden" name="csrf_token" value="<?= escape($csrfToken) ?>">
                            <input type="hidden" name="action" value="update_student">
                            <input type="hidden" name="siswa_id" value="<?= (int) $student['id'] ?>">
                            <label class="text-xs font-bold text-slate-600">Nama
                                <input name="nama" required value="<?= escape($student['nama']) ?>" class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                            </label>
                            <label class="text-xs font-bold text-slate-600">NISN
                                <input name="nisn" required value="<?= escape($student['nisn'] ?? '') ?>" class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                            </label>
                            <label class="text-xs font-bold text-slate-600">Password baru (opsional)
                                <input name="password" type="password" minlength="6" autocomplete="new-password" class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                            </label>
                            <button class="bg-emerald-700 hover:bg-emerald-800 text-white px-4 py-2 rounded-lg text-sm font-bold"><i class="fa-solid fa-floppy-disk mr-1"></i>Simpan</button>
                        </form>
                        <form method="post" class="mt-3" onsubmit="return confirm('Lepaskan siswa dari kelas? Akun dan seluruh riwayat aktivitas tetap tersimpan.');">
                            <input type="hidden" name="csrf_token" value="<?= escape($csrfToken) ?>">
                            <input type="hidden" name="action" value="remove_student">
                            <input type="hidden" name="siswa_id" value="<?= (int) $student['id'] ?>">
                            <button class="text-red-700 hover:text-red-900 text-xs font-bold"><i class="fa-solid fa-user-minus mr-1"></i>Hapus dari kelas</button>
                        </form>
                    </article>
                <?php endforeach; ?>
                <?php if (!$students): ?>
                    <p class="py-8 text-center text-sm text-slate-500">Belum ada siswa di kelas ini.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>
</body>
</html>