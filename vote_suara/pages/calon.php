<?php
require_once __DIR__ . '/../config/koneksi.php';

// Ambil semua data calon dari database
try {
    $stmt = $pdo->query("SELECT * FROM calon ORDER BY jabatan ASC, nomor_urut ASC");
    $daftar_calon = $stmt->fetchAll();
} catch (PDOException $e) {
    $daftar_calon = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Calon — Pemilihan RT/RW</title>
    
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

        /* --- Card Calon --- */
        .calon-card {
            border: none;
            border-radius: 20px;
            overflow: hidden;
            background: white;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            height: 100%;
        }
        .calon-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.08);
        }
        
        .foto-wrapper {
            position: relative;
            padding-top: 100%; /* Rasio 1:1 */
            background-color: #e2e8f0;
            overflow: hidden;
        }
        .foto-wrapper img {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        .calon-card:hover .foto-wrapper img {
            transform: scale(1.05);
        }

        /* Badge Nomor Urut */
        .nomor-urut {
            position: absolute;
            top: 15px; left: 15px;
            background: white;
            color: var(--primary-color);
            font-weight: 800;
            font-size: 1.25rem;
            width: 45px; height: 45px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 50%;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
            z-index: 10;
        }
        
        /* Badge Jabatan */
        .badge-jabatan {
            position: absolute;
            bottom: 15px; right: 15px;
            font-size: 0.8rem;
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 700;
            z-index: 10;
            backdrop-filter: blur(5px);
        }
        .badge-rt { background: rgba(14, 165, 233, 0.9); color: white; }
        .badge-rw { background: rgba(79, 70, 229, 0.9); color: white; }

        /* --- Tombol Filter --- */
        .filter-btn {
            border-radius: 50px;
            padding: 10px 24px;
            font-weight: 600;
            background: white;
            color: #64748b;
            border: 1px solid #e2e8f0;
            transition: all 0.2s;
        }
        .filter-btn:hover {
            background: #f1f5f9;
        }
        .filter-btn.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
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
                    <li class="nav-item"><a class="nav-link active" href="<?= BASE_URL ?>/pages/calon.php">Daftar Calon</a></li>
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

    <!-- HEADER -->
    <header class="page-header">
        <div class="container">
            <h1 class="page-title">Profil Calon Ketua</h1>
            <p class="text-muted fs-5 mb-0">Kenali visi, misi, dan latar belakang para calon sebelum memberikan suara Anda.</p>
        </div>
    </header>

    <!-- KONTEN UTAMA -->
    <section class="py-5">
        <div class="container">
            
            <!-- Filter Tombol -->
            <div class="d-flex justify-content-center gap-3 mb-5 flex-wrap">
                <button class="filter-btn active" onclick="filterCalon('semua', this)">
                    <i class="bi bi-grid-fill me-1"></i> Semua Calon
                </button>
                <button class="filter-btn" onclick="filterCalon('Ketua RT', this)">
                    <i class="bi bi-house-door-fill me-1"></i> Calon Ketua RT
                </button>
                <button class="filter-btn" onclick="filterCalon('Ketua RW', this)">
                    <i class="bi bi-buildings-fill me-1"></i> Calon Ketua RW
                </button>
            </div>

            <!-- Grid Daftar Calon -->
            <div class="row g-4" id="calon-container">
                <?php if (empty($daftar_calon)): ?>
                    <div class="col-12 text-center py-5">
                        <h4 class="text-muted">Belum ada data calon yang didaftarkan.</h4>
                    </div>
                <?php else: ?>
                    <?php foreach ($daftar_calon as $calon): ?>
                        <!-- Card Item -->
                        <div class="col-md-6 col-lg-4 calon-item" data-jabatan="<?= e($calon['jabatan']) ?>">
                            <div class="calon-card">
                                <div class="foto-wrapper">
                                    <span class="nomor-urut"><?= e($calon['nomor_urut']) ?></span>
                                    
                                    <!-- Cek jika foto tidak ada, pakai placeholder -->
                                    <?php 
                                        $foto_path = BASE_URL . '/uploads/' . $calon['foto'];
                                        // Cek apakah file fisiknya ada
                                        $file_fisik = __DIR__ . '/../uploads/' . $calon['foto'];
                                        $tampil_foto = (!empty($calon['foto']) && file_exists($file_fisik)) 
                                            ? $foto_path 
                                            : 'https://ui-avatars.com/api/?name=' . urlencode($calon['nama']) . '&background=e0e7ff&color=4f46e5&size=500';
                                    ?>
                                    <img src="<?= $tampil_foto ?>" alt="Foto <?= e($calon['nama']) ?>">
                                    
                                    <span class="badge-jabatan <?= $calon['jabatan'] === 'Ketua RT' ? 'badge-rt' : 'badge-rw' ?>">
                                        <?= e($calon['jabatan']) ?>
                                    </span>
                                </div>
                                <div class="card-body p-4 d-flex flex-column">
                                    <h4 class="fw-bold mb-1 fs-5"><?= e($calon['nama']) ?></h4>
                                    <p class="text-muted small mb-3"><i class="bi bi-geo-alt-fill me-1"></i> <?= e($calon['alamat'] ?: 'Alamat belum diisi') ?></p>
                                    
                                    <p class="text-muted small mb-4 flex-grow-1" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                        <?= e($calon['deskripsi'] ?: 'Belum ada deskripsi profil singkat.') ?>
                                    </p>
                                    
                                    <a href="<?= BASE_URL ?>/pages/detail-calon.php?id=<?= $calon['id'] ?>" class="btn btn-outline-primary w-100 fw-bold rounded-3 py-2" style="border-width: 2px;">
                                        Lihat Profil Lengkap
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
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
    
    <!-- Script Filter Kategori -->
    <script>
        function filterCalon(jabatan, btnElement) {
            // 1. Ubah status tombol active
            const buttons = document.querySelectorAll('.filter-btn');
            buttons.forEach(btn => btn.classList.remove('active'));
            btnElement.classList.add('active');

            // 2. Filter Card
            const items = document.querySelectorAll('.calon-item');
            items.forEach(item => {
                if (jabatan === 'semua' || item.dataset.jabatan === jabatan) {
                    item.style.display = 'block';
                    // Efek animasi fade in
                    item.style.opacity = '0';
                    setTimeout(() => { item.style.opacity = '1'; }, 50);
                } else {
                    item.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>