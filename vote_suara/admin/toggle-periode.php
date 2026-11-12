<?php
require_once __DIR__ . '/../config/koneksi.php';
requireAdmin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$status_baru = $_GET['status'] ?? '';

if ($id <= 0 || !in_array($status_baru, ['Aktif', 'Nonaktif'])) {
    setFlash('danger', 'Aksi tidak valid.');
    header('Location: ' . BASE_URL . '/admin/periode.php');
    exit;
}

try {
    // Jika mau diaktifkan, matikan dulu semua periode lain
    if ($status_baru === 'Aktif') {
        $pdo->query("UPDATE periode_voting SET status = 'Nonaktif'");
    }

    $stmt = $pdo->prepare("UPDATE periode_voting SET status = ? WHERE id = ?");
    $stmt->execute([$status_baru, $id]);

    $pesan = $status_baru === 'Aktif' ? 'Voting berhasil DIBUKA!' : 'Voting berhasil DITUTUP.';
    setFlash('success', $pesan);
} catch (PDOException $e) {
    setFlash('danger', 'Gagal mengubah status: ' . $e->getMessage());
}

header('Location: ' . BASE_URL . '/admin/periode.php');
exit;