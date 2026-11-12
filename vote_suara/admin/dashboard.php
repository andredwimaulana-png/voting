    <?php
require_once __DIR__ . '/../config/koneksi.php';

// Proteksi: Hanya Admin yang boleh masuk
requireAdmin();

// 1. Ambil Data Statistik
try {
    $total_calon_rt = $pdo->query("SELECT COUNT(*) FROM calon WHERE jabatan = 'Ketua RT'")->fetchColumn();
    $total_calon_rw = $pdo->query("SELECT COUNT(*) FROM calon WHERE jabatan = 'Ketua RW'")->fetchColumn();
    
    $total_pemilih = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'pemilih'")->fetchColumn();
    $total_suara_masuk = $pdo->query("SELECT COUNT(*) FROM voting")->fetchColumn();
    
    // Hitung persentase partisipasi (Total suara / (Total pemilih * 2)) * 100
    // Kenapa dikali 2? Karena 1 pemilih idealnya memilih 1 RT dan 1 RW
    $max_suara = $total_pemilih * 2;
    $partisipasi = $max_suara > 0 ? ($total_suara_masuk / $max_suara) * 100 : 0;

    // Cek Status Periode
    $periode = $pdo->query("SELECT * FROM periode_voting ORDER BY id DESC LIMIT 1")->fetch();
} catch (PDOException $e) {
    $total_calon_rt = $total_calon_rw = $total_pemilih = $total_suara_masuk = 0;
    $partisipasi = 0;
    $periode = null;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin — Pemilihan RT/RW</title>
    
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

        /* --- Stat Cards --- */
        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 24px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 15px rgba(0,0,0,0.02);
            transition: transform 0.3s;
            height: 100%;
        }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-icon {
            width: 50px; height: 50px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 20px;
        }
        .stat-value { font-size: 2rem; font-weight: 800; margin-bottom: 5px; }
        .stat-label { color: #64748b; font-weight: 600; font-size: 0.9rem; }

        /* --- Periode Card --- */
        .periode-card {
            background: linear-gradient(135deg, var(--primary-color), #0ea5e9);
            color: white;
            border-radius: 24px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(79, 70, 229, 0.2);
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
            <li><a href="<?= BASE_URL ?>/admin/dashboard.php" class="active"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li><a href="<?= BASE_URL ?>/admin/calon.php"><i class="bi bi-people-fill"></i> Kelola Calon</a></li>
            <li><a href="<?= BASE_URL ?>/admin/pemilih.php"><i class="bi bi-person-badge-fill"></i> Data Pemilih</a></li>
            <li><a href="<?= BASE_URL ?>/admin/riwayat-voting.php"><i class="bi bi-clock-history"></i> Riwayat Voting</a></li>
            <li><a href="<?= BASE_URL ?>/admin/hasil-voting.php"><i class="bi bi-bar-chart-fill"></i> Hasil Voting</a></li>
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
            <h4 class="fw-bold mb-0">Dashboard</h4>
            <button class="btn btn-primary" onclick="document.getElementById('sidebar').classList.toggle('show')">
                <i class="bi bi-list"></i>
            </button>
        </div>

        <!-- Header Desktop -->
        <div class="d-none d-lg-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">Selamat Datang, <?= e($_SESSION['nama']) ?>! 👋</h2>
                <p class="text-muted mb-0">Berikut adalah ringkasan data Pemilihan RT/RW hari ini.</p>
            </div>
            <a href="<?= BASE_URL ?>/pages/index.php" target="_blank" class="btn btn-outline-primary rounded-pill">
                <i class="bi bi-box-arrow-up-right me-1"></i> Lihat Halaman Publik
            </a>
        </div>

        <?php if ($flash = getFlash()): ?>
            <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show shadow-sm border-0" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i> <?= e($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- STATISTIK ROW -->
        <div class="row g-4 mb-4">
            <div class="col-md-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div class="stat-value"><?= number_format($total_calon_rt + $total_calon_rw) ?></div>
                    <div class="stat-label">Total Calon (RT: <?= $total_calon_rt ?> | RW: <?= $total_calon_rw ?>)</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="bi bi-person-badge-fill"></i>
                    </div>
                    <div class="stat-value"><?= number_format($total_pemilih) ?></div>
                    <div class="stat-label">Pemilih Terdaftar</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-envelope-paper-fill"></i>
                    </div>
                    <div class="stat-value"><?= number_format($total_suara_masuk) ?></div>
                    <div class="stat-label">Total Suara Masuk</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon bg-info bg-opacity-10 text-info">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                    <div class="stat-value"><?= number_format($partisipasi, 1) ?>%</div>
                    <div class="stat-label">Tingkat Partisipasi</div>
                </div>
            </div>
        </div>

        <!-- ROW: PERIODE & AKSI CEPAT -->
        <div class="row g-4">
            
            <!-- Status Periode -->
            <div class="col-lg-8">
                <div class="periode-card h-100">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <h5 class="fw-bold mb-1"><i class="bi bi-calendar2-check-fill me-2"></i> Status Periode Voting</h5>
                            <p class="mb-0 opacity-75">Informasi jadwal pemilihan yang sedang berjalan.</p>
                        </div>
                        <?php if ($periode && $periode['status'] === 'Aktif'): ?>
                            <span class="badge bg-white text-success fs-6 px-3 py-2 rounded-pill shadow-sm"><i class="bi bi-broadcast me-1"></i> Sedang Berlangsung</span>
                        <?php else: ?>
                            <span class="badge bg-white text-danger fs-6 px-3 py-2 rounded-pill shadow-sm"><i class="bi bi-stop-circle me-1"></i> Ditutup</span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($periode): ?>
                        <h4 class="fw-bold mb-3"><?= e($periode['nama_periode']) ?></h4>
                        <div class="row g-3 mb-4">
                            <div class="col-sm-6">
                                <div class="bg-white bg-opacity-10 p-3 rounded-3 border border-white border-opacity-25">
                                    <div class="small opacity-75 mb-1">Tanggal Mulai</div>
                                    <div class="fw-bold fs-5"><?= date('d M Y, H:i', strtotime($periode['tanggal_mulai'])) ?> WIB</div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="bg-white bg-opacity-10 p-3 rounded-3 border border-white border-opacity-25">
                                    <div class="small opacity-75 mb-1">Tanggal Selesai</div>
                                    <div class="fw-bold fs-5"><?= date('d M Y, H:i', strtotime($periode['tanggal_selesai'])) ?> WIB</div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-light text-dark border-0 mb-4">
                            Belum ada periode voting yang dibuat.
                        </div>
                    <?php endif; ?>
                    
                    <a href="<?= BASE_URL ?>/admin/periode.php" class="btn btn-light text-primary fw-bold px-4 py-2 rounded-3">
                        Kelola Periode <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>

            <!-- Aksi Cepat -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-4">
                    <h5 class="fw-bold mb-4">Aksi Cepat</h5>
                    <div class="d-grid gap-3">
                        <a href="<?= BASE_URL ?>/admin/tambah-calon.php" class="btn btn-outline-primary text-start py-3 rounded-3 fw-bold d-flex align-items-center justify-content-between">
                            <span><i class="bi bi-person-plus-fill me-2"></i> Tambah Calon Baru</span>
                            <i class="bi bi-chevron-right"></i>
                        </a>
                        <a href="<?= BASE_URL ?>/admin/hasil-voting.php" class="btn btn-outline-success text-start py-3 rounded-3 fw-bold d-flex align-items-center justify-content-between">
                            <span><i class="bi bi-file-earmark-bar-graph-fill me-2"></i> Lihat Hasil Akhir</span>
                            <i class="bi bi-chevron-right"></i>
                        </a>
                        <a href="<?= BASE_URL ?>/admin/pemilih.php" class="btn btn-outline-info text-start py-3 rounded-3 fw-bold d-flex align-items-center justify-content-between">
                            <span><i class="bi bi-people-fill me-2"></i> Data Pemilih</span>
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>