<?php
require_once "koneksi.php";
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$msg = "";
$msg_type = "success";

// Update the campus IPK threshold used to determine the 22/24 SKS limit.
if (isset($_POST['update_waktu'])) {
    $ta = esc($_POST['tahun_akademik']);
    $buka = esc($_POST['tanggal_buka']);
    $tutup = esc($_POST['tanggal_tutup']);
    $stat = esc($_POST['status_aktif']);
    $ambang_raw = trim((string)($_POST['ambang_ipk'] ?? ''));
    $ambang_value = str_replace(',', '.', $ambang_raw);

    if ($ambang_raw !== '' && (!is_numeric($ambang_value) || (float)$ambang_value < 0 || (float)$ambang_value > 4)) {
        $msg = "Ambang IPK harus berupa angka antara 0,00 dan 4,00.";
        $msg_type = "danger";
    } else {
        $ambang_sql = $ambang_raw === '' ? 'NULL' : number_format((float)$ambang_value, 2, '.', '');
        $updated = mysqli_query($conn, "UPDATE data_waktu
            SET tahun_akademik='$ta', tanggal_buka='$buka', tanggal_tutup='$tutup',
                status_aktif='$stat', ambang_ipk=$ambang_sql
            WHERE id_waktu=1");
        if (!$updated) {
            error_log("Failed to update KRS period/IPK threshold: " . mysqli_error($conn));
            $msg = "Gagal menyimpan periode KRS dan ambang IPK.";
            $msg_type = "danger";
        } else {
            $msg = "Periode KRS dan ambang IPK berhasil diperbarui.";
        }
    }
}

// Record official cumulative IPK values from the UAS recap.
if (isset($_POST['update_ipk'])) {
    $nim = esc($_POST['nim'] ?? '');
    $ipk_raw = trim((string)($_POST['ipk_kumulatif'] ?? ''));
    $ipk_value = str_replace(',', '.', $ipk_raw);

    if ($ipk_raw !== '' && (!is_numeric($ipk_value) || (float)$ipk_value < 0 || (float)$ipk_value > 4)) {
        $msg = "IPK kumulatif harus berupa angka antara 0,00 dan 4,00.";
        $msg_type = "danger";
    } else {
        $ipk_sql = $ipk_raw === '' ? 'NULL' : number_format((float)$ipk_value, 2, '.', '');
        $updated = mysqli_query($conn, "UPDATE mhs SET ipk_kumulatif=$ipk_sql WHERE NIM='$nim'");
        if (!$updated) {
            error_log("Failed to update cumulative IPK for $nim: " . mysqli_error($conn));
            $msg = "Gagal menyimpan IPK mahasiswa.";
            $msg_type = "danger";
        } else {
            $msg = $ipk_raw === ''
                ? "IPK mahasiswa $nim dikosongkan."
                : "IPK kumulatif mahasiswa $nim berhasil diperbarui.";
        }
    }
}

if (isset($_POST['update_semester'])) {
    $nim = esc($_POST['nim'] ?? '');
    $semester = filter_var($_POST['semester_aktif'] ?? null, FILTER_VALIDATE_INT);

    if ($semester === false || $semester < 1 || $semester > 14) {
        $msg = "Semester aktif harus berupa angka antara 1 dan 14.";
        $msg_type = "danger";
    } else {
        $updated = mysqli_query($conn, "UPDATE mhs SET semester_aktif=$semester WHERE NIM='$nim'");
        if (!$updated) {
            error_log("Failed to update active semester for $nim: " . mysqli_error($conn));
            $msg = "Gagal menyimpan semester aktif mahasiswa.";
            $msg_type = "danger";
        } else {
            $msg = "Semester aktif mahasiswa $nim berhasil diperbarui.";
        }
    }
}

// 1. Tambah Mahasiswa Baru (NIM & Nama Lengkap)
if (isset($_POST['tambah_mhs'])) {
    $nim   = esc($_POST['nim']);
    $nama  = esc($_POST['nama']);
    $prodi = esc($_POST['prodi']);
    $smt   = (int)$_POST['semester'];
    $pa    = esc($_POST['nip_pa']);

    $cek = mysqli_query($conn, "SELECT id FROM mhs WHERE NIM='$nim'");
    if (mysqli_num_rows($cek) > 0) {
        $msg = "NIM <b>$nim</b> sudah terdaftar dalam sistem.";
        $msg_type = "danger";
    } else {
        mysqli_query($conn, "INSERT INTO mhs (NIM, Nama, Progdi, semester_aktif, nip_pa, is_activated) VALUES ('$nim', '$nama', '$prodi', $smt, '$pa', 0)");
        $id_mhs = mysqli_insert_id($conn);
        mysqli_query($conn, "INSERT INTO data_keuangan (id, NIM, kewajiban, status) VALUES ($id_mhs, '$nim', 2500000, 'Belum Terbayar')");
        $msg = "Mahasiswa <b>$nama ($nim)</b> berhasil ditambahkan. Mahasiswa dapat langsung mengaktifkan akunnya dengan NIM & Nama Lengkap.";
    }
}

// 2. Tambah Mata Kuliah Baru
if (isset($_POST['tambah_mk'])) {
    $kode  = esc($_POST['kodemk']);
    $nama  = esc($_POST['namamk']);
    $sks   = (int)$_POST['sks'];
    $smt   = (int)$_POST['semester'];
    $prodi = esc($_POST['prodi']);
    mysqli_query($conn, "INSERT INTO data_matkul (kodemk, namamk, sks, semester, prodi) VALUES ('$kode', '$nama', $sks, $smt, '$prodi')");
    $msg = "Mata kuliah <b>$kode - $nama</b> berhasil disimpan.";
}

// 3. Tambah Jadwal Perkuliahan
if (isset($_POST['tambah_jadwal'])) {
    $kodemk = esc($_POST['kodemk']);
    $kel    = esc($_POST['kelompok']);
    $hari   = esc($_POST['hari']);
    $jamm   = esc($_POST['jammulai']);
    $jams   = esc($_POST['jamselesai']);
    $ruang  = esc($_POST['koderuang']);
    $dosen  = esc($_POST['nip_dosen']);
    mysqli_query($conn, "INSERT INTO data_jadwal (kodemk, kelompok, hari, jammulai, jamselesai, koderuang, nip_dosen) VALUES ('$kodemk', '$kel', '$hari', '$jamm', '$jams', '$ruang', '$dosen')");
    $msg = "Jadwal perkuliahan baru berhasil ditambahkan.";
}

// 5. Terbitkan Pengumuman Kampus
if (isset($_POST['tambah_pengumuman'])) {
    $judul = esc($_POST['judul']);
    $isi   = esc($_POST['isi']);
    $tgl   = date("Y-m-d");
    mysqli_query($conn, "INSERT INTO data_pengumuman (judul, author, isi, tanggal) VALUES ('$judul', 'BAAK STMIK SZ NW Anjani', '$isi', '$tgl')");
    $msg = "Pengumuman baru berhasil diterbitkan ke halaman mahasiswa.";
}

$mhs_list = mysqli_query($conn, "SELECT m.*, k.status AS status_bayar FROM mhs m LEFT JOIN data_keuangan k ON m.NIM=k.NIM ORDER BY m.id DESC");
$mk_list  = mysqli_query($conn, "SELECT * FROM data_matkul ORDER BY prodi ASC, semester ASC");
$dsn_list = mysqli_query($conn, "SELECT * FROM dosen");
$wkt      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM data_waktu WHERE id_waktu=1"));

include "header.php";
?>
<div class="topbar">
    <div class="brand">
        <div class="brand-badge">ADMIN BAAK</div>
        <div>
            <h1>STMIK SZ NW ANJANI LOMBOK TIMUR</h1>
            <p>Panel Manajemen Master Data & Periode Akademik</p>
        </div>
    </div>
    <div class="nav-links">
        <a href="panel_admin.php" class="active">Master Data Akademik</a>
        <a href="logout.php" class="logout">Keluar</a>
    </div>
</div>

<div class="container">
    <?php if ($msg): ?>
        <div class="alert alert-<?= $msg_type ?>"><?= $msg ?></div>
    <?php endif; ?>

    <div class="grid-2">
        <!-- Form Tambah Mahasiswa -->
        <div class="card">
            <div class="card-header">
                <h2>1. Tambah Data Induk Mahasiswa (NIM & Nama)</h2>
            </div>
            <form method="POST">
                <div class="form-group">
                    <label>NIM Mahasiswa</label>
                    <input type="text" name="nim" class="form-control" placeholder="Contoh: 202401099" required>
                </div>
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" class="form-control" placeholder="Nama lengkap mahasiswa" required>
                </div>
                <div class="grid-2">
                    <div class="form-group">
                        <label>Program Studi</label>
                        <select name="prodi" class="form-control">
                            <option value="Teknik Informatika">Teknik Informatika</option>
                            <option value="Sistem Informasi">Sistem Informasi</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Semester Aktif</label>
                        <input type="number" name="semester" class="form-control" value="1" min="1" max="14" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Dosen Pembimbing Akademik (PA)</label>
                    <select name="nip_pa" class="form-control">
                        <?php mysqli_data_seek($dsn_list, 0); while ($d = mysqli_fetch_assoc($dsn_list)): ?>
                            <option value="<?= $d['nip'] ?>"><?= htmlspecialchars($d['nama']) ?> (<?= $d['jabatan'] ?>)</option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <button type="submit" name="tambah_mhs" class="btn btn-primary">Simpan Data Mahasiswa</button>
            </form>
        </div>

        <!-- Form Tambah Mata Kuliah & Jadwal -->
        <div class="card">
            <div class="card-header">
                <h2>2. Tambah Mata Kuliah & Jadwal Kelas</h2>
            </div>
            <form method="POST" style="margin-bottom:18px; padding-bottom:14px; border-bottom:1px solid var(--border);">
                <div class="grid-3">
                    <div class="form-group">
                        <label>Kode MK</label>
                        <input type="text" name="kodemk" class="form-control" placeholder="TI401" required>
                    </div>
                    <div class="form-group">
                        <label>SKS</label>
                        <input type="number" name="sks" class="form-control" value="3" min="1" max="6" required>
                    </div>
                    <div class="form-group">
                        <label>Semester</label>
                        <input type="number" name="semester" class="form-control" value="3" min="1" max="8" required>
                    </div>
                </div>
                <div class="grid-2">
                    <div class="form-group">
                        <label>Nama Mata Kuliah</label>
                        <input type="text" name="namamk" class="form-control" placeholder="Contoh: Pemrograman Mobile" required>
                    </div>
                    <div class="form-group">
                        <label>Program Studi</label>
                        <select name="prodi" class="form-control">
                            <option value="Teknik Informatika">Teknik Informatika</option>
                            <option value="Sistem Informasi">Sistem Informasi</option>
                        </select>
                    </div>
                </div>
                <button type="submit" name="tambah_mk" class="btn btn-outline btn-sm">+ Simpan Mata Kuliah</button>
            </form>

            <form method="POST">
                <div class="form-group">
                    <label>Pilih Mata Kuliah untuk Dijadwalkan</label>
                    <select name="kodemk" class="form-control">
                        <?php mysqli_data_seek($mk_list, 0); while ($mk = mysqli_fetch_assoc($mk_list)): ?>
                            <option value="<?= $mk['kodemk'] ?>">[<?= $mk['prodi'] ?> - Smt <?= $mk['semester'] ?>] <?= $mk['kodemk'] ?> — <?= htmlspecialchars($mk['namamk']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="grid-3">
                    <div class="form-group">
                        <label>Kelas</label>
                        <input type="text" name="kelompok" class="form-control" placeholder="TI-3B" required>
                    </div>
                    <div class="form-group">
                        <label>Hari</label>
                        <select name="hari" class="form-control">
                            <option>Senin</option><option>Selasa</option><option>Rabu</option>
                            <option>Kamis</option><option>Jumat</option><option>Sabtu</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Ruang</label>
                        <input type="text" name="koderuang" class="form-control" value="LAB-KOM 1" required>
                    </div>
                </div>
                <div class="grid-3">
                    <div class="form-group">
                        <label>Jam Mulai</label>
                        <input type="text" name="jammulai" class="form-control" value="08.00" required>
                    </div>
                    <div class="form-group">
                        <label>Jam Selesai</label>
                        <input type="text" name="jamselesai" class="form-control" value="10.30" required>
                    </div>
                    <div class="form-group">
                        <label>Dosen Pengampu</label>
                        <select name="nip_dosen" class="form-control">
                            <?php mysqli_data_seek($dsn_list, 0); while ($d = mysqli_fetch_assoc($dsn_list)): ?>
                                <option value="<?= $d['nip'] ?>"><?= htmlspecialchars($d['nama']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <button type="submit" name="tambah_jadwal" class="btn btn-accent">+ Tambahkan Jadwal Kuliah</button>
            </form>
        </div>
    </div>

    <div class="grid-2">
        <!-- Pengaturan Periode KRS -->
        <div class="card">
            <div class="card-header">
                <h2>3. Pengaturan Periode Pengisian KRS</h2>
            </div>
            <form method="POST">
                <div class="form-group">
                    <label>Tahun Akademik</label>
                    <input type="text" name="tahun_akademik" class="form-control" value="<?= htmlspecialchars($wkt['tahun_akademik'] ?? 'Ganjil 2026/2027') ?>" required>
                </div>
                <div class="grid-2">
                    <div class="form-group">
                        <label>Tanggal Buka</label>
                        <input type="date" name="tanggal_buka" class="form-control" value="<?= $wkt['tanggal_buka'] ?? '2026-08-01' ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Tutup</label>
                        <input type="date" name="tanggal_tutup" class="form-control" value="<?= $wkt['tanggal_tutup'] ?? '2027-02-28' ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Status Portal KRS</label>
                    <select name="status_aktif" class="form-control">
                        <option value="Buka" <?= ($wkt['status_aktif']??'')==='Buka'?'selected':'' ?>>Buka (Mahasiswa Dapat Mengisi KRS)</option>
                        <option value="Tutup" <?= ($wkt['status_aktif']??'')==='Tutup'?'selected':'' ?>>Tutup (Pengisian KRS Ditutup)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Ambang IPK Kampus</label>
                    <input type="number" name="ambang_ipk" class="form-control" min="0" max="4" step="0.01"
                           value="<?= $wkt && $wkt['ambang_ipk'] !== null ? htmlspecialchars(number_format((float)$wkt['ambang_ipk'], 2, '.', '')) : '' ?>">
                    <small>IPK harus lebih tinggi dari ambang ini untuk batas 24 SKS mulai semester 3. Kosong berarti batas tetap 22 SKS.</small>
                </div>
                <button type="submit" name="update_waktu" class="btn btn-primary">Simpan Jadwal KRS</button>
            </form>
        </div>

        <!-- Terbitkan Pengumuman Kampus -->
        <div class="card">
            <div class="card-header">
                <h2>4. Terbitkan Pengumuman Kampus</h2>
            </div>
            <form method="POST">
                <div class="form-group">
                    <label>Judul Pengumuman</label>
                    <input type="text" name="judul" class="form-control" placeholder="Contoh: Batas Akhir Validasi KRS Dosen PA" required>
                </div>
                <div class="form-group">
                    <label>Isi Pengumuman</label>
                    <textarea name="isi" rows="4" class="form-control" placeholder="Tulis informasi resmi untuk mahasiswa..." required></textarea>
                </div>
                <button type="submit" name="tambah_pengumuman" class="btn btn-primary">Terbitkan Pengumuman</button>
            </form>
        </div>
    </div>

    <!-- Daftar Mahasiswa -->
    <div class="card">
        <div class="card-header">
            <h2>Daftar Mahasiswa STMIK SZ NW Anjani Lombok Timur</h2>
        </div>
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>NIM</th>
                        <th>Nama Lengkap</th>
                        <th>Program Studi</th>
                        <th>Semester</th>
                        <th>IPK Kumulatif</th>
                        <th>Status Akun</th>
                        <th>Status Pembayaran</th>
                        <th>Dokumen KRS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($m = mysqli_fetch_assoc($mhs_list)): ?>
                    <tr>
                        <td><b><?= htmlspecialchars($m['NIM']) ?></b></td>
                        <td><?= htmlspecialchars($m['Nama']) ?></td>
                        <td><?= htmlspecialchars($m['Progdi']) ?></td>
                        <td>
                            <form method="POST" style="display:flex;gap:6px;min-width:125px;">
                                <input type="hidden" name="nim" value="<?= htmlspecialchars($m['NIM']) ?>">
                                <input type="number" name="semester_aktif" class="form-control" min="1" max="14"
                                       value="<?= (int)$m['semester_aktif'] ?>" required>
                                <button type="submit" name="update_semester" class="btn btn-outline btn-sm">Simpan</button>
                            </form>
                        </td>
                        <td>
                            <form method="POST" style="display:flex;gap:6px;min-width:145px;">
                                <input type="hidden" name="nim" value="<?= htmlspecialchars($m['NIM']) ?>">
                                <input type="number" name="ipk_kumulatif" class="form-control" min="0" max="4" step="0.01"
                                       value="<?= $m['ipk_kumulatif'] !== null ? htmlspecialchars(number_format((float)$m['ipk_kumulatif'], 2, '.', '')) : '' ?>"
                                       placeholder="Belum diisi">
                                <button type="submit" name="update_ipk" class="btn btn-outline btn-sm">Simpan</button>
                            </form>
                            <small>Dari rekap IPK resmi/UAS</small>
                        </td>
                        <td>
                            <span class="badge <?= $m['is_activated'] ? 'badge-success' : 'badge-warning' ?>">
                                <?= $m['is_activated'] ? 'Aktif' : 'Belum Aktivasi' ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?= $m['status_bayar']==='Terbayar' ? 'badge-success' : 'badge-danger' ?>">
                                <?= $m['status_bayar'] === 'Terbayar' ? 'LUNAS DEMO' : htmlspecialchars($m['status_bayar'] ?? 'Belum Terbayar') ?>
                            </span>
                        </td>
                        <td>
                            <a href="cetak_krs.php?nim=<?= urlencode($m['NIM']) ?>" target="_blank" class="btn btn-outline btn-sm">Cetak KRS</a>
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
