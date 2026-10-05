<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
header('Content-Type: application/json; charset=utf-8');

function respond(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit();
}

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'siswa') {
    respond(401, ['success' => false, 'message' => 'Silakan login sebagai siswa terlebih dahulu.']);
}

$categories = ['bangun', 'tidur', 'ibadah', 'belajar'];
$category = $_SERVER['REQUEST_METHOD'] === 'POST'
    ? ($_POST['category'] ?? '')
    : ($_GET['category'] ?? '');

if (!in_array($category, $categories, true)) {
    respond(400, ['success' => false, 'message' => 'Kategori aktivitas tidak valid.']);
}

require_once 'koneksi.php';
$studentId = (int) $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $pdo->prepare(
            'SELECT waktu_mulai, deskripsi, catatan_tambahan, foto
             FROM log_aktivitas
             WHERE id_siswa = :id_siswa AND kategori = :kategori
             ORDER BY waktu_mulai DESC, id DESC'
        );
        $stmt->execute(['id_siswa' => $studentId, 'kategori' => $category]);
        $items = array_map(static function (array $item): array {
            $timestamp = strtotime($item['waktu_mulai']);
            return [
                'date' => date('Y-m-d', $timestamp),
                'time' => date('H:i', $timestamp),
                'option' => $item['deskripsi'],
                'note' => $item['catatan_tambahan'] ?? '',
                'image' => !empty($item['foto']) ? 'uploads/aktivitas/' . rawurlencode($item['foto']) : '',
            ];
        }, $stmt->fetchAll());

        respond(200, ['success' => true, 'items' => $items]);
    } catch (PDOException $exception) {
        error_log($exception->getMessage());
        respond(500, ['success' => false, 'message' => 'Gagal memuat riwayat aktivitas.']);
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: GET, POST');
    respond(405, ['success' => false, 'message' => 'Metode tidak didukung.']);
}

$optionInput = $_POST['option'] ?? '';
$noteInput = $_POST['note'] ?? '';
$date = $_POST['date'] ?? date('Y-m-d');
if (!is_string($optionInput) || !is_string($noteInput) || !is_string($date)) {
    respond(400, ['success' => false, 'message' => 'Data aktivitas tidak valid.']);
}

$option = trim($optionInput);
$note = trim($noteInput);
$parsedDate = DateTime::createFromFormat('!Y-m-d', $date);
if ($parsedDate === false || $parsedDate->format('Y-m-d') !== $date) {
    respond(400, ['success' => false, 'message' => 'Tanggal aktivitas tidak valid.']);
}
if ($option === '' || mb_strlen($option) > 255 || mb_strlen($note) > 255) {
    respond(400, ['success' => false, 'message' => 'Judul atau catatan aktivitas tidak valid.']);
}

$photoRequired = in_array($category, ['bangun', 'tidur'], true);
$photoName = null;
$uploadPath = null;

if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
    if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK || $_FILES['foto']['size'] > 8 * 1024 * 1024) {
        respond(400, ['success' => false, 'message' => 'Foto gagal diunggah atau ukurannya melebihi 8 MB.']);
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($_FILES['foto']['tmp_name']);
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($extensions[$mime])) {
        respond(400, ['success' => false, 'message' => 'Foto harus berformat JPG, PNG, atau WEBP.']);
    }

    $uploadDirectory = __DIR__ . '/uploads/aktivitas';
    if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0775, true) && !is_dir($uploadDirectory)) {
        respond(500, ['success' => false, 'message' => 'Folder penyimpanan foto tidak dapat dibuat.']);
    }

    $photoName = $category . '_' . $studentId . '_' . bin2hex(random_bytes(8)) . '.' . $extensions[$mime];
    $uploadPath = $uploadDirectory . '/' . $photoName;
    if (!move_uploaded_file($_FILES['foto']['tmp_name'], $uploadPath)) {
        respond(500, ['success' => false, 'message' => 'Foto gagal disimpan.']);
    }
} elseif ($photoRequired) {
    respond(400, ['success' => false, 'message' => 'Foto verifikasi wajib disertakan.']);
}

try {
    $stmt = $pdo->prepare(
        'INSERT INTO log_aktivitas
            (id_siswa, kategori, waktu_mulai, waktu_selesai, deskripsi, catatan_tambahan, foto)
         VALUES
            (:id_siswa, :kategori, :waktu_mulai, NULL, :deskripsi, :catatan_tambahan, :foto)'
    );
    $stmt->execute([
        'id_siswa' => $studentId,
        'kategori' => $category,
        'waktu_mulai' => $date . ' ' . date('H:i:s'),
        'deskripsi' => $option,
        'catatan_tambahan' => $note !== '' ? $note : null,
        'foto' => $photoName,
    ]);

    respond(201, ['success' => true, 'message' => 'Aktivitas berhasil disimpan.']);
} catch (PDOException $exception) {
    if ($uploadPath !== null && is_file($uploadPath)) {
        unlink($uploadPath);
    }
    error_log($exception->getMessage());
    respond(500, ['success' => false, 'message' => 'Aktivitas gagal disimpan ke database.']);
}