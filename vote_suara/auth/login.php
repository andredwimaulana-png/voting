<?php
require_once __DIR__ . '/../config/koneksi.php';

// Kalau sudah login → redirect sesuai role
if (isLoggedIn()) {
    header('Location: ' . BASE_URL . (isAdmin() ? '/admin/dashboard.php' : '/pages/index.php'));
    exit;
}

$errors = [];
$old_email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']       ?? '';
    $old_email = $email;

    if ($email === '' || $password === '') {
        $errors[] = 'Email dan password wajib diisi.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            $errors[] = 'Email atau password salah.';
        } elseif ($user['status_verifikasi'] !== 'terverifikasi' && $user['role'] !== 'admin') {
            $errors[] = 'Akun Anda belum diverifikasi oleh admin.';
        } else {
            // --- Login sukses ---
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama']    = $user['nama'];
            $_SESSION['email']   = $user['email'];
            $_SESSION['role']    = $user['role'];

            setFlash('success', 'Selamat datang, ' . $user['nama'] . '!');

            header('Location: ' . BASE_URL . ($user['role'] === 'admin' ? '/admin/dashboard.php' : '/pages/index.php'));
            exit;
        }
    }
}

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login — Voting RT/RW</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg,#e0f2fe,#f0fdf4); min-height:100vh; }
        .card-auth { max-width: 420px; margin: 80px auto; border:0; border-radius:16px; box-shadow:0 12px 30px rgba(0,0,0,.08); }
    </style>
</head>
<body>
<div class="container">
    <div class="card card-auth">
        <div class="card-body p-4">
            <h3 class="mb-1 fw-bold">Login Pemilih</h3>
            <p class="text-muted small mb-4">Masuk untuk memberikan suara Anda.</p>

            <?php if ($flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?> small"><?= e($flash['message']) ?></div>
            <?php endif; ?>

            <?php if ($errors): ?>
                <div class="alert alert-danger small">
                    <ul class="mb-0">
                        <?php foreach ($errors as $err): ?>
                            <li><?= e($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" novalidate>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= e($old_email) ?>" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button class="btn btn-primary w-100 py-2">Login</button>
            </form>

            <hr class="my-4">
            <p class="text-center small mb-0">
                Belum punya akun? <a href="<?= BASE_URL ?>/auth/register.php">Daftar di sini</a>
            </p>
        </div>
    </div>
</div>
</body>
</html>