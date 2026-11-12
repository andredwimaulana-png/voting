<?php
require_once __DIR__ . '/../config/koneksi.php';

// Hapus semua session user, tapi simpan flash
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();

// Mulai session baru hanya untuk flash
session_start();
setFlash('success', 'Anda telah logout.');
header('Location: ' . BASE_URL . '/auth/login.php');
exit;