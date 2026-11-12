<?php
require_once __DIR__ . '/../config/koneksi.php';

// 1. Ambil ID dari URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 2. Validasi ID dan ambil data calon
$stmt = $pdo->prepare("SELECT * FROM calon WHERE id = ?");
$stmt->execute([$id]);
$calon = $stmt->fetch();

// Jika calon tidak ditemukan, kembalikan ke daftar calon
if (!$calon) {
    setFlash('danger', 'Data calon tidak ditemukan.');
    header('Location: ' . BASE_URL . '/pages/calon.php');
    exit;
}

// Cek URL foto
$foto_path = BASE_URL . '/uploads/' . $calon['foto'];
$file_fisik = __DIR__ . '/../uploads/' . $calon['foto'];
$tampil_foto = (!empty($calon['foto']) && file_exists($file_fisik)) 
    ? $foto_path 
    : 'https://ui-avatars.com/api/?name=' . urlencode($calon['nama']) . '&background=e0e7ff&color=4f46e5&size=800';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil <?= e($calon['nama']) ?> — Pemilihan RT/RW</title>
    
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
        .nav-link:hover { color: var(--primary-color); }

        /* --- Header --- */
        .page-header {
            background: linear-gradient(135deg, #e0e7ff 0%, #f0fdf4 100%);
            padding: 120px 0 80px;
            border-bottom: 1px solid #e2e8f0;
        }

        /* --- Profil Card --- */
        .profile-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.05);
            overflow: hidden;
            margin-top: -60px; /* Efek menumpuk ke header */
            position: relative;
            z-index: 10;
        }
        
        .foto-profil {
            width: 100%;
            aspect-ratio: 1/1;
            object-fit: cover;
            border-radius: 20px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .badge-jabatan {
            font-size: 0.9rem;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 700;
            display: inline-block;
            margin-bottom: 15px;
        }
        .badge-rt { background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; }
        .badge-rw { background: #e0e7ff; color: #4f46e5; border: 1px solid #c7d2fe; }

        .nomor-urut-besar {
            font-size: 5rem;
            font-weight: 800;
            line-height: 1;
            color: rgba(79, 70, 229, 0.1);
            position: absolute;
            top: 10px; right: 20px;
            z-index: 0;
        }

        /* --- Section Visi Misi --- */
        .section-title {
            font-weight: 800;
            font-size: 1.5rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .section-title i {
            color: var(--primary-color);
            background: #e0e7ff;
            padding: 8px;
            border-radius: 8px;
            font-size: 1.2rem;
        }

        .content-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 24px;
            font-size: 1.05rem;
            line-height: 1.7;
            color: #475569;
            white-space: pre-line; /* Agar enter dari database terbaca */
        }

        /* --- Tombol Kustom --- */
        .btn-custom-primary {
            background-color: var(--primary-color);
            color: white;
            border-radius: 12px;
            padding: 14px 30px;
            font-weight: 700;
            border: none;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
        }
        .btn-custom-primary:hover { background-color: var(--primary-hover); transform: translateY(-2px); color: white; }
        
        .btn-outline-custom {
            background-color: transparent;
            color: var(--dark-color);
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px 30px;
            font-weight: 700;
            transition: all 0.3s;
        }
        .btn-outline-custom:hover { border-color: var(--primary-color); color: var(--primary-color); background: white; }
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

    <!-- HEADER KOSONG UNTUK BACKGROUND -->
    <header class="page-header"></header>

    <!-- KONTEN UTAMA -->
    <section class="pb-5">
        <div class="container">
            <div class="profile-card p-4 p-md-5">
                <div class="row g-5">
                    
                    <!-- KOLOM KIRI: FOTO & INFO SINGKAT -->
                    <div class="col-lg-4 position-relative">
                        <div class="nomor-urut-besar d-none d-lg-block"><?= e($calon['nomor_urut']) ?></div>
                        
                        <img src="<?= $tampil_foto ?>" alt="<?= e($calon['nama']) ?>" class="foto-profil mb-4 position-relative z-1">
                        
                        <div class="text-center">
                            <span class="badge-jabatan <?= $calon['jabatan'] === 'Ketua RT' ? 'badge-rt' : 'badge-rw' ?>">
                                <i class="bi bi-award-fill me-1"></i> Calon <?= e($calon['jabatan']) ?> No. <?= e($calon['nomor_urut']) ?>
                            </span>
                            <h2 class="fw-bold mb-1"><?= e($calon['nama']) ?></h2>
                            <p class="text-muted fs-6"><i class="bi bi-geo-alt-fill me-1 text-danger"></i> <?= e($calon['alamat'] ?: 'Alamat belum diisi') ?></p>
                        </div>
                    </div>
                    
                    <!-- KOLOM KANAN: DETAIL VISI MISI & PROFIL -->
                    <div class="col-lg-8">
                        
                        <!-- Visi -->
                        <div class="mb-4">
                            <div class="section-title">
                                <i class="bi bi-eye-fill"></i> Visi
                            </div>
                            <div class="content-box">
                                <?= e($calon['visi'] ?: 'Belum ada visi yang didaftarkan.') ?>
                            </div>
                        </div>

                        <!-- Misi -->
                        <div class="mb-4">
                            <div class="section-title">
                                <i class="bi bi-list-check"></i> Misi
                            </div>
                            <div class="content-box">
                                <?= e($calon['misi'] ?: 'Belum ada misi yang didaftarkan.') ?>
                            </div>
                        </div>

                        <!-- Program Kerja -->
                        <div class="mb-4">
                            <div class="section-title">
                                <i class="bi bi-tools"></i> Program Kerja
                            </div>
                            <div class="content-box">
                                <?= e($calon['program_kerja'] ?: 'Belum ada program kerja yang didaftarkan.') ?>
                            </div>
                        </div>

                        <!-- Deskripsi Profil -->
                        <div class="mb-5">
                            <div class="section-title">
                                <i class="bi bi-person-lines-fill"></i> Profil Singkat
                            </div>
                            <div class="content-box">
                                <?= e($calon['deskripsi'] ?: 'Belum ada deskripsi profil.') ?>
                            </div>
                        </div>

                        <!-- Tombol Aksi -->
                        <hr class="mb-4">
                        <div class="d-flex flex-column flex-sm-row gap-3">
                            <a href="<?= BASE_URL ?>/pages/calon.php" class="btn btn-outline-custom">
                                <i class="bi bi-arrow-left me-2"></i> Kembali ke Daftar
                            </a>
                            
                            <?php if (isLoggedIn() && !isAdmin()): ?>
                                <a href="<?= BASE_URL ?>/pages/voting.php" class="btn btn-custom-primary">
                                    <i class="bi bi-check2-square me-2"></i> Berikan Suara Anda
                                </a>
                            <?php elseif (!isLoggedIn()): ?>
                                <a href="<?= BASE_URL ?>/auth/login.php" class="btn btn-custom-primary">
                                    <i class="bi bi-box-arrow-in-right me-2"></i> Login untuk Memilih
                                </a>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
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