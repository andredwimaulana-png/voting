<?php
require_once __DIR__ . '/../config/koneksi.php';

// Ambil data statistik untuk ditampilkan di beranda
try {
    $total_calon = $pdo->query("SELECT COUNT(*) FROM calon")->fetchColumn();
    $total_pemilih = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'pemilih'")->fetchColumn();
    $total_suara = $pdo->query("SELECT COUNT(*) FROM voting")->fetchColumn();
    
    // Cek status periode
    $stmt_periode = $pdo->query("SELECT * FROM periode_voting WHERE status = 'Aktif' LIMIT 1");
    $periode_aktif = $stmt_periode->fetch();
} catch (PDOException $e) {
    $total_calon = $total_pemilih = $total_suara = 0;
    $periode_aktif = false;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda — Pemilihan RT/RW</title>
    
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
            --secondary-color: #0ea5e9;
            --dark-color: #1e293b;
            --light-bg: #f8fafc;
        }
        
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--light-bg);
            color: var(--dark-color);
            overflow-x: hidden;
        }

        /* --- Navbar --- */
        .navbar {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }
        .navbar-brand {
            font-weight: 800;
            color: var(--primary-color) !important;
            letter-spacing: -0.5px;
        }
        .nav-link {
            font-weight: 600;
            color: var(--dark-color);
            transition: color 0.2s;
        }
        .nav-link:hover, .nav-link.active {
            color: var(--primary-color);
        }

        /* --- Hero Section --- */
        .hero-section {
            padding: 160px 0 100px;
            background: linear-gradient(135deg, #e0e7ff 0%, #f0fdf4 100%);
            position: relative;
            overflow: hidden;
        }
        /* Ornamen Bulat Abstrak */
        .hero-section::before, .hero-section::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            z-index: 0;
        }
        .hero-section::before {
            width: 400px; height: 400px;
            background: rgba(79, 70, 229, 0.15);
            top: -100px; right: -100px;
        }
        .hero-section::after {
            width: 300px; height: 300px;
            background: rgba(14, 165, 233, 0.15);
            bottom: -50px; left: -50px;
        }
        .hero-content {
            position: relative;
            z-index: 1;
        }
        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.2;
            letter-spacing: -1px;
            margin-bottom: 1.5rem;
        }
        .hero-title span {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .hero-subtitle {
            font-size: 1.1rem;
            color: #64748b;
            margin-bottom: 2rem;
            max-width: 500px;
        }

        /* --- Tombol Kustom --- */
        .btn-custom-primary {
            background-color: var(--primary-color);
            color: white;
            border-radius: 12px;
            padding: 12px 28px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
        }
        .btn-custom-primary:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
            color: white;
        }
        .btn-custom-outline {
            background-color: transparent;
            color: var(--dark-color);
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 28px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-custom-outline:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
            background: white;
        }

        /* --- Kartu Statistik --- */
        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 24px;
            border: 1px solid rgba(0,0,0,0.03);
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            transition: transform 0.3s ease;
            text-align: center;
            height: 100%;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            width: 50px; height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin: 0 auto 15px;
        }
        .stat-number {
            font-size: 2rem;
            font-weight: 800;
            color: var(--dark-color);
            margin-bottom: 5px;
        }
        .stat-label {
            font-size: 0.9rem;
            color: #64748b;
            font-weight: 500;
        }

        /* --- Jadwal Voting --- */
        .schedule-card {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(79, 70, 229, 0.2);
            position: relative;
            overflow: hidden;
        }
        .schedule-card::after {
            content: '\F26E'; /* Bootstrap icon calendar */
            font-family: 'bootstrap-icons';
            position: absolute;
            right: -20px; bottom: -20px;
            font-size: 10rem;
            opacity: 0.1;
            transform: rotate(-15deg);
        }

        /* --- Animasi Scroll --- */
        .fade-up {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.8s ease, transform 0.8s ease;
        }
        .fade-up.show {
            opacity: 1;
            transform: translateY(0);
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
                    <li class="nav-item"><a class="nav-link active" href="<?= BASE_URL ?>/pages/index.php">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/pages/calon.php">Daftar Calon</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/pages/klasemen.php">Klasemen</a></li>
                    
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

    <!-- HERO SECTION -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <!-- Teks Kiri -->
                <div class="col-lg-6 mb-5 mb-lg-0 hero-content fade-up">
                    <?php if ($periode_aktif): ?>
                        <div class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill mb-3 border border-success border-opacity-25">
                            <i class="bi bi-broadcast me-2"></i> Voting Sedang Berlangsung
                        </div>
                    <?php else: ?>
                        <div class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill mb-3 border border-secondary border-opacity-25">
                            <i class="bi bi-clock-history me-2"></i> Voting Belum Dimulai / Ditutup
                        </div>
                    <?php endif; ?>
                    
                    <h1 class="hero-title">Tentukan Masa Depan <br><span>Lingkungan Anda.</span></h1>
                    <p class="hero-subtitle">Gunakan hak pilih Anda secara bijak. Pilih calon Ketua RT dan RW yang tepat untuk membawa perubahan positif di lingkungan kita.</p>
                    
                 <div class="d-flex flex-column flex-sm-row gap-3">
                    <a href="<?= BASE_URL ?>/pages/calon.php" class="btn btn-custom-primary d-inline-flex align-items-center justify-content-center">
                            Lihat Profil Calon <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                        <?php if (isLoggedIn() && !isAdmin()): ?>
                            <a href="<?= BASE_URL ?>/pages/voting.php" class="btn btn-custom-outline d-inline-flex align-items-center justify-content-center">
                                <i class="bi bi-check2-circle me-2"></i> Mulai Voting
                            </a>
                        <?php else: ?>
                            <a href="<?= BASE_URL ?>/auth/login.php" class="btn btn-custom-outline d-inline-flex align-items-center justify-content-center">
                                <i class="bi bi-box-arrow-in-right me-2"></i> Login Pemilih
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Gambar Kanan -->
                <div class="col-lg-6 hero-content fade-up" style="transition-delay: 0.2s;">
                    <img src="https://images.unsplash.com/photo-1540910419892-4a36d2c3266c?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                         alt="Ilustrasi Voting" class="img-fluid rounded-4 shadow-lg" 
                         style="object-fit: cover; height: 400px; width: 100%;">
                </div>
            </div>
        </div>
    </section>

    <!-- STATISTIK SECTION -->
    <section class="py-5 bg-white">
        <div class="container py-4">
            <div class="row g-4">
                <div class="col-6 col-md-3 fade-up">
                    <div class="stat-card">
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div class="stat-number"><?= number_format($total_calon) ?></div>
                        <div class="stat-label">Total Calon</div>
                    </div>
                </div>
                <div class="col-6 col-md-3 fade-up" style="transition-delay: 0.1s;">
                    <div class="stat-card">
                        <div class="stat-icon bg-success bg-opacity-10 text-success">
                            <i class="bi bi-person-badge-fill"></i>
                        </div>
                        <div class="stat-number"><?= number_format($total_pemilih) ?></div>
                        <div class="stat-label">Pemilih Terdaftar</div>
                    </div>
                </div>
                <div class="col-6 col-md-3 fade-up" style="transition-delay: 0.2s;">
                    <div class="stat-card">
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-envelope-paper-fill"></i>
                        </div>
                        <div class="stat-number"><?= number_format($total_suara) ?></div>
                        <div class="stat-label">Suara Masuk</div>
                    </div>
                </div>
                <div class="col-6 col-md-3 fade-up" style="transition-delay: 0.3s;">
                    <div class="stat-card">
                        <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                            <i class="bi bi-calendar2-check-fill"></i>
                        </div>
                        <div class="stat-number text-uppercase fs-4"><?= $periode_aktif ? 'Aktif' : 'Tutup' ?></div>
                        <div class="stat-label">Status Periode</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- JADWAL SECTION -->
    <section class="py-5 mb-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10 fade-up">
                    <div class="schedule-card">
                        <div class="row align-items-center position-relative z-1">
                            <div class="col-md-8 mb-4 mb-md-0">
                                <h3 class="fw-bold mb-3"><i class="bi bi-calendar-event me-2"></i> Informasi Jadwal Voting</h3>
                                <?php if ($periode_aktif): ?>
                                    <h5 class="mb-3"><?= e($periode_aktif['nama_periode']) ?></h5>
                                    <p class="mb-0 opacity-75">
                                        <i class="bi bi-clock me-2"></i> 
                                        <?= date('d M Y, H:i', strtotime($periode_aktif['tanggal_mulai'])) ?> 
                                        s/d 
                                        <?= date('d M Y, H:i', strtotime($periode_aktif['tanggal_selesai'])) ?>
                                    </p>
                                <?php else: ?>
                                    <p class="mb-0 opacity-75">Saat ini tidak ada periode voting yang sedang aktif. Silakan hubungi panitia untuk informasi lebih lanjut.</p>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <a href="<?= BASE_URL ?>/pages/klasemen.php" class="btn btn-light text-primary fw-bold px-4 py-3 rounded-3 shadow-sm">
                                    Lihat Klasemen <i class="bi bi-bar-chart-fill ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="bg-dark text-white py-4 mt-auto">
        <div class="container text-center">
            <p class="mb-0 opacity-75">&copy; <?= date('Y') ?> Sistem Informasi Pemilihan RT/RW. Dibuat untuk Praktik Web Development.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Script Animasi Scroll -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('show');
                    }
                });
            }, { threshold: 0.1 });

            const hiddenElements = document.querySelectorAll('.fade-up');
            hiddenElements.forEach((el) => observer.observe(el));
            
            // Navbar shadow on scroll
            const navbar = document.querySelector('.navbar');
            window.addEventListener('scroll', () => {
                if (window.scrollY > 50) {
                    navbar.classList.add('shadow-sm');
                    navbar.style.padding = '10px 0';
                } else {
                    navbar.classList.remove('shadow-sm');
                    navbar.style.padding = '16px 0';
                }
            });
        });
    </script>
</body>
</html>