<?php
require_once __DIR__ . '/../config/koneksi.php';

// Kalau sudah login, redirect ke beranda
if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/pages/index.php');
    exit;
}

$errors = [];
$old = ['nama' => '', 'email' => '', 'no_hp' => '', 'alamat' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $no_hp    = trim($_POST['no_hp']    ?? '');
    $alamat   = trim($_POST['alamat']   ?? '');
    $password = $_POST['password']       ?? '';
    $konfirm  = $_POST['konfirmasi']     ?? '';

    $old = compact('nama', 'email', 'no_hp', 'alamat');

    // --- Validasi ---
    if ($nama === '')                       $errors[] = 'Nama lengkap wajib diisi.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email tidak valid.';
    if (!preg_match('/^[0-9+\-\s]{8,20}$/', $no_hp)) $errors[] = 'Nomor HP tidak valid (8–20 digit).';
    if (strlen($password) < 6)              $errors[] = 'Password minimal 6 karakter.';
    if ($password !== $konfirm)             $errors[] = 'Konfirmasi password tidak cocok.';

    // --- Cek email duplikat ---
    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Email sudah terdaftar. Silakan gunakan email lain.';
        }
    }

    // --- Simpan ---
    if (empty($errors)) {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare(
                'INSERT INTO users (nama, email, no_hp, alamat, password, role, status_verifikasi)
                 VALUES (?, ?, ?, ?, ?, "pemilih", "terverifikasi")'
            );
            $stmt->execute([$nama, $email, $no_hp, $alamat, $hash]);

            setFlash('success', 'Registrasi berhasil! Silakan login.');
            header('Location: ' . BASE_URL . '/auth/login.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Gagal menyimpan data: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registrasi Pemilih — Voting RT/RW</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg,#e0f2fe,#f0fdf4); min-height:100vh; }
        .card-auth { max-width: 520px; margin: 40px auto; border:0; border-radius:16px; box-shadow:0 12px 30px rgba(0,0,0,.08); }
    </style>
</head>
<body>
<div class="container">
    <div class="card card-auth">
        <div class="card-body p-4">
            <h3 class="mb-1 fw-bold">Registrasi Pemilih</h3>
            <p class="text-muted small mb-4">Buat akun untuk ikut serta dalam pemilihan.</p>

            <?php if ($errors): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0 small">
                        <?php foreach ($errors as $err): ?>
                            <li><?= e($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" novalidate>
                <div class="mb-3">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="nama" class="form-control" value="<?= e($old['nama']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= e($old['email']) ?>" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">No. HP</label>
                        <input type="text" name="no_hp" class="form-control" value="<?= e($old['no_hp']) ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Alamat / No. Rumah</label>
                        <input type="text" name="alamat" class="form-control" value="<?= e($old['alamat']) ?>">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Konfirmasi Password</label>
                        <input type="password" name="konfirmasi" class="form-control" required>
                    </div>
                </div>
                <button class="btn btn-primary w-100 py-2">Daftar</button>
            </form>

            <hr class="my-4">
            <p class="text-center small mb-0">
                Sudah punya akun? <a href="<?= BASE_URL ?>/auth/login.php">Login di sini</a>
            </p>
        </div>
    </div>
</div>
</body>
</html> 