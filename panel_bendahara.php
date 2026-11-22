<?php
require_once "koneksi.php";
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'bendahara') {
    header("Location: index.php");
    exit;
}

$stat_lunas = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS jml, COALESCE(SUM(kewajiban),0) AS tot FROM data_keuangan WHERE status='Terbayar'"));
$stat_belum = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS jml FROM data_keuangan WHERE status!='Terbayar'"));

$list_trx = mysqli_query($conn, "SELECT k.*, m.Nama, m.Progdi FROM data_keuangan k JOIN mhs m ON k.NIM=m.NIM ORDER BY k.waktu_bayar DESC, k.id_keuangan DESC");

include "header.php";
?>
<div class="topbar">
    <div class="brand">
        <div class="brand-badge">BENDAHARA KAMPUS</div>
        <div>
            <h1>STMIK SZ NW ANJANI LOMBOK TIMUR</h1>
            <p>Rekap simulasi lokal — bukan transaksi nyata (<?= htmlspecialchars($_SESSION['nama']) ?>)</p>
        </div>
    </div>
    <div class="nav-links">
        <a href="panel_bendahara.php" class="active">Rekap Pembayaran Demo</a>
        <a href="logout.php" class="logout">Keluar</a>
    </div>
</div>

<div class="container">
    <div class="grid-3">
        <div class="stat-box">
            <div class="label">Simulasi Selesai</div>
            <div class="val"><?= $stat_lunas['jml'] ?> Mahasiswa</div>
            <div style="font-size:12px; color:var(--muted); margin-top:4px;">Status demo, bukan verifikasi bank</div>
        </div>
        <div class="stat-box" style="border-left-color:#d97706;">
            <div class="label">Belum Terbayar / Pending</div>
            <div class="val"><?= $stat_belum['jml'] ?> Mahasiswa</div>
        </div>
        <div class="stat-box" style="border-left-color:#0284c7;">
            <div class="label">Total Nominal Contoh</div>
            <div class="val"><?= rupiah($stat_lunas['tot']) ?></div>
        </div>
    </div>

    <div class="card" style="margin-top:20px;">
        <div class="card-header">
            <h2>Rekapitulasi Simulasi Pembayaran</h2>
            <button onclick="window.print()" class="btn btn-primary btn-sm no-print">Cetak Laporan Keuangan</button>
        </div>
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>NIM</th>
                        <th>Nama Mahasiswa</th>
                        <th>Program Studi</th>
                        <th>Nominal</th>
                        <th>Mode</th>
                        <th>Kode Demo</th>
                        <th>Waktu Simulasi</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no=1; while ($t = mysqli_fetch_assoc($list_trx)): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><b><?= htmlspecialchars($t['NIM']) ?></b></td>
                        <td><?= htmlspecialchars($t['Nama']) ?></td>
                        <td><?= htmlspecialchars($t['Progdi']) ?></td>
                        <td><?= rupiah($t['kewajiban']) ?></td>
                        <td><?= htmlspecialchars($t['metode_bayar'] ?? '-') ?></td>
                        <td><code><?= htmlspecialchars($t['kode_bukti'] ?? '-') ?></code></td>
                        <td><?= htmlspecialchars($t['waktu_bayar'] ?? '-') ?></td>
                        <td>
                            <span class="badge <?= $t['status']==='Terbayar'?'badge-success':($t['status']==='Pending'?'badge-warning':'badge-danger') ?>">
                                <?= $t['status'] === 'Terbayar' ? 'SELESAI DEMO' : htmlspecialchars($t['status']) ?>
                            </span>
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
