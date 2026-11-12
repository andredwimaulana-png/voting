<?php
require_once __DIR__ . '/../config/koneksi.php';

// Proteksi Admin
requireAdmin();

// 1. Ambil ID dari URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 2. Ambil data calon dari database
$stmt = $pdo->prepare("SELECT * FROM calon WHERE id = ?");
$stmt->execute([$id]);
$calon = $stmt->fetch();

if (!$calon) {
    setFlash('danger', 'Data calon tidak ditemukan.');
    header('Location: ' . BASE_URL . '/admin/calon.php');
    exit;
}

$errors = [];
$old = $calon; // Simpan data lama sebagai default form

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Tangkap Data Teks
    $old['nomor_urut']    = trim($_POST['nomor_urut'] ?? '');
    $old['nama']          = trim($_POST['nama'] ?? '');
    $old['jabatan']       = $_POST['jabatan'] ?? 'Ketua RT';
    $old['alamat']        = trim($_POST['alamat'] ?? '');
    $old['visi']          = trim($_POST['visi'] ?? '');
    $old['misi']          = trim($_POST['misi'] ?? '');
    $old['program_kerja'] = trim($_POST['program_kerja'] ?? '');
    $old['deskripsi']     = trim($_POST['deskripsi'] ?? '');

    // 2. Validasi Teks
    if (empty($old['nomor_urut'])) $errors[] = 'Nomor urut wajib diisi.';
    if (empty($old['nama'])) $errors[] = 'Nama calon wajib diisi.';
    if (!in_array($old['jabatan'], ['Ketua RT', 'Ketua RW'])) $errors[] = 'Jabatan tidak valid.';

    // Cek duplikasi Nomor Urut (kecuali milik ID ini sendiri)
    if (empty($errors)) {
        $stmt_cek = $pdo->prepare("SELECT id FROM calon WHERE nomor_urut = ? AND jabatan = ? AND id != ?");
        $stmt_cek->execute([$old['nomor_urut'], $old['jabatan'], $id]);
        if ($stmt_cek->fetch()) {
            $errors[] = 'Nomor urut ' . $old['nomor_urut'] . ' sudah dipakai calon lain untuk jabatan ' . $old['jabatan'] . '.';
        }
    }

    // 3. Proses Upload Foto Baru (Jika ada)
    $nama_file_foto = $calon['foto']; // Default pakai foto lama
    
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $file_tmp  = $_FILES['foto']['tmp_name'];
        $file_name = $_FILES['foto']['name'];
        $file_size = $_FILES['foto']['size'];
        $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (!in_array($file_ext, $allowed_ext)) {
            $errors[] = 'Format foto harus JPG, JPEG, PNG, atau WEBP.';
        } elseif ($file_size > 2 * 1024 * 1024) {
            $errors[] = 'Ukuran foto maksimal 2MB.';
        } else {
            // Tentukan lokasi folder images
            $folder_upload = __DIR__ . '/../images/';
            
            // Buat nama file unik
            $nama_file_baru = 'calon_' . time() . '_' . uniqid() . '.' . $file_ext;
            $tujuan_upload = $folder_upload . $nama_file_baru;

            // Pastikan folder images ada
            if (!is_dir($folder_upload)) {
                mkdir($folder_upload, 0777, true);
            }

            if (move_uploaded_file($file_tmp, $tujuan_upload)) {
                // Jika berhasil upload, hapus foto lama (jika ada dan file fisiknya ada)
                if (!empty($calon['foto']) && file_exists($folder_upload . $calon['foto'])) {
                    unlink($folder_upload . $calon['foto']);
                }
                $nama_file_foto = $nama_file_baru; // Ganti dengan nama file baru
            } else {
                $errors[] = 'Gagal mengunggah foto baru.';
            }
        }
    }

    // 4. Update ke Database
    if (empty($errors)) {
        try {
            $sql = "UPDATE calon SET 
                        nomor_urut = ?, nama = ?, jabatan = ?, foto = ?, 
                        alamat = ?, visi = ?, misi = ?, program_kerja = ?, deskripsi = ? 
                    WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $old['nomor_urut'], $old['nama'], $old['jabatan'], $nama_file_foto,
                $old['alamat'], $old['visi'], $old['misi'], 
                $old['program_kerja'], $old['deskripsi'], $id
            ]);

            setFlash('success', 'Data calon berhasil diperbarui!');
            header('Location: ' . BASE_URL . '/admin/calon.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Gagal memperbarui database: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Calon — Admin Panel</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #4f46e5;
            --dark-color: #1e293b;
            --light-bg: #f8fafc;
            --sidebar-width: 260px;
        }
        
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--light-bg);
            color: var(--dark-color);
            overflow-x: hidden;
        }

        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0; left: 0;
            background: white;
            border-right: 1px solid #e2e8f0;
            z-index: 1000;
            display: flex;
            flex-direction: column;
        }
        .sidebar-brand {
            padding: 24px;
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary-color);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sidebar-menu { padding: 0 15px; list-style: none; flex-grow: 1; overflow-y: auto; }
        .sidebar-menu li { margin-bottom: 5px; }
        .sidebar-menu a {
            display: flex; align-items: center; gap: 12px; padding: 12px 16px;
            color: #64748b; text-decoration: none; border-radius: 12px; font-weight: 600; transition: all 0.2s;
        }
        .sidebar-menu a:hover, .sidebar-menu a.active { background: #e0e7ff; color: var(--primary-color); }
        .sidebar-footer { padding: 20px; border-top: 1px solid #e2e8f0; }

        .main-content { margin-left: var(--sidebar-width); padding: 30px; min-height: 100vh; }

        .card-form {
            background: white;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 15px rgba(0,0,0,0.02);
        }
        .form-label { font-weight: 600; color: var(--dark-color); font-size: 0.9rem; }
        .form-control, .form-select {
            border-radius: 10px;
            padding: 12px 16px;
            border: 1px solid #cbd5e1;
            font-size: 0.95rem;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }
        
        .preview-container {
            border: 2px dashed #cbd5e1;
            border-radius: 16px;
            padding: 20px;
            text-align: center;
            background: #f8fafc;
            transition: all 0.3s;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 250px;
        }
        .preview-container:hover { border-color: var(--primary-color); background: #f1f5f9; }
        .preview-img {
            max-width: 100%;
            max-height: 200px;
            border-radius: 12px;
            object-fit: cover;
            display: block;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        @media (max-width: 991.98px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; }
        }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <div class="sidebar" id="sidebar">
        <a href="<?= BASE_URL ?>/admin/dashboard.php" class="sidebar-brand">
            <i class="bi bi-shield-lock-fill"></i> Admin Panel
        </a>
        
        <ul class="sidebar-menu">
            <li><a href="<?= BASE_URL ?>/admin/dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li><a href="<?= BASE_URL ?>/admin/calon.php" class="active"><i class="bi bi-people-fill"></i> Kelola Calon</a></li>
            <li><a href="<?= BASE_URL ?>/admin/pemilih.php"><i class="bi bi-person-badge-fill"></i> Data Pemilih</a></li>
            <li><a href="<?= BASE_URL ?>/admin/hasil-voting.php"><i class="bi bi-bar-chart-fill"></i> Hasil Voting</a></li>
            <li><a href="<?= BASE_URL ?>/admin/riwayat-voting.php"><i class="bi bi-clock-history"></i> Riwayat Voting</a></li>
            <li><a href="<?= BASE_URL ?>/admin/periode.php"><i class="bi bi-calendar-event-fill"></i> Periode Voting</a></li>
        </ul>

        <div class="sidebar-footer">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px;">
                    <?= strtoupper(substr($_SESSION['nama'], 0, 1)) ?>
                </div>
                <div>
                    <div class="fw-bold small"><?= e($_SESSION['nama']) ?></div>
                    <div class="text-muted" style="font-size: 0.75rem;">Administrator</div>
                </div>
            </div>
            <a href="<?= BASE_URL ?>/auth/logout.php" class="btn btn-outline-danger w-100 btn-sm rounded-pill" onclick="return confirm('Yakin ingin logout?')">
                <i class="bi bi-box-arrow-right me-1"></i> Logout
            </a>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        
        <div class="d-flex justify-content-between align-items-center mb-4 d-lg-none">
            <h4 class="fw-bold mb-0">Edit Calon</h4>
            <button class="btn btn-primary" onclick="document.getElementById('sidebar').classList.toggle('show')">
                <i class="bi bi-list"></i>
            </button>
        </div>

        <div class="d-flex align-items-center gap-3 mb-4">
            <a href="<?= BASE_URL ?>/admin/calon.php" class="btn btn-light border rounded-circle p-2" title="Kembali">
                <i class="bi bi-arrow-left fs-5"></i>
            </a>
            <div>
                <h2 class="fw-bold mb-1">Edit Data Calon</h2>
                <p class="text-muted mb-0">Perbarui informasi profil calon.</p>
            </div>
        </div>

        <?php if ($errors): ?>
            <div class="alert alert-danger shadow-sm border-0">
                <ul class="mb-0">
                    <?php foreach ($errors as $err): ?>
                        <li><?= e($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="row g-4">
                
                <!-- KOLOM KIRI -->
                <div class="col-lg-4">
                    <div class="card card-form p-4 mb-4">
                        <h5 class="fw-bold mb-4 border-bottom pb-3">Info Utama</h5>
                        
                        <div class="mb-3">
                            <label class="form-label">Jabatan <span class="text-danger">*</span></label>
                            <select name="jabatan" class="form-select" required>
                                <option value="Ketua RT" <?= $old['jabatan'] === 'Ketua RT' ? 'selected' : '' ?>>Ketua RT</option>
                                <option value="Ketua RW" <?= $old['jabatan'] === 'Ketua RW' ? 'selected' : '' ?>>Ketua RW</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nomor Urut <span class="text-danger">*</span></label>
                            <input type="text" name="nomor_urut" class="form-control" value="<?= e($old['nomor_urut']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="nama" class="form-control" value="<?= e($old['nama']) ?>" required>
                        </div>

                        <div class="mb-0">
                            <label class="form-label">Alamat / Wilayah</label>
                            <input type="text" name="alamat" class="form-control" value="<?= e($old['alamat']) ?>">
                        </div>
                    </div>

                    <!-- FOTO -->
                    <div class="card card-form p-4">
                        <h5 class="fw-bold mb-4 border-bottom pb-3">Foto Calon</h5>
                        
                        <div class="preview-container mb-3" onclick="document.getElementById('inputFoto').click()" style="cursor: pointer;">
                            <?php 
                                // Cek apakah file ada di folder images/ (sesuai request Anda)
                                $path_fisik = __DIR__ . '/../images/' . $calon['foto'];
                                $tampil_foto = (!empty($calon['foto']) && file_exists($path_fisik)) 
                                    ? BASE_URL . '/images/' . $calon['foto'] 
                                    : 'https://ui-avatars.com/api/?name=' . urlencode($calon['nama']) . '&background=e2e8f0&color=64748b&size=300';
                            ?>
                            <img src="<?= $tampil_foto ?>" alt="Foto Saat Ini" id="previewFoto" class="preview-img mb-2">
                            <span class="text-muted small mt-2"><i class="bi bi-camera me-1"></i> Klik untuk ganti foto</span>
                        </div>
                        
                        <input type="file" name="foto" id="inputFoto" class="form-control" accept="image/jpeg, image/png, image/webp" onchange="tampilkanPreview(this)">
                        <div class="form-text small mt-1">Biarkan kosong jika tidak ingin mengubah foto. (Maks. 2MB)</div>
                    </div>
                </div>

                <!-- KOLOM KANAN -->
                <div class="col-lg-8">
                    <div class="card card-form p-4 h-100">
                        <h5 class="fw-bold mb-4 border-bottom pb-3">Visi, Misi & Profil</h5>

                        <div class="mb-3">
                            <label class="form-label">Visi</label>
                            <textarea name="visi" class="form-control" rows="3"><?= e($old['visi']) ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Misi</label>
                            <textarea name="misi" class="form-control" rows="4"><?= e($old['misi']) ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Program Kerja</label>
                            <textarea name="program_kerja" class="form-control" rows="3"><?= e($old['program_kerja']) ?></textarea>
                        </div>

                        <div class="mb-0">
                            <label class="form-label">Deskripsi / Profil Singkat</label>
                            <textarea name="deskripsi" class="form-control" rows="3"><?= e($old['deskripsi']) ?></textarea>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Tombol Aksi -->
            <div class="d-flex justify-content-end gap-2 mt-4 mb-5">
                <a href="<?= BASE_URL ?>/admin/calon.php" class="btn btn-light border px-4 py-2 fw-bold rounded-3">Batal</a>
                <button type="submit" class="btn btn-primary px-5 py-2 fw-bold rounded-3 shadow-sm">
                    <i class="bi bi-save me-1"></i> Perbarui Data
                </button>
            </div>
        </form>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function tampilkanPreview(input) {
            const preview = document.getElementById('previewFoto');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                }
                
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>