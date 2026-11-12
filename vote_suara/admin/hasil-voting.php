<?php
require_once __DIR__ . '/../config/koneksi.php';

// Proteksi Admin
requireAdmin();

// 1. Ambil data klasemen (sama seperti di halaman publik)
function getHasil($pdo, $jabatan) {
    $sql = "SELECT c.nomor_urut, c.nama, c.foto, COUNT(v.id) AS total_suara
            FROM calon c
            LEFT JOIN voting v ON c.id = v.calon_id
            WHERE c.jabatan = ?
            GROUP BY c.id, c.nomor_urut, c.nama, c.foto
            ORDER BY total_suara DESC, c.nomor_urut ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$jabatan]);
    return $stmt->fetchAll();
}

$hasil_rt = getHasil($pdo, 'Ketua RT');
$hasil_rw = getHasil($pdo, 'Ketua RW');

$total_rt = array_sum(array_column($hasil_rt, 'total_suara'));
$total_rw = array_sum(array_column($hasil_rw, 'total_suara'));

// Ambil info periode terakhir
$periode = $pdo->query("SELECT * FROM periode_voting ORDER BY id DESC LIMIT 1")->fetch();

// Ambil total pemilih
$total_pemilih = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'pemilih'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Akhir Voting — Admin Panel</title>
    
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

        /* --- Laporan Card --- */
        .report-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            border: 1px solid #e2e8f0;
            padding: 40px;
            max-width: 900px;
            margin: 0 auto;
        }

        .table-report th {
            background: #f8fafc;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            padding: 16px;
            border-bottom: 2px solid #e2e8f0;
        }
        .table-report td {
            padding: 16px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }

        /* --- Print Styles --- */
        @media print {
            body { background: white; }
            .sidebar, .d-print-none, .btn, .alert { display: none !important; }
            .main-content { margin-left: 0; padding: 0; }
            .report-card { border: none; box-shadow: none; padding: 0; max-width: 100%; }
            .table-report th { background: #e2e8f0 !important; color: black !important; -webkit-print-color-adjust: exact; }
            .badge { border: 1px solid #000 !important; color: #000 !important; background: transparent !important; }
        }
    </style>
</head>
<body>

    <!-- SIDEBAR (Akan hilang saat di-print) -->
    <div class="sidebar d-print-none" id="sidebar">
        <a href="<?= BASE_URL ?>/admin/dashboard.php" class="sidebar-brand">
            <i class="bi bi-shield-lock-fill"></i> Admin Panel
        </a>
        
        <ul class="sidebar-menu">
            <li><a href="<?= BASE_URL ?>/admin/dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li><a href="<?= BASE_URL ?>/admin/calon.php"><i class="bi bi-people-fill"></i> Kelola Calon</a></li>
            <li><a href="<?= BASE_URL ?>/admin/pemilih.php"><i class="bi bi-person-badge-fill"></i> Data Pemilih</a></li>
            <li><a href="<?= BASE_URL ?>/admin/hasil-voting.php" class="active"><i class="bi bi-bar-chart-fill"></i> Hasil Voting</a></li>
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
        <div class="d-flex justify-content-between align-items-center mb-4 d-lg-none d-print-none">
            <h4 class="fw-bold mb-0">Hasil Voting</h4>
            <button class="btn btn-primary" onclick="document.getElementById('sidebar').classList.toggle('show')">
                <i class="bi bi-list"></i>
            </button>
        </div>

        <!-- Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3 d-print-none">
            <div>
                <h2 class="fw-bold mb-1">Laporan Hasil Akhir</h2>
                <p class="text-muted mb-0">Rekapitulasi resmi perolehan suara Pemilihan RT/RW.</p>
            </div>
            <button onclick="window.print()" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm">
                <i class="bi bi-printer-fill me-1"></i> Cetak / Simpan PDF
            </button>
        </div>

        <!-- KERTAS LAPORAN -->
        <div class="report-card">
            
            <!-- Kop Surat -->
            <div class="text-center mb-5 border-bottom pb-4">
                <h3 class="fw-bold mb-1 text-uppercase">Panitia Pemilihan Daerah</h3>
                <h4 class="fw-bold text-primary mb-2"><?= $periode ? e($periode['nama_periode']) : 'Pemilihan RT/RW' ?></h4>
                <p class="text-muted mb-0">Dicetak pada: <?= date('d F Y, H:i') ?> WIB</p>
            </div>

            <!-- Ringkasan Eksekutif -->
            <div class="row mb-5 text-center">
                <div class="col-4 border-end">
                    <h6 class="text-muted text-uppercase fw-bold mb-2">Total Pemilih</h6>
                    <h3 class="fw-bold mb-0"><?= number_format($total_pemilih) ?> <span class="fs-6 text-muted fw-normal">Jiwa</span></h3>
                </div>
                <div class="col-4 border-end">
                    <h6 class="text-muted text-uppercase fw-bold mb-2">Suara Ketua RT</h6>
                    <h3 class="fw-bold mb-0 text-primary"><?= number_format($total_rt) ?> <span class="fs-6 text-muted fw-normal">Suara</span></h3>
                </div>
                <div class="col-4">
                    <h6 class="text-muted text-uppercase fw-bold mb-2">Suara Ketua RW</h6>
                    <h3 class="fw-bold mb-0 text-info"><?= number_format($total_rw) ?> <span class="fs-6 text-muted fw-normal">Suara</span></h3>
                </div>
            </div>

            <!-- Tabel Hasil RT -->
            <div class="mb-5">
                <h5 class="fw-bold mb-3 border-start border-4 border-primary ps-3">A. Perolehan Suara Ketua RT</h5>
                <table class="table table-report table-bordered mb-0">
                    <thead>
                        <tr>
                            <th width="10%" class="text-center">Rank</th>
                            <th width="15%" class="text-center">No. Urut</th>
                            <th width="45%">Nama Calon</th>
                            <th width="30%" class="text-center">Total Suara</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($hasil_rt)): ?>
                            <tr><td colspan="4" class="text-center py-3 text-muted">Tidak ada data calon.</td></tr>
                        <?php else: ?>
                            <?php foreach ($hasil_rt as $index => $row): ?>
                                <tr>
                                    <td class="text-center fw-bold fs-5"><?= $index + 1 ?></td>
                                    <td class="text-center fw-bold"><?= e($row['nomor_urut']) ?></td>
                                    <td class="fw-bold"><?= e($row['nama']) ?></td>
                                    <td class="text-center fw-bold fs-5"><?= number_format($row['total_suara']) ?> Suara</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Tabel Hasil RW -->
            <div class="mb-5">
                <h5 class="fw-bold mb-3 border-start border-4 border-info ps-3">B. Perolehan Suara Ketua RW</h5>
                <table class="table table-report table-bordered mb-0">
                    <thead>
                        <tr>
                            <th width="10%" class="text-center">Rank</th>
                            <th width="15%" class="text-center">No. Urut</th>
                            <th width="45%">Nama Calon</th>
                            <th width="30%" class="text-center">Total Suara</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($hasil_rw)): ?>
                            <tr><td colspan="4" class="text-center py-3 text-muted">Tidak ada data calon.</td></tr>
                        <?php else: ?>
                            <?php foreach ($hasil_rw as $index => $row): ?>
                                <tr>
                                    <td class="text-center fw-bold fs-5"><?= $index + 1 ?></td>
                                    <td class="text-center fw-bold"><?= e($row['nomor_urut']) ?></td>
                                    <td class="fw-bold"><?= e($row['nama']) ?></td>
                                    <td class="text-center fw-bold fs-5"><?= number_format($row['total_suara']) ?> Suara</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Tanda Tangan -->
            <div class="row mt-5 pt-5">
                <div class="col-6 text-center">
                    <p class="mb-5">Mengetahui,<br>Ketua Panitia Pemilihan</p>
                    <h5 class="fw-bold text-decoration-underline mb-0">_______________________</h5>
                </div>
                <div class="col-6 text-center">
                    <p class="mb-5">Ponorogo, <?= date('d F Y') ?><br>Sekretaris Panitia</p>
                    <h5 class="fw-bold text-decoration-underline mb-0">_______________________</h5>
                </div>
            </div>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>