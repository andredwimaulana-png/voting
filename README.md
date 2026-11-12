# 🗳️ Sistem Informasi Pemilihan RT/RW Berbasis Web (E-Voting)

Aplikasi web modern dan responsif yang dirancang untuk memfasilitasi proses pemilihan Ketua RT dan Ketua RW secara digital (E-Voting). Proyek ini dibangun untuk memenuhi tugas praktik *Web Development* tingkat SMK, dengan fokus pada keamanan data, kemudahan penggunaan (UI/UX), dan transparansi hasil.

Aplikasi ini terbagi menjadi dua bagian utama: **Halaman Pemilih (Publik)** dan **Panel Administrator (Admin)**.

---

## 🚀 Fitur Utama

### 1. 👥 Halaman Pemilih (Masyarakat)
Halaman ini digunakan oleh warga untuk melihat informasi calon dan memberikan suara.
- **Beranda Interaktif:** Menampilkan informasi jadwal pemilihan, status bilik suara (Aktif/Tutup), serta statistik *real-time* (jumlah calon, pemilih, dan suara masuk).
- **Profil Calon Lengkap:** Daftar calon Ketua RT dan RW ditampilkan dalam bentuk *card* modern. Dilengkapi filter jabatan tanpa *refresh* halaman.
- **Detail Calon:** Menampilkan profil lengkap, Visi, Misi, Program Kerja, serta foto resolusi tinggi.
- **Autentikasi Aman:** Sistem registrasi dan login untuk warga. Password di-*hash* menggunakan algoritma bawaan PHP (`password_hash`).
- **Bilik Suara (Voting) Cerdas:** 
  - Antarmuka mencoblos dengan efek visual interaktif.
  - Validasi ganda (sisi *client* dan *server*) untuk mencegah *double voting*.
  - **Fitur Kunci Warna:** Setelah warga memilih, kartu calon yang dipilih akan berubah menjadi **Hijau**, dan calon yang tidak dipilih menjadi **Abu-abu Pudar** (tidak bisa diklik lagi).
- **Klasemen Real-Time:** Menampilkan perolehan suara sementara dengan visualisasi **Podium Juara 1, 2, dan 3**, serta *progress bar* persentase suara.

### 2. 🛡️ Panel Administrator
Panel khusus panitia pemilihan untuk mengelola seluruh data aplikasi.
- **Dashboard Eksekutif:** Ringkasan statistik data calon, pemilih, total suara, dan persentase partisipasi warga.
- **Kelola Calon (CRUD):** Admin dapat menambah, mengedit, dan menghapus data calon.
  - Dilengkapi fitur **Upload Foto** dengan validasi format (JPG/PNG/WEBP) dan batasan ukuran (Maks 2MB).
  - Sistem otomatis menghapus file foto lama dari server jika admin mengganti foto calon.
- **Manajemen Pemilih:** Melihat daftar warga terdaftar, mencari data, serta melihat status partisipasi (Lengkap / Sebagian / Belum Memilih).
- **Audit Trail (Riwayat Voting):** Transparansi data yang mencatat secara detail *siapa* memilih *calon mana*, dan *kapan* waktunya.
- **Kontrol Periode (Buka/Tutup):** Fitur "saklar" untuk membuka atau menutup bilik suara. Hanya boleh ada satu periode yang aktif dalam satu waktu.
- **Laporan Hasil Akhir:** Rekapitulasi hasil akhir yang dirancang khusus agar **siap dicetak (Print/PDF)** dengan kop surat dan kolom tanda tangan panitia.

---

## 💻 Teknologi yang Digunakan

- **Backend:** PHP 8.x (Native, tanpa framework)
- **Database:** MySQL / MariaDB (Menggunakan PDO untuk keamanan query)
- **Frontend:** HTML5, CSS3, Vanilla JavaScript
- **Framework CSS:** Bootstrap 5.3
- **Ikon & Font:** Bootstrap Icons, Google Fonts (Plus Jakarta Sans)

---

## 🔒 Sistem Keamanan yang Diterapkan

1. **Anti SQL Injection:** Seluruh query ke database menggunakan *Prepared Statements* (PDO).
2. **Anti XSS (Cross-Site Scripting):** Output di-*escape* menggunakan *helper* `htmlspecialchars()`.
3. **Enkripsi Password:** Menggunakan `password_hash()` saat registrasi dan `password_verify()` saat login.
4. **Pencegahan Double Voting:** 
   - **Sisi Database:** Menerapkan `UNIQUE KEY (user_id, jabatan)` pada tabel voting.
   - **Sisi Server:** Menggunakan *Database Transaction* (`beginTransaction` & `commit`) dengan penguncian baris (`FOR UPDATE`) agar tidak terjadi *race condition* jika user menekan tombol submit dua kali secara bersamaan.
5. **Proteksi Sesi:** `session_regenerate_id()` saat login sukses untuk mencegah *Session Fixation*.
6. **Validasi File Upload:** Memvalidasi *MIME type* dan ekstensi file gambar untuk mencegah *upload* skrip berbahaya.

---

## ⚙️ Cara Instalasi (Localhost)

Ikuti langkah berikut untuk menjalankan proyek ini di komputer Anda:

1. **Persiapan:**
   - Pastikan Anda sudah menginstal **XAMPP** (Apache & MySQL).
2. **Unduh Proyek:**
   - *Clone* repositori ini atau unduh sebagai ZIP.
   - Ekstrak folder proyek ke dalam direktori `htdocs` XAMPP Anda (contoh: `C:\xampp\htdocs\vote_suara`).
3. **Konfigurasi Database:**
   - Jalankan Apache dan MySQL di XAMPP Control Panel.
   - Buka `http://localhost/phpmyadmin`.
   - Buat database baru bernama `db_voting_rt_rw`.
   - Lakukan *Import* file SQL (jika ada di repositori) ke dalam database tersebut.
4. **Pengaturan Koneksi:**
   - Buka file `config/koneksi.php`.
   - Sesuaikan `DB_USER`, `DB_PASS`, dan `BASE_URL` jika nama folder proyek Anda berbeda.
     ```php
     define('DB_HOST', 'localhost');
     define('DB_NAME', 'db_voting_rt_rw');
     define('DB_USER', 'root');
     define('DB_PASS', ''); 
     define('BASE_URL', '/vote_suara'); // Sesuaikan dengan nama folder Anda
