<?php
require_once "koneksi.php";
require_once "aturan_sks.php";
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: index.php");
    exit;
}

$nim = esc($_SESSION['nim']);
$mhs = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM mhs WHERE NIM='$nim'"));
$keu = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM data_keuangan WHERE NIM='$nim' ORDER BY id_keuangan DESC LIMIT 1"));
$wkt_krs = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM data_waktu WHERE id_waktu=1"));
$ambang_ipk = $wkt_krs ? $wkt_krs['ambang_ipk'] : null;
$batas_sks = batas_sks($mhs['semester_aktif'], $mhs['ipk_kumulatif'], $ambang_ipk);
if (!$keu) {
    mysqli_query($conn, "INSERT INTO data_keuangan (id, NIM, kewajiban, status) VALUES ({$mhs['id']}, '$nim', 2500000, 'Belum Terbayar')");
    $keu = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM data_keuangan WHERE NIM='$nim' ORDER BY id_keuangan DESC LIMIT 1"));
}

// Hitung SKS diambil
$q_sks = mysqli_query($conn, "SELECT COALESCE(SUM(m.sks),0) AS total_sks FROM data_krsmhs k JOIN data_jadwal j ON k.id_jadwal=j.id_jadwal JOIN data_matkul m ON j.kodemk=m.kodemk WHERE k.NIM='$nim'");
$total_sks = (int)mysqli_fetch_assoc($q_sks)['total_sks'];

$pengumuman = mysqli_query($conn, "SELECT * FROM data_pengumuman ORDER BY id DESC LIMIT 3");

include "header.php";
?>
<div class="topbar">
    <div class="brand">
        <div class="brand-badge">MAHASISWA</div>
        <div>
            <h1>STMIK SZ NW ANJANI LOMBOK TIMUR</h1>
            <p><?= htmlspecialchars($mhs['Nama']) ?> (NIM: <?= htmlspecialchars($mhs['NIM']) ?>) — Prodi <?= htmlspecialchars($mhs['Progdi']) ?></p>
        </div>
    </div>
    <div class="nav-links">
        <a href="panel_mhs.php" class="active">Dashboard</a>
        <a href="pembayaran_mhs.php">Pembayaran & Bukti</a>
        <a href="krs_mhs.php">Pengisian KRS</a>
        <a href="logout.php" class="logout">Keluar</a>
    </div>
</div>

<div class="container">
    <div class="grid-3">
        <div class="stat-box">
            <div class="label">Status Pembayaran Semester</div>
            <div class="val">
                <?php if ($keu['status'] === 'Terbayar'): ?>
                    <span class="badge badge-success">LUNAS DEMO</span>
                <?php elseif ($keu['status'] === 'Pending'): ?>
                    <span class="badge badge-warning">MENUNGGU PEMBAYARAN</span>
                <?php else: ?>
                    <span class="badge badge-danger">BELUM TERBAYAR</span>
                <?php endif; ?>
            </div>
            <div style="margin-top:8px; font-size:12.5px; color:var(--muted);">
                Tagihan: <b><?= rupiah($keu['kewajiban']) ?></b>
            </div>
        </div>
        <div class="stat-box" style="border-left-color: #d97706;">
            <div class="label">Beban Studi Diambil (KRS)</div>
            <div class="val"><?= $total_sks ?> / <?= $batas_sks ?> SKS</div>
            <div style="margin-top:8px; font-size:12.5px; color:var(--muted);">
                Semester Aktif: <b>Semester <?= $mhs['semester_aktif'] ?></b>
                · IPK Kumulatif: <b><?= $mhs['ipk_kumulatif'] === null ? 'Belum dicatat' : number_format((float)$mhs['ipk_kumulatif'], 2, ',', '.') ?></b>
            </div>
        </div>
        <div class="stat-box" style="border-left-color: #0284c7;">
            <div class="label">Riwayat Pembayaran Demo</div>
            <div class="val" style="font-size:15px;">
                <?= $keu['kode_bukti'] ? htmlspecialchars($keu['kode_bukti']) : 'Belum Ada Bukti' ?>
            </div>
            <div style="margin-top:8px;">
                <a href="pembayaran_mhs.php" class="btn btn-primary btn-sm">Buka Menu Pembayaran</a>
            </div>
        </div>
    </div>

    <!-- Alur Langkah Mahasiswa -->
    <div class="card" style="margin-top:20px;">
        <div class="card-header">
            <h2>Alur Pengisian KRS Online</h2>
        </div>
        <div class="grid-2">
            <div>
                <h4 style="color:var(--primary);">1. Simulasi Pembayaran (DEMO)</h4>
                <p style="font-size:13px; color:var(--muted); margin-top:4px;">
                    Gunakan simulasi lokal untuk mencoba alur pembayaran. Simulasi tidak terhubung ke Midtrans dan bukan pembayaran sungguhan.
                </p>
                <a href="pembayaran_mhs.php" class="btn btn-outline btn-sm" style="margin-top:8px;">Cek Tagihan & Bukti &rarr;</a>
            </div>
            <div>
                <h4 style="color:var(--primary);">2. Pengisian & Persetujuan KRS</h4>
                <p style="font-size:13px; color:var(--muted); margin-top:4px;">
                    Pilih mata kuliah sesuai semester/kelas. Pengesahan dilakukan secara online oleh Dosen Pembimbing & Kaprodi tanpa perlu tanda tangan kertas.
                </p>
                <a href="krs_mhs.php" class="btn btn-outline btn-sm" style="margin-top:8px;">Isi / Lihat KRS &rarr;</a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>Pengumuman Akademik Terbaru</h2>
        </div>
        <?php while ($p = mysqli_fetch_assoc($pengumuman)): ?>
            <div style="padding:12px 0; border-bottom:1px solid var(--border);">
                <div style="font-weight:700; color:var(--primary-dark);"><?= htmlspecialchars($p['judul']) ?></div>
                <div style="font-size:12px; color:var(--muted); margin-bottom:4px;">Oleh: <?= htmlspecialchars($p['author']) ?> | <?= $p['tanggal'] ?></div>
                <p style="font-size:13.5px;"><?= nl2br(htmlspecialchars($p['isi'])) ?></p>
            </div>
        <?php endwhile; ?>
    </div>
</div>
<div class="footer">&copy; 2026 STMIK Syaikh Zainuddin NW Anjani Lombok Timur</div>
</body>
</html>
