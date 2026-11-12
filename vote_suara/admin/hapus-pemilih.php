<?php
require_once __DIR__ . '/../config/koneksi.php';
requireAdmin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Pastikan admin tidak menghapus dirinya sendiri
if ($id === (int)$_SESSION['user_id']) {
    setFlash('danger', 'Anda tidak dapat menghapus akun Anda sendiri.');
    header('Location: ' . BASE_URL . '/admin/pemilih.php');
    exit;
}

try {
    // Ambil nama untuk pesan sukses
    $stmt = $pdo->prepare("SELECT nama FROM users WHERE id = ? AND role = 'pemilih'");
    $stmt->execute([$id]);
    $user = $stmt->fetch();

    if ($user) {
        // Hapus user (suara mereka otomatis terhapus karena CASCADE)
        $del = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $del->execute([$id]);
        setFlash('success', 'Akun pemilih <strong>' . e($user['nama']) . '</strong> berhasil dihapus.');
    } else {
        setFlash('danger', 'Data pemilih tidak ditemukan.');
    }
} catch (PDOException $e) {
    setFlash('danger', 'Gagal menghapus pemilih: ' . $e->getMessage());
}

header('Location: ' . BASE_URL . '/admin/pemilih.php');
exit;