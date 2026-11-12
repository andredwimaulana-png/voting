<?php
require_once __DIR__ . '/../config/koneksi.php';
requireAdmin();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_periode = trim($_POST['nama_periode'] ?? '');
    $tanggal_mulai = $_POST['tanggal_mulai'] ?? '';
    $tanggal_selesai = $_POST['tanggal_selesai'] ?? '';
    $status = $_POST['status'] ?? 'Nonaktif';

    if (empty($nama_periode)) $errors[] = 'Nama periode wajib diisi.';
    if (empty($tanggal_mulai)) $errors[] = 'Tanggal mulai wajib diisi.';
    if (empty($tanggal_selesai)) $errors[] = 'Tanggal selesai wajib diisi.';
    if (strtotime($tanggal_selesai) <= strtotime($tanggal_mulai)) {
        $errors[] = 'Tanggal selesai harus lebih besar dari tanggal mulai.';
    }

    if (empty($errors)) {
        try {
            // Jika status diset Aktif, matikan periode lain dulu
            if ($status === 'Aktif') {
                $pdo->query("UPDATE periode_voting SET status = 'Nonaktif'");
            }

            $sql = "INSERT INTO periode_voting (nama_periode, tanggal_mulai, tanggal_selesai, status) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nama_periode, $tanggal_mulai, $tanggal_selesai, $status]);

            setFlash('success', 'Periode voting berhasil dibuat!');
            header('Location: ' . BASE_URL . '/admin/periode.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Gagal menyimpan: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Periode — Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f8fafc; }
        .card-form { max-width: 600px; margin: 50px auto; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: none; }
    </style>
</head>
<body>
<div class="container">
    <div class="card card-form">
        <div class="card-body p-4">
            <div class="d-flex align-items-center gap-3 mb-4 border-bottom pb-3">
                <a href="<?= BASE_URL ?>/admin/periode.php" class="btn btn-light border rounded-circle p-2"><i class="bi bi-arrow-left"></i></a>
                <h4 class="fw-bold mb-0">Buat Periode Baru</h4>
            </div>

            <?php if ($errors): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0"><?php foreach ($errors as $e) echo "<li>$e</li>"; ?></ul>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Nama Periode</label>
                    <input type="text" name="nama_periode" class="form-control" placeholder="Contoh: Pemilihan RT 05 2026" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Tanggal & Waktu Mulai</label>
                        <input type="datetime-local" name="tanggal_mulai" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Tanggal & Waktu Selesai</label>
                        <input type="datetime-local" name="tanggal_selesai" class="form-control" required>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold">Status Awal</label>
                    <select name="status" class="form-select">
                        <option value="Nonaktif">Nonaktif (Simpan Dulu)</option>
                        <option value="Aktif">Aktif (Langsung Buka Voting)</option>
                    </select>
                    <div class="form-text small">Jika memilih Aktif, periode lain yang sedang aktif akan otomatis ditutup.</div>
                </div>
                <button type="submit" class="btn btn-primary w-100 fw-bold py-2 rounded-3">Simpan Periode</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>