<?php
require_once "koneksi.php";
if (!isset($_SESSION['nim']) && !isset($_GET['nim'])) {
    header("Location: index.php");
    exit;
}
$nim = isset($_GET['nim']) ? esc($_GET['nim']) : esc($_SESSION['nim']);
$mhs = mysqli_fetch_assoc(mysqli_query($conn, "SELECT m.*, d.nama AS nama_pa FROM mhs m LEFT JOIN dosen d ON m.nip_pa=d.nip WHERE m.NIM='$nim'"));
if (!$mhs) { die("Data mahasiswa tidak ditemukan."); }

$prodi_esc = esc($mhs['Progdi']);
$kaprodi = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nama, nip FROM dosen WHERE jabatan='Kaprodi' AND prodi='$prodi_esc' LIMIT 1"));
$keu = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM data_keuangan WHERE NIM='$nim' ORDER BY id_keuangan DESC LIMIT 1"));
$wkt = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM data_waktu ORDER BY id_waktu DESC LIMIT 1"));
$krs = mysqli_query($conn, "SELECT k.*, j.kodemk, j.kelompok, j.hari, j.jammulai, j.jamselesai, j.koderuang, m.namamk, m.sks 
    FROM data_krsmhs k JOIN data_jadwal j ON k.id_jadwal=j.id_jadwal JOIN data_matkul m ON j.kodemk=m.kodemk WHERE k.NIM='$nim'");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak KRS — <?= htmlspecialchars($mhs['NIM']) ?> | STMIK SZ NW Anjani</title>
    <style>
        body { font-family: 'Times New Roman', serif; color: #000; padding: 24px; max-width: 860px; margin: auto; }
        .kop { text-align: center; border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 18px; }
        .kop h2 { margin: 0; font-size: 18px; text-transform: uppercase; }
        .kop h3 { margin: 2px 0; font-size: 16px; }
        .kop p { margin: 2px 0; font-size: 12px; }
        table.bio { width: 100%; margin-bottom: 14px; font-size: 13.5px; }
        table.bio td { padding: 3px 6px; }
        table.grid { width: 100%; border-collapse: collapse; font-size: 13px; margin-bottom: 24px; }
        table.grid th, table.grid td { border: 1px solid #000; padding: 7px 8px; }
        table.grid th { background: #f1f5f9; }
        .ttd { width: 100%; margin-top: 26px; font-size: 13px; text-align: center; }
        .ttd td { width: 33.3%; vertical-align: top; }
        .qr-stamp { border: 1px solid #065f46; color: #065f46; padding: 6px 8px; font-size: 10px; display: inline-block; margin: 8px 0; font-family: sans-serif; border-radius: 4px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom:15px; display:flex; justify-content:space-between; align-items:center; background:#f8fafc; padding:10px 14px; border:1px solid #cbd5e1; border-radius:8px; font-family:sans-serif;">
        <span style="font-size:13px;">Pratinjau Dokumen Resmi KRS — <b>STMIK SZ NW Anjani Lombok Timur</b></span>
        <button onclick="window.print()" style="padding:8px 16px; background:#065f46; color:#fff; border:none; border-radius:6px; cursor:pointer; font-weight:bold;">Cetak / Simpan PDF</button>
    </div>

    <div class="kop">
        <h2>SEKOLAH TINGGI MANAJEMEN INFORMATIKA DAN KOMPUTER</h2>
        <h3>STMIK SYAIKH ZAINUDDIN NW ANJANI LOMBOK TIMUR</h3>
        <p>Alamat: Jl. Raya Mataram - Labuhan Lombok Km. 49 Anjani, Kec. Suralaga, Kab. Lombok Timur, NTB</p>
    </div>

    <h3 style="text-align:center; text-decoration:underline; margin-bottom:4px;">KARTU RANCANGAN STUDI (KRS)</h3>
    <p style="text-align:center; font-size:13px; margin-top:0; margin-bottom:16px;">Tahun Akademik: <?= htmlspecialchars($wkt['tahun_akademik'] ?? 'Ganjil 2026/2027') ?></p>

    <table class="bio">
        <tr>
            <td width="18%"><b>NIM</b></td><td width="32%">: <?= htmlspecialchars($mhs['NIM']) ?></td>
            <td width="22%"><b>Program Studi</b></td><td width="28%">: <?= htmlspecialchars($mhs['Progdi']) ?></td>
        </tr>
        <tr>
            <td><b>Nama Lengkap</b></td><td>: <?= htmlspecialchars($mhs['Nama']) ?></td>
            <td><b>Semester Aktif</b></td><td>: Semester <?= $mhs['semester_aktif'] ?></td>
        </tr>
        <tr>
            <td><b>Dosen PA</b></td><td>: <?= htmlspecialchars($mhs['nama_pa'] ?? '-') ?></td>
            <td><b><?= ($keu['metode_bayar'] ?? '') === 'SIMULASI DEMO' ? 'Kode Simulasi' : 'No. Bukti Pembayaran' ?></b></td>
            <td>: <?= htmlspecialchars($keu['kode_bukti'] ?? 'Belum Lunas') ?></td>
        </tr>
    </table>

    <table class="grid">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode MK</th>
                <th>Nama Mata Kuliah</th>
                <th>SKS</th>
                <th>Kelas</th>
                <th>Jadwal & Ruang</th>
                <th>Status Validasi Online</th>
            </tr>
        </thead>
        <tbody>
            <?php $no=1; $tot=0; while ($r = mysqli_fetch_assoc($krs)): $tot += $r['sks']; ?>
            <tr>
                <td align="center"><?= $no++ ?></td>
                <td><?= htmlspecialchars($r['kodemk']) ?></td>
                <td><?= htmlspecialchars($r['namamk']) ?></td>
                <td align="center"><?= $r['sks'] ?></td>
                <td align="center"><?= htmlspecialchars($r['kelompok']) ?></td>
                <td><?= $r['hari'] ?>, <?= $r['jammulai'] ?>-<?= $r['jamselesai'] ?> (<?= $r['koderuang'] ?>)</td>
                <td>PA: <?= $r['status_pa'] ?> | Kaprodi: <?= $r['status_kaprodi'] ?></td>
            </tr>
            <?php endwhile; ?>
            <tr>
                <th colspan="3" align="right">Jumlah Total SKS Diambil</th>
                <th align="center"><?= $tot ?></th>
                <th colspan="3"></th>
            </tr>
        </tbody>
    </table>

    <table class="ttd">
        <tr>
            <td>
                Mengetahui,<br>Ketua Program Studi<br>
                <div class="qr-stamp">DISAHKAN SECARA DIGITAL<br>Kaprodi <?= htmlspecialchars($mhs['Progdi']) ?></div><br>
                <b><u><?= htmlspecialchars($kaprodi['nama'] ?? 'Ketua Program Studi') ?></u></b><br>
                NIDN/NIP. <?= htmlspecialchars($kaprodi['nip'] ?? '-') ?>
            </td>
            <td>
                Menyetujui,<br>Dosen Pembimbing Akademik<br>
                <div class="qr-stamp">DISETUJUI SECARA ONLINE<br>Sistem Akademik STMIK SZ NW</div><br>
                <b><u><?= htmlspecialchars($mhs['nama_pa'] ?? 'Dosen PA') ?></u></b><br>
                NIDN/NIP. <?= htmlspecialchars($mhs['nip_pa'] ?? '-') ?>
            </td>
            <td>
                Anjani, <?= date('d M Y') ?><br>Mahasiswa Ybs,<br><br><br><br>
                <b><u><?= htmlspecialchars($mhs['Nama']) ?></u></b><br>
                NIM. <?= htmlspecialchars($mhs['NIM']) ?>
            </td>
        </tr>
    </table>
</body>
</html>
