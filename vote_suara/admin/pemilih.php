<?php
require_once __DIR__ . '/../config/koneksi.php';

// Proteksi Admin
requireAdmin();

// 1. Tangkap parameter pencarian
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// 2. Query data pemilih beserta status voting mereka
// Kita hitung ada berapa suara yang sudah masuk dari user tersebut
$sql = "SELECT u.*, 
               COUNT(v.id) AS total_suara_masuk
        FROM users u
        LEFT JOIN voting v ON u.id = v.user_id
        WHERE u.role = 'pemilih'";

$params = [];

if (!empty($search)) {
    $sql .= " AND (u.nama LIKE ? OR u.email LIKE ? OR u.no_hp LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " GROUP BY u.id ORDER BY u.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$daftar_pemilih = $stmt->fetchAll();

// 3. Hitung statistik ringkas
$total_pemilih = count($daftar_pemilih);
$sudah_voting_lengkap = 0;
$belum_voting = 0;

foreach ($daftar_pemilih as $p) {
    if ($p['total_suara_masuk'] >= 2) {
        $sudah_voting_lengkap++;
    } elseif ($p['total_suara_masuk'] == 0) {
        $belum_voting++;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pemilih — Admin Panel</title>
    
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
            width: 45px; height: 45px;
            border-radius: 50%;
            object-fit: cover;
            background: #e0e7ff;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.2rem;
            border: 2px solid #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
            <li><a href="<?= BASE_URL ?>/admin/calon.php"><i class="bi bi-people-fill"></i> Kelola Calon</a></li>
            <li><a href="<?= BASE_URL ?>/admin/pemilih.php" class="active"><i class="bi bi-person-badge-fill"></i> Data Pemilih</a></li>
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
            <h4 class="fw-bold mb-0">Data Pemilih</h4>
            <button class="btn btn-primary" onclick="document.getElementById('sidebar').classList.toggle('show')">
                <i class="bi bi-list"></i>
            </button>
        </div>

        <!-- Header Desktop -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <h2 class="fw-bold mb-1">Data Pemilih</h2>
                <p class="text-muted mb-0">Daftar seluruh warga yang terdaftar sebagai pemilih.</p>
            </div>
        </div>

        <?php if ($flash = getFlash()): ?>
            <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show shadow-sm border-0" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i> <?= $flash['message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Kartu Ringkasan -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 d-flex align-items-center justify-content-center">
                            <i class="bi bi-people-fill fs-4"></i>
                        </div>
                        <div>
                            <div class="fw-bold fs-4"><?= number_format($total_pemilih) ?></div>
                            <div class="text-muted small">Total Pemilih</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-success bg-opacity-10 text-success rounded-circle p-3 d-flex align-items-center justify-content-center">
                            <i class="bi bi-check-circle-fill fs-4"></i>
                        </div>
                        <div>
                            <div class="fw-bold fs-4"><?= number_format($sudah_voting_lengkap) ?></div>
                            <div class="text-muted small">Selesai Memilih (RT & RW)</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-danger bg-opacity-10 text-danger rounded-circle p-3 d-flex align-items-center justify-content-center">
                            <i class="bi bi-x-circle-fill fs-4"></i>
                        </div>
                        <div>
                            <div class="fw-bold fs-4"><?= number_format($belum_voting) ?></div>
                            <div class="text-muted small">Belum Memilih</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Pencarian -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3">
                <form method="GET" class="row g-2 align-items-center">
                    <div class="col-md-8">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari nama, email, atau no HP..." value="<?= e($search) ?>">
                        </div>
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100 fw-bold">Cari</button>
                        <?php if ($search): ?>
                            <a href="<?= BASE_URL ?>/admin/pemilih.php" class="btn btn-outline-secondary w-100">Reset</a>
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
                        <th width="25%">Nama Pemilih</th>
                        <th width="20%">Kontak</th>
                        <th width="15%">Alamat</th>
                        <th width="15%" class="text-center">Status Voting</th>
                        <th width="10%">Tgl Daftar</th>
                        <th width="10%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($daftar_pemilih)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Tidak ada data pemilih yang ditemukan.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($daftar_pemilih as $index => $row): ?>
                            <tr>
                                <td class="text-center fw-bold text-muted"><?= $index + 1 ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar-table">
                                            <?= strtoupper(substr($row['nama'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold"><?= e($row['nama']) ?></div>
                                            <div class="text-muted small"><?= e($row['email']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-muted small">
                                    <i class="bi bi-telephone-fill me-1"></i> <?= e($row['no_hp']) ?>
                                </td>
                                <td class="text-muted small"><?= e($row['alamat'] ?: '-') ?></td>
                                <td class="text-center">
                                    <?php if ($row['total_suara_masuk'] >= 2): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill border border-success border-opacity-25">
                                            <i class="bi bi-check2-all me-1"></i> Lengkap
                                        </span>
                                    <?php elseif ($row['total_suara_masuk'] == 1): ?>
                                        <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill border border-warning border-opacity-25">
                                            <i class="bi bi-hourglass-split me-1"></i> Sebagian
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill border border-danger border-opacity-25">
                                            <i class="bi bi-x-circle me-1"></i> Belum
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted small"><?= date('d/m/y', strtotime($row['created_at'])) ?></td>
                                <td class="text-center">
                                    <a href="<?= BASE_URL ?>/admin/hapus-pemilih.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger rounded-3" onclick="return confirm('Apakah Anda yakin ingin menghapus pemilih ini?\n\nSemua riwayat suara dari pemilih ini akan ikut terhapus.')" title="Hapus Pemilih">
                                        <i class="bi bi-trash"></i>
                                    </a>
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