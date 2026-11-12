<?php
require_once __DIR__ . '/../config/koneksi.php';

// Proteksi Admin
requireAdmin();

// 1. Ambil semua data periode
$stmt = $pdo->query("SELECT * FROM periode_voting ORDER BY id DESC");
$daftar_periode = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Periode Voting — Admin Panel</title>
    
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

        /* --- Main Content --- */
        .main-content { margin-left: var(--sidebar-width); padding: 30px; min-height: 100vh; }

        /* --- Card --- */
        .card-custom {
            background: white;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 15px rgba(0,0,0,0.02);
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

        /* Animasi Pulse untuk status aktif */
        @keyframes pulse-green {
            0% { box-shadow: 0 0 0 0 rgba(25, 135, 84, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(25, 135, 84, 0); }
            100% { box-shadow: 0 0 0 0 rgba(25, 135, 84, 0); }
        }
        .badge-aktif {
            animation: pulse-green 2s infinite;
            font-size: 0.85rem;
            padding: 8px 16px;
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
            <li><a href="<?= BASE_URL ?>/admin/calon.php"><i class="bi bi-people-fill"></i> Kelola Calon</a></li>
            <li><a href="<?= BASE_URL ?>/admin/pemilih.php"><i class="bi bi-person-badge-fill"></i> Data Pemilih</a></li>
            <li><a href="<?= BASE_URL ?>/admin/hasil-voting.php"><i class="bi bi-bar-chart-fill"></i> Hasil Voting</a></li>
            <li><a href="<?= BASE_URL ?>/admin/riwayat-voting.php"><i class="bi bi-clock-history"></i> Riwayat Voting</a></li>
            <li><a href="<?= BASE_URL ?>/admin/periode.php" class="active"><i class="bi bi-calendar-event-fill"></i> Periode Voting</a></li>
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
            <h4 class="fw-bold mb-0">Periode Voting</h4>
            <button class="btn btn-primary" onclick="document.getElementById('sidebar').classList.toggle('show')">
                <i class="bi bi-list"></i>
            </button>
        </div>

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <h2 class="fw-bold mb-1">Kelola Periode Voting</h2>
                <p class="text-muted mb-0">Atur jadwal dan buka/tutup bilik suara pemilihan.</p>
            </div>
            <a href="<?= BASE_URL ?>/admin/tambah-periode.php" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm">
                <i class="bi bi-plus-lg me-1"></i> Buat Periode Baru
            </a>
        </div>

        <?php if ($flash = getFlash()): ?>
            <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show shadow-sm border-0" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i> <?= $flash['message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Tabel Data -->
        <div class="table-responsive card-custom">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th width="5%" class="text-center">No</th>
                        <th width="30%">Nama Periode</th>
                        <th width="20%">Mulai</th>
                        <th width="20%">Selesai</th>
                        <th width="10%" class="text-center">Status</th>
                        <th width="15%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($daftar_periode)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
                                Belum ada periode voting yang dibuat.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($daftar_periode as $index => $row): ?>
                            <tr>
                                <td class="text-center fw-bold text-muted"><?= $index + 1 ?></td>
                                <td>
                                    <div class="fw-bold fs-6"><?= e($row['nama_periode']) ?></div>
                                </td>
                                <td class="text-muted">
                                    <i class="bi bi-calendar-check text-primary me-1"></i>
                                    <?= date('d M Y, H:i', strtotime($row['tanggal_mulai'])) ?> WIB
                                </td>
                                <td class="text-muted">
                                    <i class="bi bi-calendar-x text-danger me-1"></i>
                                    <?= date('d M Y, H:i', strtotime($row['tanggal_selesai'])) ?> WIB
                                </td>
                                <td class="text-center">
                                    <?php if ($row['status'] === 'Aktif'): ?>
                                        <span class="badge bg-success rounded-pill badge-aktif">
                                            <i class="bi bi-broadcast me-1"></i> Aktif
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary rounded-pill px-3 py-2">
                                            <i class="bi bi-stop-circle me-1"></i> Nonaktif
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <?php if ($row['status'] === 'Aktif'): ?>
                                            <a href="<?= BASE_URL ?>/admin/toggle-periode.php?id=<?= $row['id'] ?>&status=Nonaktif" class="btn btn-sm btn-warning text-white rounded-3 px-3 fw-bold" title="Tutup Voting" onclick="return confirm('Apakah Anda yakin ingin MENUTUP voting ini?\n\nMasyarakat tidak akan bisa memberikan suara lagi.')">
                                                <i class="bi bi-pause-circle me-1"></i> Tutup
                                            </a>
                                        <?php else: ?>
                                            <a href="<?= BASE_URL ?>/admin/toggle-periode.php?id=<?= $row['id'] ?>&status=Aktif" class="btn btn-sm btn-success rounded-3 px-3 fw-bold" title="Buka Voting" onclick="return confirm('Apakah Anda yakin ingin MEMBUKA voting ini?\n\nMasyarakat akan bisa langsung memberikan suara.')">
                                                <i class="bi bi-play-circle me-1"></i> Buka
                                            </a>
                                        <?php endif; ?>
                                        
                                        <a href="<?= BASE_URL ?>/admin/hapus-periode.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger rounded-3" title="Hapus" onclick="return confirm('Hapus periode ini?\n\nPastikan Anda sudah menutupnya. Data voting tidak akan terhapus, hanya periode ini yang hilang.')">
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

        <div class="alert alert-info border-0 shadow-sm mt-4">
            <i class="bi bi-info-circle-fill me-2"></i> <strong>Informasi:</strong> Hanya boleh ada <b>satu periode</b> yang berstatus "Aktif" dalam satu waktu. Jika Anda membuka satu periode, sistem akan otomatis menutup periode lainnya.
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>