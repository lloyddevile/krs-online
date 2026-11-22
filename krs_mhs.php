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
$lunas = ($keu && $keu['status'] === 'Terbayar');
$wkt_krs = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM data_waktu WHERE id_waktu=1"));
$portal_buka = ($wkt_krs && $wkt_krs['status_aktif'] === 'Buka');
$ambang_ipk = $wkt_krs ? $wkt_krs['ambang_ipk'] : null;
$batas_sks = batas_sks($mhs['semester_aktif'], $mhs['ipk_kumulatif'], $ambang_ipk);

$msg = "";
$msg_type = "danger";

// Tambah Mata Kuliah ke KRS
if ($lunas && $portal_buka && isset($_GET['ambil'])) {
    $id_jadwal = (int)$_GET['ambil'];
    $jdw = mysqli_fetch_assoc(mysqli_query($conn, "SELECT j.*, m.namamk, m.sks FROM data_jadwal j JOIN data_matkul m ON j.kodemk=m.kodemk WHERE j.id_jadwal=$id_jadwal"));
    if ($jdw) {
        // Cek apakah kode matkul sudah diambil
        $cek_mk = mysqli_query($conn, "SELECT k.id_krs FROM data_krsmhs k JOIN data_jadwal j ON k.id_jadwal=j.id_jadwal WHERE k.NIM='$nim' AND j.kodemk='{$jdw['kodemk']}'");
        // Hitung total SKS
        $q_sks = mysqli_query($conn, "SELECT COALESCE(SUM(m.sks),0) AS tot FROM data_krsmhs k JOIN data_jadwal j ON k.id_jadwal=j.id_jadwal JOIN data_matkul m ON j.kodemk=m.kodemk WHERE k.NIM='$nim'");
        $tot_sks = (int)mysqli_fetch_assoc($q_sks)['tot'];
        // Cek bentrok hari & jam
        $cek_bentrok = mysqli_query($conn, "SELECT m.namamk FROM data_krsmhs k JOIN data_jadwal j ON k.id_jadwal=j.id_jadwal JOIN data_matkul m ON j.kodemk=m.kodemk WHERE k.NIM='$nim' AND j.hari='{$jdw['hari']}' AND j.jammulai='{$jdw['jammulai']}'");

        if (mysqli_num_rows($cek_mk) > 0) {
            $msg = "Mata kuliah <b>{$jdw['namamk']}</b> sudah ada dalam daftar KRS Anda.";
        } elseif (($tot_sks + $jdw['sks']) > $batas_sks) {
            $msg = "Batas maksimal pengambilan {$batas_sks} SKS terlampaui.";
        } elseif (mysqli_num_rows($cek_bentrok) > 0) {
            $b = mysqli_fetch_assoc($cek_bentrok);
            $msg = "Jadwal bentrok dengan mata kuliah <b>{$b['namamk']}</b> pada hari {$jdw['hari']} jam {$jdw['jammulai']}.";
        } else {
            mysqli_query($conn, "INSERT INTO data_krsmhs (id_jadwal, NIM, status_pa, status_kaprodi) VALUES ($id_jadwal, '$nim', 'Menunggu', 'Menunggu')");
            $msg = "Berhasil menambahkan <b>{$jdw['namamk']} ({$jdw['sks']} SKS)</b> ke KRS. Menunggu persetujuan online Dosen Pembimbing & Kaprodi.";
            $msg_type = "success";
        }
    }
}

// Hapus Mata Kuliah dari KRS (selama belum disetujui Kaprodi)
if ($lunas && $portal_buka && isset($_GET['hapus'])) {
    $id_krs = (int)$_GET['hapus'];
    $krow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM data_krsmhs WHERE id_krs=$id_krs AND NIM='$nim'"));
    if ($krow) {
        if ($krow['status_kaprodi'] === 'Disetujui') {
            $msg = "Mata kuliah yang sudah disetujui Kaprodi tidak dapat dihapus secara mandiri.";
        } else {
            mysqli_query($conn, "DELETE FROM data_krsmhs WHERE id_krs=$id_krs");
            $msg = "Mata kuliah berhasil dihapus dari KRS.";
            $msg_type = "warning";
        }
    }
}

$filter_smt = isset($_GET['semester']) ? (int)$_GET['semester'] : 0;
$where_smt = $filter_smt > 0 ? "AND m.semester = $filter_smt" : "";

$jadwal_list = mysqli_query($conn, "SELECT j.*, m.namamk, m.sks, m.semester, d.nama AS nama_dosen 
    FROM data_jadwal j 
    JOIN data_matkul m ON j.kodemk = m.kodemk 
    LEFT JOIN dosen d ON j.nip_dosen = d.nip
    WHERE m.prodi = '{$mhs['Progdi']}' $where_smt
    ORDER BY m.semester ASC, j.kodemk ASC");

$krs_saya = mysqli_query($conn, "SELECT k.*, j.kodemk, j.kelompok, j.hari, j.jammulai, j.jamselesai, j.koderuang, m.namamk, m.sks 
    FROM data_krsmhs k 
    JOIN data_jadwal j ON k.id_jadwal = j.id_jadwal 
    JOIN data_matkul m ON j.kodemk = m.kodemk 
    WHERE k.NIM = '$nim'");

include "header.php";
?>
<div class="topbar">
    <div class="brand">
        <div class="brand-badge">KRS ONLINE</div>
        <div>
            <h1>STMIK SZ NW ANJANI LOMBOK TIMUR</h1>
            <p><?= htmlspecialchars($mhs['Nama']) ?> (<?= htmlspecialchars($mhs['NIM']) ?>) — <?= htmlspecialchars($mhs['Progdi']) ?></p>
        </div>
    </div>
    <div class="nav-links">
        <a href="panel_mhs.php">Dashboard</a>
        <a href="pembayaran_mhs.php">Pembayaran & Bukti</a>
        <a href="krs_mhs.php" class="active">Pengisian KRS</a>
        <a href="logout.php" class="logout">Keluar</a>
    </div>
</div>

<div class="container">
    <?php if ($msg): ?>
        <div class="alert alert-<?= $msg_type ?>"><?= $msg ?></div>
    <?php endif; ?>

    <?php if (!$lunas): ?>
        <div class="card" style="text-align:center; padding:40px 20px;">
            <span class="badge badge-danger" style="font-size:14px; padding:6px 14px;">AKSES PENGISIAN KRS TERKUNCI</span>
            <h2 style="margin-top:14px; color:var(--danger);">Anda Belum Menyelesaikan Pembayaran Semester</h2>
            <p style="max-width:540px; margin:10px auto 20px; color:var(--muted); font-size:14px;">
                Pada mode demo, pengisian KRS terbuka setelah simulasi pembayaran ditandai selesai. Simulasi ini bukan transaksi sungguhan.
            </p>
            <a href="pembayaran_mhs.php" class="btn btn-primary">Buka Simulasi Pembayaran &rarr;</a>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            Batas KRS Anda saat ini <b><?= $batas_sks ?> SKS</b>. Beban dasar 22 SKS; batas 24 SKS berlaku mulai semester 3 jika IPK kumulatif Anda lebih tinggi dari ambang kampus.
        </div>
        <!-- Daftar Mata Kuliah Ditawarkan (Adaptasi Alur Filter Semester & Kelas) -->
        <div class="card">
            <div class="card-header">
                <h2>Pilih Mata Kuliah Ditawarkan (Prodi <?= htmlspecialchars($mhs['Progdi']) ?>)</h2>
                <form method="GET" style="display:flex; gap:8px; align-items:center;">
                    <select name="semester" class="form-control" style="width:auto;">
                        <option value="0">-- Semua Semester --</option>
                        <?php for ($s=1; $s<=8; $s++): ?>
                            <option value="<?= $s ?>" <?= $filter_smt===$s ? 'selected' : '' ?>>Semester <?= $s ?></option>
                        <?php endfor; ?>
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                </form>
            </div>
            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Mata Kuliah</th>
                            <th>Smt</th>
                            <th>SKS</th>
                            <th>Kelas</th>
                            <th>Jadwal & Ruang</th>
                            <th>Dosen Pengampu</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($jadwal_list)): ?>
                        <tr>
                            <td><b><?= htmlspecialchars($row['kodemk']) ?></b></td>
                            <td><?= htmlspecialchars($row['namamk']) ?></td>
                            <td><?= $row['semester'] ?></td>
                            <td><?= $row['sks'] ?></td>
                            <td><span class="badge badge-info"><?= htmlspecialchars($row['kelompok']) ?></span></td>
                            <td><?= htmlspecialchars($row['hari']) ?>, <?= $row['jammulai'] ?>-<?= $row['jamselesai'] ?> (<?= htmlspecialchars($row['koderuang']) ?>)</td>
                            <td><?= htmlspecialchars($row['nama_dosen'] ?? '-') ?></td>
                            <td>
                                <a href="krs_mhs.php?ambil=<?= $row['id_jadwal'] ?>" class="btn btn-primary btn-sm">+ Ambil MK</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- KRS Mahasiswa Saat Ini + Status Persetujuan Digital PA & Kaprodi -->
        <div class="card">
            <div class="card-header">
                <h2>Kartu Rancangan Studi (KRS) Anda & Status Persetujuan Online</h2>
                <a href="cetak_krs.php" target="_blank" class="btn btn-accent btn-sm">Cetak KRS (PDF)</a>
            </div>
            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode MK</th>
                            <th>Mata Kuliah</th>
                            <th>SKS</th>
                            <th>Kelas & Jadwal</th>
                            <th>Persetujuan Dosen PA</th>
                            <th>Pengesahan Kaprodi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1; $sum_sks = 0;
                        while ($k = mysqli_fetch_assoc($krs_saya)): 
                            $sum_sks += $k['sks'];
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><b><?= htmlspecialchars($k['kodemk']) ?></b></td>
                            <td><?= htmlspecialchars($k['namamk']) ?></td>
                            <td><?= $k['sks'] ?></td>
                            <td><?= htmlspecialchars($k['kelompok']) ?> | <?= $k['hari'] ?>, <?= $k['jammulai'] ?>-<?= $k['jamselesai'] ?></td>
                            <td>
                                <span class="badge <?= $k['status_pa']==='Disetujui' ? 'badge-success' : ($k['status_pa']==='Ditolak' ? 'badge-danger' : 'badge-warning') ?>">
                                    <?= $k['status_pa'] ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?= $k['status_kaprodi']==='Disetujui' ? 'badge-success' : ($k['status_kaprodi']==='Ditolak' ? 'badge-danger' : 'badge-warning') ?>">
                                    <?= $k['status_kaprodi'] ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($k['status_kaprodi'] !== 'Disetujui'): ?>
                                    <a href="krs_mhs.php?hapus=<?= $k['id_krs'] ?>" onclick="return confirm('Hapus mata kuliah ini dari KRS?')" class="btn btn-danger btn-sm">Hapus</a>
                                <?php else: ?>
                                    <span style="font-size:12px; color:var(--success); font-weight:700;">Sah & Terkunci</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <tr style="background:#f8fafc; font-weight:700;">
                            <td colspan="3" style="text-align:right;">Total SKS Diambil:</td>
                            <td colspan="5"><?= $sum_sks ?> / <?= $batas_sks ?> SKS</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>
<div class="footer">&copy; 2026 STMIK Syaikh Zainuddin NW Anjani Lombok Timur</div>
</body>
</html>
