<?php
require_once __DIR__ . '/../config/koneksi.php';

// 1. Proteksi: Wajib Login
requireLogin('/auth/login.php');

// 2. Proteksi: Admin tidak boleh mengakses halaman voting
if (isAdmin()) {
    setFlash('warning', 'Admin tidak dapat melakukan voting.');
    header('Location: ' . BASE_URL . '/admin/dashboard.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// 3. Cek Status Periode Voting
$stmt_periode = $pdo->query("SELECT * FROM periode_voting WHERE status = 'Aktif' LIMIT 1");
$periode = $stmt_periode->fetch();

if (!$periode) {
    setFlash('danger', 'Voting sedang ditutup atau belum dimulai.');
    header('Location: ' . BASE_URL . '/pages/index.php');
    exit;
}

// 4. Cek apakah user sudah voting sebelumnya & Ambil ID calon yang dipilih
$stmt_cek = $pdo->prepare("SELECT jabatan, calon_id FROM voting WHERE user_id = ?");
$stmt_cek->execute([$user_id]);
$riwayat_voting = $stmt_cek->fetchAll();

$sudah_voting = array_column($riwayat_voting, 'jabatan');
$sudah_vote_rt = in_array('Ketua RT', $sudah_voting);
$sudah_vote_rw = in_array('Ketua RW', $sudah_voting);

$id_pilihan_rt = null;
$id_pilihan_rw = null;
foreach ($riwayat_voting as $rv) {
    if ($rv['jabatan'] === 'Ketua RT') $id_pilihan_rt = $rv['calon_id'];
    if ($rv['jabatan'] === 'Ketua RW') $id_pilihan_rw = $rv['calon_id'];
}

// 5. Ambil data calon
$calon_rt = $pdo->query("SELECT * FROM calon WHERE jabatan = 'Ketua RT' ORDER BY nomor_urut ASC")->fetchAll();
$calon_rw = $pdo->query("SELECT * FROM calon WHERE jabatan = 'Ketua RW' ORDER BY nomor_urut ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bilik Suara — Pemilihan RT/RW</title>
    
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

        /* --- Header Halaman --- */
        .page-header {
            background: linear-gradient(135deg, #e0e7ff 0%, #f0fdf4 100%);
            padding: 120px 0 60px;
            text-align: center;
            border-bottom: 1px solid #e2e8f0;
        }
        .page-title { font-weight: 800; font-size: 2.5rem; }

        /* --- Card Pilihan Calon --- */
        .pilihan-card {
            border: 3px solid transparent;
            border-radius: 20px;
            overflow: hidden;
            background: white;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            cursor: pointer;
            position: relative;
            height: 100%;
        }
        .pilihan-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.08);
        }
        
        /* State saat dipilih (Radio Checked - Sebelum Submit) */
        input[type="radio"]:checked + .pilihan-card {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.2);
        }
        
        /* Ikon Centang */
        .check-icon {
            position: absolute;
            top: 15px; right: 15px;
            background: var(--primary-color);
            color: white;
            width: 35px; height: 35px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem;
            opacity: 0;
            transform: scale(0.5);
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            z-index: 10;
        }
        input[type="radio"]:checked + .pilihan-card .check-icon {
            opacity: 1;
            transform: scale(1);
        }

        /* --- State KARTU JIKA SUDAH MEMILIH (Hijau & Abu-abu) --- */
        .card-selected {
            border-color: #10b981 !important; /* Hijau */
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.2) !important;
            background-color: #ecfdf5; /* Latar hijau sangat muda */
        }
        .card-selected .check-icon {
            background: #10b981 !important; /* Centang jadi hijau */
            opacity: 1 !important;
            transform: scale(1) !important;
        }
        .card-selected .nomor-urut { color: #10b981 !important; }

        .card-not-selected {
            opacity: 0.4;
            filter: grayscale(100%);
            pointer-events: none; /* Tidak bisa diklik */
        }

        .foto-wrapper {
            position: relative;
            padding-top: 100%;
            background-color: #e2e8f0;
        }
        .foto-wrapper img {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            object-fit: cover;
        }

        .nomor-urut {
            position: absolute;
            bottom: 15px; left: 15px;
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

        /* Sembunyikan radio button asli */
        .radio-hidden {
            position: absolute;
            opacity: 0;
            width: 0; height: 0;
        }

        .btn-vote {
            background-color: var(--primary-color);
            color: white;
            border-radius: 50px;
            padding: 15px 40px;
            font-weight: 700;
            font-size: 1.1rem;
            border: none;
            transition: all 0.3s;
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3);
        }
        .btn-vote:hover { background-color: var(--primary-hover); transform: translateY(-2px); color: white;}
        
        /* Section Terkunci */
        .section-locked {
            pointer-events: none; /* Hanya nonaktifkan klik */
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
            <div class="ms-auto">
                <span class="fw-bold text-dark me-3 d-none d-md-inline"><i class="bi bi-person-circle me-1"></i> <?= e($_SESSION['nama']) ?></span>
                <a href="<?= BASE_URL ?>/auth/logout.php" class="btn btn-danger rounded-pill px-4 btn-sm" onclick="return confirm('Yakin ingin logout?')">Logout</a>
            </div>
        </div>
    </nav>

    <!-- HEADER -->
    <header class="page-header">
        <div class="container">
            <h1 class="page-title">Bilik Suara</h1>
            <p class="text-muted fs-5 mb-0">Pilih dengan hati nurani. Suara Anda menentukan masa depan lingkungan.</p>
        </div>
    </header>

    <!-- KONTEN UTAMA -->
    <section class="py-5">
        <div class="container">
            
            <?php if ($flash = getFlash()): ?>
                <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show shadow-sm border-0" role="alert">
                    <i class="bi bi-info-circle-fill me-2"></i> <?= e($flash['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form action="<?= BASE_URL ?>/proses/simpan-voting.php" method="POST" id="formVoting" onsubmit="return konfirmasiVoting(event)">
                
                <!-- ========================================== -->
                <!-- SECTION VOTING KETUA RT                    -->
                <!-- ========================================== -->
                <div class="mb-5 pb-5 border-bottom">
                    <div class="d-flex justify-content-between align-items-end mb-4">
                        <div>
                            <h3 class="fw-bold mb-1"><i class="bi bi-house-door-fill text-primary me-2"></i> Pilih Ketua RT</h3>
                            <p class="text-muted mb-0">Pilih 1 calon Ketua RT dari daftar di bawah ini.</p>
                        </div>
                        <?php if ($sudah_vote_rt): ?>
                            <span class="badge bg-success fs-6 rounded-pill px-3 py-2"><i class="bi bi-check-circle-fill me-1"></i> Selesai</span>
                        <?php endif; ?>
                    </div>

                    <div class="row g-4 <?= $sudah_vote_rt ? 'section-locked' : '' ?>">
                        <?php foreach ($calon_rt as $c): 
                            // Logika penentuan class visual
                            $is_pilihan_saya = ($sudah_vote_rt && $id_pilihan_rt == $c['id']);
                            $is_terkunci = ($sudah_vote_rt && $id_pilihan_rt != $c['id']);
                            
                            $card_class = '';
                            if ($is_pilihan_saya) $card_class = 'card-selected';
                            if ($is_terkunci) $card_class = 'card-not-selected';
                        ?>
                            <div class="col-md-6 col-lg-3">
                                <label class="d-block h-100 m-0">
                                    <input type="radio" name="calon_rt" value="<?= $c['id'] ?>" class="radio-hidden" required <?= $sudah_vote_rt ? 'disabled' : '' ?> <?= $is_pilihan_saya ? 'checked' : '' ?>>
                                    
                                    <div class="pilihan-card <?= $card_class ?>">
                                        <div class="check-icon"><i class="bi bi-check-lg"></i></div>
                                        <div class="foto-wrapper">
                                            <?php 
                                                $foto = (!empty($c['foto']) && file_exists(__DIR__ . '/../uploads/' . $c['foto'])) 
                                                    ? BASE_URL . '/uploads/' . $c['foto'] 
                                                    : 'https://ui-avatars.com/api/?name=' . urlencode($c['nama']) . '&background=e0e7ff&color=4f46e5&size=400';
                                            ?>
                                            <img src="<?= $foto ?>" alt="<?= e($c['nama']) ?>">
                                            <div class="nomor-urut"><?= e($c['nomor_urut']) ?></div>
                                        </div>
                                        <div class="p-3 text-center bg-white">
                                            <h5 class="fw-bold mb-1 text-truncate"><?= e($c['nama']) ?></h5>
                                            <p class="text-muted small mb-0 text-truncate"><?= e($c['alamat']) ?></p>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- SECTION VOTING KETUA RW                    -->
                <!-- ========================================== -->
                <div class="mb-5">
                    <div class="d-flex justify-content-between align-items-end mb-4">
                        <div>
                            <h3 class="fw-bold mb-1"><i class="bi bi-buildings-fill text-info me-2"></i> Pilih Ketua RW</h3>
                            <p class="text-muted mb-0">Pilih 1 calon Ketua RW dari daftar di bawah ini.</p>
                        </div>
                        <?php if ($sudah_vote_rw): ?>
                            <span class="badge bg-success fs-6 rounded-pill px-3 py-2"><i class="bi bi-check-circle-fill me-1"></i> Selesai</span>
                        <?php endif; ?>
                    </div>

                    <div class="row g-4 <?= $sudah_vote_rw ? 'section-locked' : '' ?>">
                        <?php foreach ($calon_rw as $c): 
                            // Logika penentuan class visual
                            $is_pilihan_saya = ($sudah_vote_rw && $id_pilihan_rw == $c['id']);
                            $is_terkunci = ($sudah_vote_rw && $id_pilihan_rw != $c['id']);
                            
                            $card_class = '';
                            if ($is_pilihan_saya) $card_class = 'card-selected';
                            if ($is_terkunci) $card_class = 'card-not-selected';
                        ?>
                            <div class="col-md-6 col-lg-3">
                                <label class="d-block h-100 m-0">
                                    <input type="radio" name="calon_rw" value="<?= $c['id'] ?>" class="radio-hidden" required <?= $sudah_vote_rw ? 'disabled' : '' ?> <?= $is_pilihan_saya ? 'checked' : '' ?>>
                                    
                                    <div class="pilihan-card <?= $card_class ?>">
                                        <div class="check-icon"><i class="bi bi-check-lg"></i></div>
                                        <div class="foto-wrapper">
                                            <?php 
                                                $foto = (!empty($c['foto']) && file_exists(__DIR__ . '/../uploads/' . $c['foto'])) 
                                                    ? BASE_URL . '/uploads/' . $c['foto'] 
                                                    : 'https://ui-avatars.com/api/?name=' . urlencode($c['nama']) . '&background=e0f2fe&color=0284c7&size=400';
                                            ?>
                                            <img src="<?= $foto ?>" alt="<?= e($c['nama']) ?>">
                                            <div class="nomor-urut"><?= e($c['nomor_urut']) ?></div>
                                        </div>
                                        <div class="p-3 text-center bg-white">
                                            <h5 class="fw-bold mb-1 text-truncate"><?= e($c['nama']) ?></h5>
                                            <p class="text-muted small mb-0 text-truncate"><?= e($c['alamat']) ?></p>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- TOMBOL SUBMIT -->
                <div class="text-center mt-5 pt-3">
                    <?php if ($sudah_vote_rt && $sudah_vote_rw): ?>
                        <div class="alert alert-success d-inline-block px-4 py-3 rounded-4 shadow-sm border-0">
                            <h5 class="mb-1 fw-bold"><i class="bi bi-check-circle-fill me-2"></i> Terima Kasih!</h5>
                            <p class="mb-0 text-success">Anda sudah menggunakan hak suara Anda untuk kedua kategori.</p>
                        </div>
                        <br>
                        <a href="<?= BASE_URL ?>/pages/klasemen.php" class="btn btn-outline-primary btn-lg mt-3 rounded-pill px-5">Lihat Klasemen</a>
                    <?php else: ?>
                        <button type="submit" class="btn btn-vote">
                            <i class="bi bi-box-arrow-in-right me-2"></i> Kirim Suara Saya
                        </button>
                        <p class="text-muted small mt-3"><i class="bi bi-shield-lock-fill text-success me-1"></i> Suara Anda dijamin kerahasiaannya dan tidak dapat diubah setelah dikirim.</p>
                    <?php endif; ?>
                </div>

            </form>
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
    
    <!-- Script Konfirmasi Sebelum Submit -->
    <script>
        function konfirmasiVoting(event) {
            const isRtVoted = <?= $sudah_vote_rt ? 'true' : 'false' ?>;
            const isRwVoted = <?= $sudah_vote_rw ? 'true' : 'false' ?>;
            
            let cekRt = true;
            let cekRw = true;

            if (!isRtVoted) {
                cekRt = document.querySelector('input[name="calon_rt"]:checked') !== null;
            }
            if (!isRwVoted) {
                cekRw = document.querySelector('input[name="calon_rw"]:checked') !== null;
            }

            if (!cekRt || !cekRw) {
                event.preventDefault(); // Batalkan submit
                alert('Anda harus memilih calon untuk kategori yang belum Anda coblos!');
                return false;
            }

            const pesan = "Apakah Anda yakin dengan pilihan Anda?\n\nSuara yang sudah dikirim TIDAK DAPAT diubah atau dibatalkan.";
            return confirm(pesan);
        }
    </script>
</body>
</html>