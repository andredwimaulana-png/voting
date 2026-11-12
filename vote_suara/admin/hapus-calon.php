<?php
require_once __DIR__ . '/../config/koneksi.php';

// Proteksi Admin
requireAdmin();

// 1. Ambil ID dari URL dan pastikan itu angka
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    setFlash('danger', 'ID calon tidak valid.');
    header('Location: ' . BASE_URL . '/admin/calon.php');
    exit;
}

try {
    // 2. Ambil data calon terlebih dahulu untuk mendapatkan nama file fotonya
    $stmt = $pdo->prepare("SELECT nama, foto FROM calon WHERE id = ?");
    $stmt->execute([$id]);
    $calon = $stmt->fetch();

    if (!$calon) {
        setFlash('danger', 'Data calon tidak ditemukan atau sudah dihapus.');
        header('Location: ' . BASE_URL . '/admin/calon.php');
        exit;
    }

    // 3. Hapus file foto fisik dari folder images/ (jika ada)
    if (!empty($calon['foto'])) {
        $path_foto = __DIR__ . '/../images/' . $calon['foto'];
        if (file_exists($path_foto)) {
            unlink($path_foto); // Hapus file
        }
    }

    // 4. Hapus data dari database
    // (Catatan: Karena kita menggunakan ON DELETE CASCADE pada tabel voting, 
    // semua suara yang masuk untuk calon ini akan otomatis terhapus juga)
    $stmt_delete = $pdo->prepare("DELETE FROM calon WHERE id = ?");
    $stmt_delete->execute([$id]);

    setFlash('success', 'Data calon <strong>' . e($calon['nama']) . '</strong> berhasil dihapus.');
    header('Location: ' . BASE_URL . '/admin/calon.php');
    exit;

} catch (PDOException $e) {
    setFlash('danger', 'Gagal menghapus data: ' . $e->getMessage());
    header('Location: ' . BASE_URL . '/admin/calon.php');
    exit;
}