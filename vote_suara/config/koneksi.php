<?php
// =========================================================
// Konfigurasi Database & Helper Global
// =========================================================

// --- Session start (aman untuk semua halaman) ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- Konfigurasi DB ---
define('DB_HOST', 'localhost');
define('DB_NAME', 'db_voting_rt_rw');
define('DB_USER', 'root');
define('DB_PASS', '');          // sesuaikan dengan XAMPP Anda
define('BASE_URL', '/vote_suara'); // sesuaikan nama folder di htdocs

// --- Koneksi PDO ---
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die('Koneksi database gagal: ' . htmlspecialchars($e->getMessage()));
}

// =========================================================
// Helper Functions
// =========================================================

/** Cek apakah user sudah login */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

/** Cek apakah user adalah admin */
function isAdmin(): bool {
    return isLoggedIn() && ($_SESSION['role'] ?? '') === 'admin';
}

/** Redirect jika belum login */
function requireLogin(string $redirect = '/auth/login.php'): void {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . $redirect);
        exit;
    }
}

/** Redirect jika bukan admin */
function requireAdmin(): void {
    if (!isAdmin()) {
        header('Location: ' . BASE_URL . '/auth/login.php');
        exit;
    }
}

/** Ambil flash message */
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

/** Escape HTML */
function e(?string $str): string {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}