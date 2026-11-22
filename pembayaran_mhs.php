<?php
require_once "koneksi.php";
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: index.php");
    exit;
}

$nim = esc($_SESSION['nim']);
$mhs = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM mhs WHERE NIM='$nim'"));
$msg = "";
$msg_type = "success";
$keu_result = mysqli_query($conn, "SELECT * FROM data_keuangan WHERE NIM='$nim' ORDER BY id_keuangan DESC LIMIT 1");
if (!$keu_result) {
    error_log("Failed to load demo payment for $nim: " . mysqli_error($conn));
    http_response_code(500);
    die("Gagal memuat data tagihan.");
}
$keu = mysqli_fetch_assoc($keu_result);
if (!$keu) {
    http_response_code(404);
    die("Data tagihan tidak ditemukan. Silakan hubungi administrator.");
}

// Local demonstration only; this does not contact Midtrans or move real money.
if (isset($_POST['buat_invoice'])) {
    $order_id = "DEMO-SZNW-" . date("YmdHis") . "-" . strtoupper(bin2hex(random_bytes(3)));
    $updated = mysqli_query($conn, "UPDATE data_keuangan
        SET status='Pending', order_id='$order_id', metode_bayar='SIMULASI DEMO',
            va_number=NULL, waktu_bayar=NULL, kode_bukti=NULL
        WHERE id_keuangan=" . (int)$keu['id_keuangan'] . " AND NIM='$nim' AND status='Belum Terbayar'");

    if (!$updated) {
        error_log("Failed to create demo payment for $nim: " . mysqli_error($conn));
        $msg = "Gagal membuat tagihan demo. Silakan coba kembali.";
        $msg_type = "danger";
    } elseif (mysqli_affected_rows($conn) !== 1) {
        $msg = "Tagihan demo tidak dibuat karena status pembayaran sudah berubah. Muat ulang halaman.";
        $msg_type = "warning";
    } else {
        $msg = "Tagihan demo dibuat. Ini hanya simulasi lokal untuk demonstrasi, bukan transaksi Midtrans.";
    }
}

if (isset($_POST['konfirmasi_demo'])) {
    $pending = mysqli_query($conn, "SELECT id_keuangan FROM data_keuangan
        WHERE id_keuangan=" . (int)$keu['id_keuangan'] . " AND NIM='$nim' AND status='Pending' AND metode_bayar='SIMULASI DEMO'
            AND order_id LIKE 'DEMO-SZNW-%'
        LIMIT 1");

    if (!$pending) {
        error_log("Failed to check pending demo payment for $nim: " . mysqli_error($conn));
        $msg = "Gagal memeriksa tagihan demo. Silakan coba kembali.";
        $msg_type = "danger";
    } elseif ($trx = mysqli_fetch_assoc($pending)) {
        $waktu = date("Y-m-d H:i:s");
        $kode_bukti = "DEMO-" . strtoupper(bin2hex(random_bytes(4)));
        $updated = mysqli_query($conn, "UPDATE data_keuangan
            SET status='Terbayar', waktu_bayar='$waktu', kode_bukti='$kode_bukti'
            WHERE id_keuangan=" . (int)$trx['id_keuangan'] . " AND NIM='$nim' AND status='Pending'");

        if (!$updated) {
            error_log("Failed to confirm demo payment for $nim: " . mysqli_error($conn));
            $msg = "Gagal menyimpan hasil simulasi. Silakan coba kembali.";
            $msg_type = "danger";
        } elseif (mysqli_affected_rows($conn) !== 1) {
            $msg = "Status tagihan demo sudah berubah. Muat ulang halaman.";
            $msg_type = "warning";
        } else {
            $msg = "Simulasi selesai. Status demo telah diperbarui dan akses KRS dibuka untuk pengujian. Ini bukan pembayaran asli.";
        }
    } else {
        $msg = "Tidak ada tagihan demo yang menunggu konfirmasi.";
        $msg_type = "warning";
    }
}

$keu_result = mysqli_query($conn, "SELECT * FROM data_keuangan WHERE NIM='$nim' ORDER BY id_keuangan DESC LIMIT 1");
if (!$keu_result) {
    error_log("Failed to reload demo payment for $nim: " . mysqli_error($conn));
    http_response_code(500);
    die("Gagal memuat status tagihan.");
}
$keu = mysqli_fetch_assoc($keu_result);
include "header.php";
?>
<div class="topbar">
    <div class="brand">
        <div class="brand-badge">PEMBAYARAN DEMO</div>
        <div>
            <h1>STMIK SZ NW ANJANI LOMBOK TIMUR</h1>
            <p><?= htmlspecialchars($mhs['Nama']) ?> (<?= htmlspecialchars($mhs['NIM']) ?>) · Simulasi lokal</p>
        </div>
    </div>
    <div class="nav-links">
        <a href="panel_mhs.php">Dashboard</a>
        <a href="pembayaran_mhs.php" class="active">Pembayaran & Bukti</a>
        <a href="krs_mhs.php">Pengisian KRS</a>
        <a href="logout.php" class="logout">Keluar</a>
    </div>
</div>

<div class="container">
    <?php if ($msg): ?>
        <div class="alert alert-<?= $msg_type ?>"><?= $msg ?></div>
    <?php endif; ?>

    <div class="alert alert-warning">
        <b>Mode DEMO:</b> halaman ini hanya simulasi lokal untuk demonstrasi. Tidak terhubung ke Midtrans, tidak memproses uang, dan kuitansinya bukan bukti pembayaran.
    </div>

    <div class="grid-2">
        <!-- Kolom Kiri: Simulasi pembayaran -->
        <div class="card">
            <div class="card-header">
                <h2>Tagihan Registrasi & KRS Semester</h2>
                <span class="badge <?= $keu['status']==='Terbayar' ? 'badge-success' : ($keu['status']==='Pending' ? 'badge-warning' : 'badge-danger') ?>">
                    <?= $keu['status'] === 'Terbayar' ? 'LUNAS DEMO' : htmlspecialchars($keu['status']) ?>
                </span>
            </div>

            <table>
                <tr><th>NIM</th><td><?= htmlspecialchars($mhs['NIM']) ?></td></tr>
                <tr><th>Nama Lengkap</th><td><?= htmlspecialchars($mhs['Nama']) ?></td></tr>
                <tr><th>Program Studi</th><td><?= htmlspecialchars($mhs['Progdi']) ?></td></tr>
                <tr><th>Periode Akademik</th><td><?= htmlspecialchars($keu['semester']) ?></td></tr>
                <tr><th>Nominal Kewajiban</th><td><b style="font-size:16px; color:var(--primary);"><?= rupiah($keu['kewajiban']) ?></b></td></tr>
            </table>

            <?php if ($keu['status'] === 'Belum Terbayar'): ?>
                <form method="POST" style="margin-top:18px;">
                    <button type="submit" name="buat_invoice" class="btn btn-primary" style="width:100%;">Mulai Simulasi Pembayaran</button>
                </form>
            <?php elseif ($keu['status'] === 'Pending'): ?>
                <div style="margin-top:18px; padding:16px; background:#fffbeb; border:1px solid #fcd34d; border-radius:10px;">
                    <div style="font-size:12px; color:#92400e; font-weight:700;">SIMULASI DEMO MENUNGGU</div>
                    <div style="margin:6px 0; font-size:14px;">ID Demo: <b><?= htmlspecialchars($keu['order_id']) ?></b></div>
                    <form method="POST" style="margin-top:12px;">
                        <button type="submit" name="konfirmasi_demo" class="btn btn-accent" style="width:100%;">
                            Simulasikan Pembayaran Berhasil
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <div class="alert alert-success" style="margin-top:18px;">
                    Simulasi pembayaran selesai. Status ini hanya untuk pengujian; tidak ada pembayaran sungguhan yang terjadi.
                    <div style="margin-top:10px;">
                        <a href="krs_mhs.php" class="btn btn-primary btn-sm">Lanjut ke Pengisian KRS &rarr;</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Kolom Kanan: Arsip Bukti Transaksi Digital yang Disimpan di Akun -->
        <div class="card">
            <div class="card-header">
                <h2>Riwayat Pembayaran Demo</h2>
                <?php if ($keu['status'] === 'Terbayar'): ?>
                    <button onclick="window.print()" class="btn btn-outline btn-sm no-print">Cetak / Simpan PDF</button>
                <?php endif; ?>
            </div>

            <?php if ($keu['status'] === 'Terbayar'): ?>
                <div style="border: 2px dashed #16a34a; border-radius: 12px; padding: 20px; background: #f0fdf4;">
                    <div style="text-align:center; border-bottom:1px solid #bbf7d0; padding-bottom:12px; margin-bottom:12px;">
                        <strong style="font-size:15px; color:#166534;">BUKTI SIMULASI — BUKAN KUITANSI SAH</strong><br>
                        <span style="font-size:12px; color:#15803d;">STMIK SYAIKH ZAINUDDIN NW ANJANI LOMBOK TIMUR</span>
                    </div>
                    <table>
                        <tr><th>Kode Simulasi</th><td><b><?= htmlspecialchars($keu['kode_bukti']) ?></b></td></tr>
                        <tr><th>ID Demo</th><td><?= htmlspecialchars($keu['order_id']) ?></td></tr>
                        <tr><th>NIM / Mahasiswa</th><td><?= htmlspecialchars($mhs['NIM']) ?> — <?= htmlspecialchars($mhs['Nama']) ?></td></tr>
                        <tr><th>Mode</th><td>Simulasi lokal</td></tr>
                        <tr><th>Waktu Simulasi</th><td><?= htmlspecialchars($keu['waktu_bayar']) ?> WITA</td></tr>
                        <tr><th>Nominal Contoh</th><td><b><?= rupiah($keu['kewajiban']) ?></b></td></tr>
                        <tr><th>Status</th><td><span class="badge badge-warning">DEMO — BUKAN PEMBAYARAN ASLI</span></td></tr>
                    </table>
                    <p style="font-size:11.5px; color:#166534; margin-top:12px; text-align:center;">
                        Data ini hanya untuk demonstrasi dan tidak dapat digunakan sebagai bukti pembayaran.
                    </p>
                </div>
            <?php else: ?>
                <div style="text-align:center; padding:40px 20px; color:var(--muted);">
                    <p style="font-size:14px;">Belum ada simulasi pembayaran pada akun Anda.</p>
                    <p style="font-size:12.5px; margin-top:6px;">Setelah simulasi selesai, catatan DEMO akan muncul di sini dan tetap bukan bukti pembayaran asli.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<div class="footer">&copy; 2026 STMIK Syaikh Zainuddin NW Anjani Lombok Timur</div>
</body>
</html>
