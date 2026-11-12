<?php
require_once __DIR__ . '/../config/koneksi.php';

// Fungsi untuk mengambil data klasemen berdasarkan jabatan
function getKlasemen($pdo, $jabatan) {
    $sql = "SELECT c.id, c.nomor_urut, c.nama, c.foto, COUNT(v.id) AS total_suara
            FROM calon c
            LEFT JOIN voting v ON c.id = v.calon_id
            WHERE c.jabatan = ?
            GROUP BY c.id, c.nomor_urut, c.nama, c.foto
            ORDER BY total_suara DESC, c.nomor_urut ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$jabatan]);
    return $stmt->fetchAll();
}

// Ambil data
$klasemen_rt = getKlasemen($pdo, 'Ketua RT');
$klasemen_rw = getKlasemen($pdo, 'Ketua RW');

// Hitung total suara untuk persentase
$total_suara_rt = array_sum(array_column($klasemen_rt, 'total_suara'));
$total_suara_rw = array_sum(array_column($klasemen_rw, 'total_suara'));

// Fungsi bantuan untuk menentukan warna progress bar
function getProgressColor($index) {
    $colors = ['bg-primary', 'bg-success', 'bg-warning', 'bg-info', 'bg-secondary'];
    return $colors[$index % count($colors)];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Klasemen Voting — Pemilihan RT/RW</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #4f46e5;
            --primary-hover: #4338ca;
            --dark-color: #1e293b;
            --light-bg: #f8fafc;
        }
        
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--light-bg);
            color: var(--dark-color);
        }

        /* --- Navbar --- */
        .navbar {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.05);
        }
        .navbar-brand { font-weight: 800; color: var(--primary-color) !important; }
        .nav-link { font-weight: 600; color: var(--dark-color); }
        .nav-link:hover, .nav-link.active { color: var(--primary-color); }

        /* --- Header Halaman --- */
        .page-header {
            background: linear-gradient(135deg, #e0e7ff 0%, #f0fdf4 100%);
            padding: 120px 0 60px;
            text-align: center;
            border-bottom: 1px solid #e2e8f0;
        }
        .page-title {
            font-weight: 800;
            font-size: 2.5rem;
            letter-spacing: -0.5px;
        }

        /* --- Podium --- */
        .podium-container {
            display: flex;
            justify-content: center;
            align-items: flex-end;
            gap: 15px;
            margin-top: 30px;
            margin-bottom: 50px;
        }
        .podium-item {
            text-align: center;
            width: 30%;
            max-width: 200px;
            position: relative;
        }
        .podium-foto {
            width: 80px; height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin: 0 auto 10px;
            background: #e2e8f0;
        }
        .podium-nama {
            font-weight: 700;
            font-size: 0.9rem;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .podium-suara {
            font-size: 0.8rem;
            color: #64748b;
            font-weight: 600;
        }
        .podium-box {
            background: #e2e8f0;
            border-radius: 12px 12px 0 0;
            margin-top: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: 800;
            color: #94a3b8;
            box-shadow: inset 0 5px 10px rgba(255,255,255,0.5);
        }
        /* Juara 1 */
        .podium-1 .podium-foto { border-color: #fbbf24; width: 100px; height: 100px; }
        .podium-1 .podium-box { background: linear-gradient(to bottom, #fbbf24, #f59e0b); color: white; height: 120px; }
        .podium-1 { width: 35%; z-index: 2; }
        /* Juara 2 */
        .podium-2 .podium-foto { border-color: #cbd5e1; }
        .podium-2 .podium-box { background: linear-gradient(to bottom, #cbd5e1, #94a3b8); color: white; height: 90px; }
        /* Juara 3 */
        .podium-3 .podium-foto { border-color: #d97706; }
        .podium-3 .podium-box { background: linear-gradient(to bottom, #d97706, #b45309); color: white; height: 70px; }

        /* --- Tabel Klasemen Modern --- */
        .table-custom {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.03);
        }
        .table-custom th {
            background: #f8fafc;
            color: #64748b;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            padding: 16px;
            border-bottom: 2px solid #e2e8f0;
        }
        .table-custom td {
            padding: 16px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }
        .table-custom tbody tr:hover {
            background-color: #f8fafc;
        }
        
        /* --- Progress Bar --- */
        .progress-custom {
            height: 10px;
            border-radius: 10px;
            background-color: #f1f5f9;
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);
        }
        .progress-bar-custom {
            border-radius: 10px;
            transition: width 1.5s ease-in-out;
        }

        /* --- Tombol Kustom --- */
        .btn-custom-primary {
            background-color: var(--primary-color);
            color: white;
            border-radius: 10px;
            font-weight: 600;
            border: none;
        }
        .btn-custom-primary:hover { background-color: var(--primary-hover); color: white; }

        .avatar-klasemen {
            width: 45px; height: 45px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e2e8f0;
        }
    </style>
</head>
<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg fixed-top py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="<?= BASE_URL ?>/pages/index.php">
                <i class="bi bi-box-seam-fill fs-3"></i> VoteRT/RW
            </a>
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center gap-2 mt-3 mt-lg-0">
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/pages/index.php">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/pages/calon.php">Daftar Calon</a></li>
                    <li class="nav-item"><a class="nav-link active" href="<?= BASE_URL ?>/pages/klasemen.php">Klasemen</a></li>
                    
                    <?php if (isLoggedIn()): ?>
                        <?php if (isAdmin()): ?>
                            <li class="nav-item"><a class="nav-link fw-bold text-warning" href="<?= BASE_URL ?>/admin/dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                        <?php else: ?>
                            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/pages/voting.php"><i class="bi bi-check2-square"></i> Voting</a></li>
                        <?php endif; ?>
                        <li class="nav-item ms-lg-3 mt-2 mt-lg-0">
                            <a href="<?= BASE_URL ?>/auth/logout.php" class="btn btn-danger rounded-pill px-4" onclick="return confirm('Yakin ingin logout?')">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item ms-lg-3 mt-2 mt-lg-0">
                            <a href="<?= BASE_URL ?>/auth/login.php" class="btn btn-custom-primary w-100">Login / Daftar</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- HEADER -->
    <header class="page-header">
        <div class="container">
            <h1 class="page-title">Klasemen Sementara</h1>
            <p class="text-muted fs-5 mb-0">Perolehan suara *real-time* Pemilihan Ketua RT & RW.</p>
            <div class="badge bg-white text-primary border px-3 py-2 rounded-pill mt-3 shadow-sm">
                <i class="bi bi-clock-history me-1"></i> Pembaruan Terakhir: <?= date('d M Y, H:i:s') ?> WIB
            </div>
        </div>
    </header>

    <!-- KONTEN UTAMA -->
    <section class="py-5">
        <div class="container">
            
            <!-- ========================================== -->
            <!-- KLASEMEN KETUA RT                          -->
            <!-- ========================================== -->
            <div class="mb-5">
                <div class="d-flex align-items-center gap-2 mb-4">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-house-door-fill fs-5"></i>
                    </div>
                    <h3 class="fw-bold mb-0">Klasemen Ketua RT</h3>
                    <span class="badge bg-secondary ms-2 rounded-pill"><?= number_format($total_suara_rt) ?> Suara Masuk</span>
                </div>

                <?php if (empty($klasemen_rt)): ?>
                    <div class="alert alert-info">Belum ada data calon Ketua RT.</div>
                <?php else: ?>
                    <!-- Podium RT (Hanya muncul jika ada minimal 3 calon) -->
                    <?php if (count($klasemen_rt) >= 3): ?>
                        <div class="podium-container">
                            <!-- Juara 2 -->
                            <div class="podium-item podium-2 order-1">
                                <?php $c2 = $klasemen_rt[1]; ?>
                                <img src="<?= BASE_URL ?>/uploads/<?= e($c2['foto']) ?>" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($c2['nama']) ?>&background=e2e8f0&color=64748b'" class="podium-foto" alt="<?= e($c2['nama']) ?>">
                                <div class="podium-nama"><?= e($c2['nama']) ?></div>
                                <div class="podium-suara"><?= $c2['total_suara'] ?> Suara</div>
                                <div class="podium-box">2</div>
                            </div>
                            
                            <!-- Juara 1 -->
                            <div class="podium-item podium-1 order-2">
                                <?php $c1 = $klasemen_rt[0]; ?>
                                <img src="<?= BASE_URL ?>/uploads/<?= e($c1['foto']) ?>" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($c1['nama']) ?>&background=fbbf24&color=fff'" class="podium-foto" alt="<?= e($c1['nama']) ?>">
                                <div class="podium-nama"><?= e($c1['nama']) ?></div>
                                <div class="podium-suara"><?= $c1['total_suara'] ?> Suara</div>
                                <div class="podium-box"><i class="bi bi-crown"></i></div>
                            </div>

                            <!-- Juara 3 -->
                            <div class="podium-item podium-3 order-3">
                                <?php $c3 = $klasemen_rt[2]; ?>
                                <img src="<?= BASE_URL ?>/uploads/<?= e($c3['foto']) ?>" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($c3['nama']) ?>&background=e2e8f0&color=64748b'" class="podium-foto" alt="<?= e($c3['nama']) ?>">
                                <div class="podium-nama"><?= e($c3['nama']) ?></div>
                                <div class="podium-suara"><?= $c3['total_suara'] ?> Suara</div>
                                <div class="podium-box">3</div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Tabel Detail RT -->
                    <div class="table-responsive table-custom">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th width="10%" class="text-center">Rank</th>
                                    <th width="40%">Calon</th>
                                    <th width="35%">Persentase Suara</th>
                                    <th width="15%" class="text-center">Total Suara</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($klasemen_rt as $index => $row): 
                                    $persen = $total_suara_rt > 0 ? ($row['total_suara'] / $total_suara_rt) * 100 : 0;
                                    $color = getProgressColor($index);
                                ?>
                                <tr>
                                    <td class="text-center">
                                        <?php if ($index == 0): ?>
                                            <span class="badge bg-warning text-dark fs-6 rounded-circle p-2"><i class="bi bi-trophy-fill"></i></span>
                                        <?php else: ?>
                                            <span class="fw-bold text-muted fs-5"><?= $index + 1 ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="<?= BASE_URL ?>/uploads/<?= e($row['foto']) ?>" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($row['nama']) ?>'" class="avatar-klasemen" alt="Foto">
                                            <div>
                                                <div class="fw-bold"><?= e($row['nama']) ?></div>
                                                <div class="text-muted small">Nomor Urut <?= e($row['nomor_urut']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="small fw-bold text-muted"><?= number_format($persen, 1) ?>%</span>
                                        </div>
                                        <div class="progress progress-custom">
                                            <div class="progress-bar progress-bar-custom <?= $color ?>" role="progressbar" style="width: <?= $persen ?>%"></div>
                                        </div>
                                    </td>
                                    <td class="text-center fw-bold fs-5"><?= number_format($row['total_suara']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- ========================================== -->
            <!-- KLASEMEN KETUA RW                          -->
            <!-- ========================================== -->
            <div class="mt-5">
                <div class="d-flex align-items-center gap-2 mb-4">
                    <div class="bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-buildings-fill fs-5"></i>
                    </div>
                    <h3 class="fw-bold mb-0">Klasemen Ketua RW</h3>
                    <span class="badge bg-secondary ms-2 rounded-pill"><?= number_format($total_suara_rw) ?> Suara Masuk</span>
                </div>

                <?php if (empty($klasemen_rw)): ?>
                    <div class="alert alert-info">Belum ada data calon Ketua RW.</div>
                <?php else: ?>
                    <!-- Podium RW -->
                    <?php if (count($klasemen_rw) >= 3): ?>
                        <div class="podium-container">
                            <?php $r2 = $klasemen_rw[1]; $r1 = $klasemen_rw[0]; $r3 = $klasemen_rw[2]; ?>
                            <!-- Juara 2 -->
                            <div class="podium-item podium-2 order-1">
                                <img src="<?= BASE_URL ?>/uploads/<?= e($r2['foto']) ?>" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($r2['nama']) ?>&background=e2e8f0&color=64748b'" class="podium-foto">
                                <div class="podium-nama"><?= e($r2['nama']) ?></div>
                                <div class="podium-suara"><?= $r2['total_suara'] ?> Suara</div>
                                <div class="podium-box">2</div>
                            </div>
                            <!-- Juara 1 -->
                            <div class="podium-item podium-1 order-2">
                                <img src="<?= BASE_URL ?>/uploads/<?= e($r1['foto']) ?>" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($r1['nama']) ?>&background=fbbf24&color=fff'" class="podium-foto">
                                <div class="podium-nama"><?= e($r1['nama']) ?></div>
                                <div class="podium-suara"><?= $r1['total_suara'] ?> Suara</div>
                                <div class="podium-box"><i class="bi bi-crown"></i></div>
                            </div>
                            <!-- Juara 3 -->
                            <div class="podium-item podium-3 order-3">
                                <img src="<?= BASE_URL ?>/uploads/<?= e($r3['foto']) ?>" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($r3['nama']) ?>&background=e2e8f0&color=64748b'" class="podium-foto">
                                <div class="podium-nama"><?= e($r3['nama']) ?></div>
                                <div class="podium-suara"><?= $r3['total_suara'] ?> Suara</div>
                                <div class="podium-box">3</div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Tabel Detail RW -->
                    <div class="table-responsive table-custom">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th width="10%" class="text-center">Rank</th>
                                    <th width="40%">Calon</th>
                                    <th width="35%">Persentase Suara</th>
                                    <th width="15%" class="text-center">Total Suara</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($klasemen_rw as $index => $row): 
                                    $persen = $total_suara_rw > 0 ? ($row['total_suara'] / $total_suara_rw) * 100 : 0;
                                    $color = getProgressColor($index);
                                ?>
                                <tr>
                                    <td class="text-center">
                                        <?php if ($index == 0): ?>
                                            <span class="badge bg-warning text-dark fs-6 rounded-circle p-2"><i class="bi bi-trophy-fill"></i></span>
                                        <?php else: ?>
                                            <span class="fw-bold text-muted fs-5"><?= $index + 1 ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="<?= BASE_URL ?>/uploads/<?= e($row['foto']) ?>" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($row['nama']) ?>'" class="avatar-klasemen" alt="Foto">
                                            <div>
                                                <div class="fw-bold"><?= e($row['nama']) ?></div>
                                                <div class="text-muted small">Nomor Urut <?= e($row['nomor_urut']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="small fw-bold text-muted"><?= number_format($persen, 1) ?>%</span>
                                        </div>
                                        <div class="progress progress-custom">
                                            <div class="progress-bar progress-bar-custom <?= $color ?>" role="progressbar" style="width: <?= $persen ?>%"></div>
                                        </div>
                                    </td>
                                    <td class="text-center fw-bold fs-5"><?= number_format($row['total_suara']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Tombol CTA -->
            <div class="text-center mt-5 pt-4">
                <a href="<?= BASE_URL ?>/pages/voting.php" class="btn btn-custom-primary btn-lg px-5 py-3 rounded-pill shadow-sm">
                    <i class="bi bi-check2-square me-2"></i> Berikan Suara Anda Sekarang
                </a>
            </div>

        </div>
    </section>

    <!-- FOOTER -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0 opacity-75">&copy; <?= date('Y') ?> Sistem Informasi Pemilihan RT/RW. Dibuat untuk Praktik Web Development.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>