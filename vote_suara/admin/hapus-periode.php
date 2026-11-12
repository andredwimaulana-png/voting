<?php
require_once __DIR__ . '/../config/koneksi.php';
requireAdmin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    try {
        $stmt = $pdo->prepare("DELETE FROM periode_voting WHERE id = ?");
        $stmt->execute([$id]);
        setFlash('success', 'Periode voting berhasil dihapus.');
    } catch (PDOException $e) {
        setFlash('danger', 'Gagal menghapus periode: ' . $e->getMessage());
    }
} else {
    setFlash('danger', 'ID tidak valid.');
}

header('Location: ' . BASE_URL . '/admin/periode.php');
exit;