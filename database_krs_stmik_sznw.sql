-- Database: `krs_stmik_sznw`
-- Sistem Informasi Pengisian KRS Online
-- Kampus: STMIK SYAIKH ZAINUDDIN NW ANJANI LOMBOK TIMUR

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+08:00";

-- 1. Tabel Admin BAAK
CREATE TABLE IF NOT EXISTS `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,
  `nama_admin` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `admin` (`username`, `nama_admin`, `password`) VALUES
('admin', 'Administrator BAAK STMIK SZ NW Anjani', 'admin123');

-- 2. Tabel Bendahara / Keuangan Kampus
CREATE TABLE IF NOT EXISTS `biku` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,
  `nama_bendahara` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `biku` (`username`, `nama_bendahara`, `password`) VALUES
('bendahara', 'Bendahara Keuangan STMIK SZ NW Anjani', 'bendahara123');

-- 3. Tabel Dosen & Kaprodi
CREATE TABLE IF NOT EXISTS `dosen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nip` varchar(30) NOT NULL UNIQUE,
  `nama` varchar(100) NOT NULL,
  `jabatan` enum('Dosen Pembimbing','Kaprodi') NOT NULL DEFAULT 'Dosen Pembimbing',
  `prodi` varchar(100) NOT NULL DEFAULT 'Teknik Informatika',
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `dosen` (`nip`, `nama`, `jabatan`, `prodi`, `password`) VALUES
('0810018801', 'Ahmad Fauzi, M.Kom.', 'Kaprodi', 'Teknik Informatika', 'dosen123'),
('0815048902', 'Baiq Nurul Hidayah, M.T.', 'Dosen Pembimbing', 'Teknik Informatika', 'dosen123'),
('0820079003', 'Lalu Muhammad Rizki, M.Kom.', 'Kaprodi', 'Sistem Informasi', 'dosen123');

-- 4. Tabel Mahasiswa (Akun dibuat dari NIM & Nama Lengkap)
CREATE TABLE IF NOT EXISTS `mhs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `NIM` varchar(25) NOT NULL UNIQUE,
  `Nama` varchar(100) NOT NULL,
  `Password` varchar(255) DEFAULT NULL,
  `is_activated` tinyint(1) NOT NULL DEFAULT 0,
  `Progdi` varchar(100) NOT NULL,
  `semester_aktif` int(11) NOT NULL DEFAULT 1,
  `ipk_kumulatif` decimal(3,2) DEFAULT NULL,
  `nip_pa` varchar(30) DEFAULT '0815048902',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `mhs` (`NIM`, `Nama`, `Password`, `is_activated`, `Progdi`, `semester_aktif`, `nip_pa`) VALUES
('202401001', 'Muhammad Zulfikar', 'mhs123', 1, 'Teknik Informatika', 3, '0815048902'),
('202401002', 'Siti Aisyah Putri', NULL, 0, 'Teknik Informatika', 3, '0815048902'),
('202402001', 'Lalu Hendra Wijaya', 'mhs123', 1, 'Sistem Informasi', 1, '0820079003');

-- 5. Tabel Tagihan & Simulasi Pembayaran DEMO
CREATE TABLE IF NOT EXISTS `data_keuangan` (
  `id_keuangan` int(11) NOT NULL AUTO_INCREMENT,
  `id` int(11) NOT NULL,
  `NIM` varchar(25) NOT NULL,
  `semester` varchar(30) NOT NULL DEFAULT 'Ganjil 2026/2027',
  `kewajiban` int(11) NOT NULL DEFAULT 2500000,
  `status` enum('Belum Terbayar','Pending','Terbayar') NOT NULL DEFAULT 'Belum Terbayar',
  `order_id` varchar(60) DEFAULT NULL,
  `metode_bayar` varchar(80) DEFAULT NULL,
  `va_number` varchar(50) DEFAULT NULL,
  `waktu_bayar` datetime DEFAULT NULL,
  `kode_bukti` varchar(80) DEFAULT NULL,
  PRIMARY KEY (`id_keuangan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `data_keuangan` (`id`, `NIM`, `semester`, `kewajiban`, `status`, `order_id`, `metode_bayar`, `va_number`, `waktu_bayar`, `kode_bukti`) VALUES
(1, '202401001', 'Ganjil 2026/2027', 2500000, 'Terbayar', 'DEMO-SZNW-20261118-001', 'SIMULASI DEMO', NULL, '2026-11-18 09:15:22', 'DEMO-SZNW-9981273A'),
(2, '202401002', 'Ganjil 2026/2027', 2500000, 'Belum Terbayar', NULL, NULL, NULL, NULL, NULL),
(3, '202402001', 'Ganjil 2026/2027', 2500000, 'Belum Terbayar', NULL, NULL, NULL, NULL, NULL);

-- 6. Tabel Mata Kuliah
CREATE TABLE IF NOT EXISTS `data_matkul` (
  `id_matkul` int(11) NOT NULL AUTO_INCREMENT,
  `kodemk` varchar(20) NOT NULL UNIQUE,
  `namamk` varchar(100) NOT NULL,
  `sks` int(11) NOT NULL,
  `tp` varchar(10) NOT NULL DEFAULT 'T/P',
  `semester` int(11) NOT NULL,
  `prodi` varchar(100) NOT NULL DEFAULT 'Teknik Informatika',
  PRIMARY KEY (`id_matkul`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `data_matkul` (`kodemk`, `namamk`, `sks`, `tp`, `semester`, `prodi`) VALUES
('TI101', 'Algoritma dan Pemrograman', 4, 'T/P', 1, 'Teknik Informatika'),
('TI102', 'Pengantar Teknologi Informasi', 3, 'T', 1, 'Teknik Informatika'),
('TI301', 'Pemrograman Web Lanjut', 4, 'T/P', 3, 'Teknik Informatika'),
('TI302', 'Sistem Basis Data', 3, 'T/P', 3, 'Teknik Informatika'),
('TI303', 'Jaringan Komputer', 3, 'T/P', 3, 'Teknik Informatika'),
('TI304', 'Metode Penelitian (METOPEN)', 3, 'T', 3, 'Teknik Informatika'),
('TI501', 'Rekayasa Perangkat Lunak', 3, 'T', 5, 'Teknik Informatika'),
('SI101', 'Konsep Sistem Informasi', 3, 'T', 1, 'Sistem Informasi'),
('SI102', 'Manajemen Proses Bisnis', 3, 'T', 1, 'Sistem Informasi');

-- 7. Tabel Ruang Kuliah
CREATE TABLE IF NOT EXISTS `data_ruang` (
  `id_ruang` int(11) NOT NULL AUTO_INCREMENT,
  `koderuang` varchar(20) NOT NULL UNIQUE,
  `kapasitas` int(11) NOT NULL,
  PRIMARY KEY (`id_ruang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `data_ruang` (`koderuang`, `kapasitas`) VALUES
('R.101', 45), ('R.102', 45), ('LAB-KOM 1', 40), ('LAB-KOM 2', 40);

-- 8. Tabel Jadwal Kuliah
CREATE TABLE IF NOT EXISTS `data_jadwal` (
  `id_jadwal` int(11) NOT NULL AUTO_INCREMENT,
  `kodemk` varchar(20) NOT NULL,
  `kelompok` varchar(20) NOT NULL,
  `hari` varchar(20) NOT NULL,
  `jammulai` varchar(10) NOT NULL,
  `jamselesai` varchar(10) NOT NULL,
  `koderuang` varchar(20) NOT NULL,
  `nip_dosen` varchar(30) DEFAULT '0815048902',
  PRIMARY KEY (`id_jadwal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `data_jadwal` (`kodemk`, `kelompok`, `hari`, `jammulai`, `jamselesai`, `koderuang`, `nip_dosen`) VALUES
('TI101', 'TI-1A', 'Senin', '08.00', '10.30', 'LAB-KOM 1', '0815048902'),
('TI102', 'TI-1A', 'Selasa', '08.00', '10.30', 'R.101', '0810018801'),
('TI301', 'TI-3A', 'Senin', '08.00', '10.30', 'LAB-KOM 2', '0810018801'),
('TI302', 'TI-3A', 'Selasa', '10.30', '13.00', 'LAB-KOM 1', '0815048902'),
('TI303', 'TI-3A', 'Rabu', '08.00', '10.30', 'R.102', '0815048902'),
('TI304', 'TI-3A', 'Kamis', '10.30', '13.00', 'R.101', '0810018801'),
('TI501', 'TI-5A', 'Jumat', '08.00', '10.30', 'R.102', '0810018801'),
('SI101', 'SI-1A', 'Senin', '10.30', '13.00', 'R.101', '0820079003'),
('SI102', 'SI-1A', 'Rabu', '10.30', '13.00', 'R.101', '0820079003');

-- 9. Tabel KRS Mahasiswa (Dengan Persetujuan Digital Dosen Pembimbing & Kaprodi)
CREATE TABLE IF NOT EXISTS `data_krsmhs` (
  `id_krs` int(11) NOT NULL AUTO_INCREMENT,
  `id_jadwal` int(11) NOT NULL,
  `NIM` varchar(25) NOT NULL,
  `status_pa` enum('Menunggu','Disetujui','Ditolak') NOT NULL DEFAULT 'Menunggu',
  `status_kaprodi` enum('Menunggu','Disetujui','Ditolak') NOT NULL DEFAULT 'Menunggu',
  `tgl_pengajuan` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_krs`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `data_krsmhs` (`id_jadwal`, `NIM`, `status_pa`, `status_kaprodi`) VALUES
(3, '202401001', 'Disetujui', 'Disetujui'),
(4, '202401001', 'Disetujui', 'Disetujui');

-- 10. Tabel Jadwal Buka/Tutup Pengisian KRS
CREATE TABLE IF NOT EXISTS `data_waktu` (
  `id_waktu` int(11) NOT NULL AUTO_INCREMENT,
  `tahun_akademik` varchar(40) NOT NULL DEFAULT 'Ganjil 2026/2027',
  `tanggal_buka` date NOT NULL,
  `tanggal_tutup` date NOT NULL,
  `status_aktif` enum('Buka','Tutup') NOT NULL DEFAULT 'Buka',
  `ambang_ipk` decimal(3,2) DEFAULT NULL,
  PRIMARY KEY (`id_waktu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `data_waktu` (`tahun_akademik`, `tanggal_buka`, `tanggal_tutup`, `status_aktif`) VALUES
('Ganjil 2026/2027', '2026-08-01', '2027-02-28', 'Buka');

-- 12. Tabel Pengumuman Kampus
CREATE TABLE IF NOT EXISTS `data_pengumuman` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `judul` varchar(150) NOT NULL,
  `author` varchar(80) NOT NULL,
  `isi` text NOT NULL,
  `tanggal` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `data_pengumuman` (`judul`, `author`, `isi`, `tanggal`) VALUES
('Pembukaan Pengisian KRS Online Semester Ganjil 2026/2027', 'BAAK STMIK SZ NW Anjani', 'Mahasiswa diwajibkan melakukan aktivasi akun menggunakan NIM & Nama Lengkap, menggunakan simulasi pembayaran DEMO untuk pengujian akses KRS, kemudian mengisi KRS serta menunggu persetujuan online dari Dosen Pembimbing dan Kaprodi.', '2026-11-01');
