<?php
require_once __DIR__ . '/../config/koneksi.php';

// Proteksi Admin
requireAdmin();

// 1. Tangkap parameter pencarian dan filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_jabatan = isset($_GET['jabatan']) ? $_GET['jabatan'] : '';

// 2. Bangun Query Dinamis
$sql = "SELECT * FROM calon WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (nama LIKE ? OR nomor_urut LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($filter_jabatan) && in_array($filter_jabatan, ['Ketua RT', 'Ketua RW'])) {
    $sql .= " AND jabatan = ?";
    $params[] = $filter_jabatan;
}

$sql .= " ORDER BY jabatan ASC, nomor_urut ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$daftar_calon = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Calon — Admin Panel</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts -->
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

        /* --- Sidebar Styling --- */
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
            transition: all 0.3s;
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
        .sidebar-menu {
            padding: 0 15px;
            list-style: none;
            flex-grow: 1;
            overflow-y: auto;
        }
        .sidebar-menu li { margin-bottom: 5px; }
        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: #64748b;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.2s;
        }
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: #e0e7ff;
            color: var(--primary-color);
        }
        .sidebar-footer {
            padding: 20px;
            border-top: 1px solid #e2e8f0;
        }

        /* --- Main Content Styling --- */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 30px;
            min-height: 100vh;
        }

        /* --- Tabel Styling --- */
        .table-custom {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.02);
            border: 1px solid #e2e8f0;
        }
        .table-custom th {
            background: #f8fafc;
            color: #64748b;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            padding: 16px 20px;
            border-bottom: 2px solid #e2e8f0;
        }
        .table-custom td {
            padding: 16px 20px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }
        .table-custom tbody tr:hover { background-color: #f8fafc; }

        .avatar-table {
            width: 50px; height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e2e8f0;
        }

        /* Responsive */
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
        
        <!-- Top Bar Mobile -->
        <div class="d-flex justify-content-between align-items-center mb-4 d-lg-none">
            <h4 class="fw-bold mb-0">Kelola Calon</h4>
            <button class="btn btn-primary" onclick="document.getElementById('sidebar').classList.toggle('show')">
                <i class="bi bi-list"></i>
            </button>
        </div>

        <!-- Header Desktop -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <h2 class="fw-bold mb-1">Kelola Data Calon</h2>
                <p class="text-muted mb-0">Tambah, edit, atau hapus data calon Ketua RT dan RW.</p>
            </div>
            <a href="<?= BASE_URL ?>/admin/tambah-calon.php" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm">
                <i class="bi bi-plus-lg me-1"></i> Tambah Calon Baru
            </a>
        </div>

        <?php if ($flash = getFlash()): ?>
            <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show shadow-sm border-0" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i> <?= e($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Filter & Pencarian -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3">
                <form method="GET" class="row g-2 align-items-center">
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari nama atau nomor urut..." value="<?= e($search) ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select name="jabatan" class="form-select">
                            <option value="">Semua Jabatan</option>
                            <option value="Ketua RT" <?= $filter_jabatan === 'Ketua RT' ? 'selected' : '' ?>>Ketua RT</option>
                            <option value="Ketua RW" <?= $filter_jabatan === 'Ketua RW' ? 'selected' : '' ?>>Ketua RW</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100 fw-bold">Terapkan</button>
                        <?php if ($search || $filter_jabatan): ?>
                            <a href="<?= BASE_URL ?>/admin/calon.php" class="btn btn-outline-secondary w-100">Reset</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabel Data -->
        <div class="table-responsive table-custom">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th width="5%" class="text-center">No</th>
                        <th width="10%" class="text-center">Foto</th>
                        <th width="10%" class="text-center">No. Urut</th>
                        <th width="30%">Nama Calon</th>
                        <th width="15%">Jabatan</th>
                        <th width="15%">Alamat</th>
                        <th width="15%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($daftar_calon)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Tidak ada data calon yang ditemukan.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($daftar_calon as $index => $row): ?>
                            <tr>
                                <td class="text-center fw-bold text-muted"><?= $index + 1 ?></td>
                                <td class="text-center">
                                    <?php 
                                        // PERBAIKAN URL GAMBAR: Menghapus '/../'
                                        $foto = (!empty($row['foto']) && file_exists(__DIR__ . '/../images/' . $row['foto'])) 
                                            ? BASE_URL . '/images/' . $row['foto'] 
                                            : 'https://ui-avatars.com/api/?name=' . urlencode($row['nama']) . '&background=e2e8f0&color=64748b';
                                    ?>
                                    <img src="<?= $foto ?>" alt="Foto" class="avatar-table">
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary fs-6 rounded-circle p-2" style="width: 35px; height: 35px; display: inline-flex; align-items: center; justify-content: center;">
                                        <?= e($row['nomor_urut']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold"><?= e($row['nama']) ?></div>
                                    <div class="text-muted small text-truncate" style="max-width: 250px;"><?= e($row['deskripsi']) ?></div>
                                </td>
                                <td>
                                    <span class="badge <?= $row['jabatan'] === 'Ketua RT' ? 'bg-info bg-opacity-10 text-info' : 'bg-primary bg-opacity-10 text-primary' ?> px-3 py-2 rounded-pill fw-bold">
                                        <?= e($row['jabatan']) ?>
                                    </span>
                                </td>
                                <td class="text-muted small"><?= e($row['alamat']) ?></td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="<?= BASE_URL ?>/admin/edit-calon.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning text-white rounded-3" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/admin/hapus-calon.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger rounded-3" onclick="return confirm('Apakah Anda yakin ingin menghapus calon ini?\n\nSemua suara yang masuk untuk calon ini juga akan terhapus.')" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>