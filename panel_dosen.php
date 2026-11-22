<?php
require_once "koneksi.php";
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'dosen') {
    header("Location: index.php");
    exit;
}

$nip     = esc($_SESSION['nip']);
$jabatan = $_SESSION['jabatan']; // 'Dosen Pembimbing' atau 'Kaprodi'
$prodi   = esc($_SESSION['prodi']);
$msg     = "";

// Proses Persetujuan Digital KRS (Menggantikan Tanda Tangan Fisik)
if (isset($_GET['aksi']) && isset($_GET['id_krs'])) {
    $id_krs = (int)$_GET['id_krs'];
    $status = ($_GET['aksi'] === 'setuju') ? 'Disetujui' : 'Ditolak';

    if ($jabatan === 'Kaprodi') {
        mysqli_query($conn, "UPDATE data_krsmhs SET status_kaprodi='$status' WHERE id_krs=$id_krs");
        $msg = "Pengesahan online Kaprodi berhasil diperbarui menjadi: <b>$status</b>.";
    } else {
        mysqli_query($conn, "UPDATE data_krsmhs SET status_pa='$status' WHERE id_krs=$id_krs");
        $msg = "Persetujuan online Dosen Pembimbing berhasil diperbarui menjadi: <b>$status</b>.";
    }
}

// Query pengajuan KRS mahasiswa sesuai peran (Dosen PA melihat bimbingannya, Kaprodi melihat seluruh prodi)
if ($jabatan === 'Kaprodi') {
    $q_krs = mysqli_query($conn, "SELECT k.*, m.Nama AS nama_mhs, m.Progdi, j.kodemk, j.kelompok, j.hari, j.jammulai, j.jamselesai, mk.namamk, mk.sks
        FROM data_krsmhs k
        JOIN mhs m ON k.NIM = m.NIM
        JOIN data_jadwal j ON k.id_jadwal = j.id_jadwal
        JOIN data_matkul mk ON j.kodemk = mk.kodemk
        WHERE m.Progdi = '$prodi'
        ORDER BY k.id_krs DESC");
} else {
    $q_krs = mysqli_query($conn, "SELECT k.*, m.Nama AS nama_mhs, m.Progdi, j.kodemk, j.kelompok, j.hari, j.jammulai, j.jamselesai, mk.namamk, mk.sks
        FROM data_krsmhs k
        JOIN mhs m ON k.NIM = m.NIM
        JOIN data_jadwal j ON k.id_jadwal = j.id_jadwal
        JOIN data_matkul mk ON j.kodemk = mk.kodemk
        WHERE m.nip_pa = '$nip'
        ORDER BY k.id_krs DESC");
}

include "header.php";
?>
<div class="topbar">
    <div class="brand">
        <div class="brand-badge"><?= strtoupper($jabatan) ?></div>
        <div>
            <h1>STMIK SZ NW ANJANI LOMBOK TIMUR</h1>
            <p><?= htmlspecialchars($_SESSION['nama']) ?> (NIP: <?= htmlspecialchars($nip) ?>) — Prodi <?= htmlspecialchars($prodi) ?></p>
        </div>
    </div>
    <div class="nav-links">
        <a href="panel_dosen.php" class="active">Persetujuan KRS</a>
        <a href="logout.php" class="logout">Keluar</a>
    </div>
</div>

<div class="container">
    <?php if ($msg): ?>
        <div class="alert alert-success"><?= $msg ?></div>
    <?php endif; ?>

    <!-- Modul Persetujuan Digital KRS (Tanpa Tanda Tangan Basah) -->
    <div class="card">
        <div class="card-header">
            <h2>Persetujuan KRS Online Mahasiswa (<?= htmlspecialchars($jabatan) ?>)</h2>
            <span class="badge badge-info">Pengganti Tanda Tangan Fisik</span>
        </div>
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>NIM</th>
                        <th>Nama Mahasiswa</th>
                        <th>Mata Kuliah & SKS</th>
                        <th>Kelas & Jadwal</th>
                        <th>Status PA</th>
                        <th>Status Kaprodi</th>
                        <th>Aksi Validasi Online</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($r = mysqli_fetch_assoc($q_krs)): ?>
                    <tr>
                        <td><b><?= htmlspecialchars($r['NIM']) ?></b></td>
                        <td><?= htmlspecialchars($r['nama_mhs']) ?></td>
                        <td><?= htmlspecialchars($r['kodemk']) ?> — <?= htmlspecialchars($r['namamk']) ?> (<?= $r['sks'] ?> SKS)</td>
                        <td><?= htmlspecialchars($r['kelompok']) ?> | <?= $r['hari'] ?>, <?= $r['jammulai'] ?></td>
                        <td><span class="badge <?= $r['status_pa']==='Disetujui'?'badge-success':($r['status_pa']==='Ditolak'?'badge-danger':'badge-warning') ?>"><?= $r['status_pa'] ?></span></td>
                        <td><span class="badge <?= $r['status_kaprodi']==='Disetujui'?'badge-success':($r['status_kaprodi']==='Ditolak'?'badge-danger':'badge-warning') ?>"><?= $r['status_kaprodi'] ?></span></td>
                        <td>
                            <a href="panel_dosen.php?aksi=setuju&id_krs=<?= $r['id_krs'] ?>" class="btn btn-primary btn-sm">Setujui</a>
                            <a href="panel_dosen.php?aksi=tolak&id_krs=<?= $r['id_krs'] ?>" class="btn btn-danger btn-sm">Tolak</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="footer">&copy; 2026 STMIK Syaikh Zainuddin NW Anjani Lombok Timur</div>
</body>
</html>
