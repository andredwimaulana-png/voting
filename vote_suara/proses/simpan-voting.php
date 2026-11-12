<?php
require_once __DIR__ . '/../config/koneksi.php';

// 1. Pastikan yang mengakses adalah pemilih yang sudah login
if (!isLoggedIn() || isAdmin()) {
    setFlash('danger', 'Akses ditolak. Anda harus login sebagai pemilih.');
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

// 2. Pastikan request yang masuk adalah metode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/pages/voting.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// 3. Cek Status Periode Voting
$stmt_periode = $pdo->query("SELECT id FROM periode_voting WHERE status = 'Aktif' LIMIT 1");
if (!$stmt_periode->fetch()) {
    setFlash('danger', 'Gagal! Periode voting sudah ditutup atau belum dimulai.');
    header('Location: ' . BASE_URL . '/pages/voting.php');
    exit;
}

// 4. Tangkap data yang dikirim
$calon_rt_id = $_POST['calon_rt'] ?? null;
$calon_rw_id = $_POST['calon_rw'] ?? null;

// Validasi minimal harus ada satu pilihan (jika memang baru memilih salah satu)
if (!$calon_rt_id && !$calon_rw_id) {
    setFlash('warning', 'Anda belum memilih calon apapun.');
    header('Location: ' . BASE_URL . '/pages/voting.php');
    exit;
}

try {
    // ========================================================
    // MULAI TRANSAKSI DATABASE (Mencegah Race Condition)
    // ========================================================
    $pdo->beginTransaction();

    // 5. Cek rekam jejak voting user ini di dalam database
    // (Untuk mencegah jika user mencoba me-refresh halaman submit)
    $stmt_cek = $pdo->prepare("SELECT jabatan FROM voting WHERE user_id = ? FOR UPDATE");
    $stmt_cek->execute([$user_id]);
    $sudah_voting = $stmt_cek->fetchAll(PDO::FETCH_COLUMN);

    $sukses = false;

    // 6. Proses Simpan Suara Ketua RT
    if ($calon_rt_id && !in_array('Ketua RT', $sudah_voting)) {
        // Validasi: Pastikan ID calon yang dikirim benar-benar ada dan jabatannya Ketua RT
        $stmt_val = $pdo->prepare("SELECT id FROM calon WHERE id = ? AND jabatan = 'Ketua RT'");
        $stmt_val->execute([$calon_rt_id]);
        
        if ($stmt_val->fetch()) {
            $insert = $pdo->prepare("INSERT INTO voting (user_id, calon_id, jabatan) VALUES (?, ?, 'Ketua RT')");
            $insert->execute([$user_id, $calon_rt_id]);
            $sukses = true;
        }
    }

    // 7. Proses Simpan Suara Ketua RW
    if ($calon_rw_id && !in_array('Ketua RW', $sudah_voting)) {
        // Validasi: Pastikan ID calon yang dikirim benar-benar ada dan jabatannya Ketua RW
        $stmt_val = $pdo->prepare("SELECT id FROM calon WHERE id = ? AND jabatan = 'Ketua RW'");
        $stmt_val->execute([$calon_rw_id]);
        
        if ($stmt_val->fetch()) {
            $insert = $pdo->prepare("INSERT INTO voting (user_id, calon_id, jabatan) VALUES (?, ?, 'Ketua RW')");
            $insert->execute([$user_id, $calon_rw_id]);
            $sukses = true;
        }
    }

    // 8. Cek jika tidak ada data yang berhasil disimpan
    if (!$sukses) {
        // Rollback (Batalkan semua proses simpan)
        $pdo->rollBack();
        setFlash('warning', 'Sistem mendeteksi Anda sudah memilih. Suara tidak dapat diubah.');
        header('Location: ' . BASE_URL . '/pages/voting.php');
        exit;
    }

    // ========================================================
    // JIKA SEMUA AMAN, SIMPAN PERMANEN KE DATABASE
    // ========================================================
    $pdo->commit();

    // Set pesan sukses
    setFlash('success', 'Terima kasih! Suara Anda berhasil disimpan.');
    header('Location: ' . BASE_URL . '/pages/klasemen.php');
    exit;

} catch (PDOException $e) {
    // Jika ada error database (misal: duplikat UNIQUE KEY), batalkan semua
    $pdo->rollBack();
    
    // Cek jika error disebabkan oleh pelanggaran UNIQUE KEY (kode error 23000)
    if ($e->getCode() == 23000) {
        setFlash('danger', 'Gagal! Anda sudah pernah memberikan suara untuk kategori ini.');
    } else {
        setFlash('danger', 'Terjadi kesalahan sistem: ' . $e->getMessage());
    }
    
    header('Location: ' . BASE_URL . '/pages/voting.php');
    exit;
}