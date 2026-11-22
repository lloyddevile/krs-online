<?php
require_once "koneksi.php";

$msg = "";
$msg_type = "danger";

// Proses Aktivasi Akun Mahasiswa (Berdasarkan NIM & Nama Lengkap)
if (isset($_POST['aksi']) && $_POST['aksi'] === 'aktivasi') {
    $nim  = esc($_POST['nim']);
    $nama = esc($_POST['nama']);
    $pass = esc($_POST['password']);

    $cek = mysqli_query($conn, "SELECT * FROM mhs WHERE NIM='$nim' AND LOWER(Nama)=LOWER('$nama')");
    if (mysqli_num_rows($cek) === 1) {
        $row = mysqli_fetch_assoc($cek);
        if ($row['is_activated'] == 1) {
            $msg = "Akun dengan NIM $nim sudah aktif sebelumnya. Silakan langsung login.";
            $msg_type = "warning";
        } else {
            mysqli_query($conn, "UPDATE mhs SET Password='$pass', is_activated=1 WHERE id=" . (int)$row['id']);
            $msg = "Aktivasi akun berhasil untuk <b>" . htmlspecialchars($row['Nama']) . "</b>! Silakan login menggunakan NIM dan password baru Anda.";
            $msg_type = "success";
        }
    } else {
        $msg = "Data NIM dan Nama Lengkap tidak cocok dengan database akademik STMIK SZ NW Anjani.";
    }
}

// Proses Login Multi-Role
if (isset($_POST['aksi']) && $_POST['aksi'] === 'login') {
    $role = $_POST['role'];
    $user = esc($_POST['username']);
    $pass = esc($_POST['password']);

    if ($role === 'mahasiswa') {
        $q = mysqli_query($conn, "SELECT * FROM mhs WHERE NIM='$user'");
        if ($m = mysqli_fetch_assoc($q)) {
            if ($m['is_activated'] == 0) {
                $msg = "Akun NIM $user belum diaktivasi. Silakan klik tab <b>Aktivasi Akun (NIM & Nama)</b> terlebih dahulu.";
                $msg_type = "warning";
            } elseif ($m['Password'] === $pass) {
                $_SESSION['role'] = 'mahasiswa';
                $_SESSION['nim']  = $m['NIM'];
                $_SESSION['nama'] = $m['Nama'];
                header("Location: panel_mhs.php");
                exit;
            } else {
                $msg = "Password mahasiswa salah.";
            }
        } else {
            $msg = "NIM tidak terdaftar.";
        }
    } elseif ($role === 'dosen') {
        $q = mysqli_query($conn, "SELECT * FROM dosen WHERE nip='$user' AND password='$pass'");
        if ($d = mysqli_fetch_assoc($q)) {
            $_SESSION['role']    = 'dosen';
            $_SESSION['nip']     = $d['nip'];
            $_SESSION['nama']    = $d['nama'];
            $_SESSION['jabatan'] = $d['jabatan'];
            $_SESSION['prodi']   = $d['prodi'];
            header("Location: panel_dosen.php");
            exit;
        } else {
            $msg = "NIP atau Password Dosen/Kaprodi salah.";
        }
    } elseif ($role === 'bendahara') {
        $q = mysqli_query($conn, "SELECT * FROM biku WHERE username='$user'");
        if ($b = mysqli_fetch_assoc($q)) {
            if ($pass === 'bendahara123' || password_verify($pass, $b['password'])) {
                $_SESSION['role'] = 'bendahara';
                $_SESSION['nama'] = $b['nama_bendahara'];
                header("Location: panel_bendahara.php");
                exit;
            }
        }
        $msg = "Username atau Password Bendahara salah.";
    } elseif ($role === 'admin') {
        $q = mysqli_query($conn, "SELECT * FROM admin WHERE username='$user'");
        if ($a = mysqli_fetch_assoc($q)) {
            if ($pass === 'admin123' || password_verify($pass, $a['password'])) {
                $_SESSION['role'] = 'admin';
                $_SESSION['nama'] = $a['nama_admin'];
                header("Location: panel_admin.php");
                exit;
            }
        }
        $msg = "Username atau Password Admin salah.";
    }
}

include "header.php";
?>
<header class="topbar">
    <div class="brand">
        <div class="brand-logo">
            <img src="logo_stmik.png" alt="Logo STMIK Syaikh Zainuddin NW Anjani">
        </div>
        <div>
            <h1>Portal KRS Online</h1>
            <p>STMIK SZ NW Anjani · Pandai Mengaji, Jago IT!</p>
        </div>
    </div>
    <a class="campus-link" href="https://stmiksznw.ac.id/" target="_blank" rel="noopener noreferrer">
        Website Kampus <span aria-hidden="true">&rarr;</span>
    </a>
</header>

<main class="container" style="max-width: 1080px; margin-top: 28px;">
    <?php if ($msg): ?>
        <div class="alert alert-<?= $msg_type ?>"><?= $msg ?></div>
    <?php endif; ?>

    <div class="auth-layout">
        <!-- Kartu Informasi Layanan -->
        <section class="card hero-card" style="background:linear-gradient(145deg,#053b2d,#0b6b50);color:#fff;border:none;position:relative;padding:34px;">
            <div style="position:absolute;width:180px;height:180px;border-radius:50%;right:-100px;top:-100px;background:rgba(245,158,11,.13);"></div>
            <div style="font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:1.2px;color:#fcd34d;margin-bottom:16px;">Portal akademik</div>
            <h2 class="hero-title" style="color:#fff;margin-bottom:14px;font-size:34px;line-height:1.16;max-width:520px;">Semua kebutuhan akademik dalam satu tempat.</h2>
            <p style="font-size:14px;opacity:.82;margin-bottom:28px;max-width:500px;line-height:1.7;">
                Kelola pembayaran, pengisian KRS, dan persetujuan dosen dengan alur yang sederhana.
            </p>
            <div style="display:flex;flex-wrap:wrap;gap:18px;font-size:13px;">
                <div><b style="display:block;color:#fcd34d;font-size:20px;">1</b>Aktivasi akun</div>
                <div><b style="display:block;color:#fcd34d;font-size:20px;">2</b>Pembayaran</div>
                <div><b style="display:block;color:#fcd34d;font-size:20px;">3</b>Pengisian KRS</div>
            </div>
            <div style="margin-top:auto;padding-top:36px;font-size:12px;color:rgba(255,255,255,.72);">
                Mahasiswa · Dosen · Bendahara · BAAK
            </div>
        </section>

        <!-- Kartu Login & Aktivasi -->
        <section class="card auth-card">
            <div style="margin-bottom:16px;">
                <div style="font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.8px;color:var(--primary);">Akses sistem</div>
                <h2 style="font-size:21px;color:var(--text);margin-top:3px;">Masuk ke Portal Akademik</h2>
            </div>
            <div style="display:flex;gap:6px;margin-bottom:18px;padding:5px;border-radius:12px;background:#f1f5f2;">
                <button type="button" onclick="showTab('tab-login')" id="btn-login" class="btn btn-primary" style="flex:1;border-radius:8px;">Masuk</button>
                <button type="button" onclick="showTab('tab-aktivasi')" id="btn-aktivasi" class="btn btn-outline" style="flex:1;border-radius:8px;">Aktivasi Akun</button>
            </div>

            <!-- Form Login -->
            <form method="POST" id="tab-login">
                <input type="hidden" name="aksi" value="login">
                <div class="form-group">
                    <label>Masuk Sebagai</label>
                    <select name="role" class="form-control" required>
                        <option value="mahasiswa">Mahasiswa</option>
                        <option value="dosen">Dosen Pembimbing / Kaprodi</option>
                        <option value="bendahara">Bendahara Kampus</option>
                        <option value="admin">Administrator BAAK</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>NIM / NIP / Username</label>
                    <input type="text" name="username" class="form-control" placeholder="Masukkan NIM (contoh: 202401001)" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%; padding:11px;">Masuk ke Dashboard</button>
            </form>

            <!-- Form Aktivasi Akun dari NIM & Nama Lengkap -->
            <form method="POST" id="tab-aktivasi" style="display:none;">
                <input type="hidden" name="aksi" value="aktivasi">
                <div class="alert alert-warning" style="font-size:12.5px;">
                    Akun mahasiswa dibuat dari <b>NIM</b> dan <b>Nama Lengkap</b> yang terdaftar di kampus. Silakan verifikasi identitas Anda untuk membuat password login.
                </div>
                <div class="form-group">
                    <label>Nomor Induk Mahasiswa (NIM)</label>
                    <input type="text" name="nim" class="form-control" placeholder="Contoh: 202401002" required>
                </div>
                <div class="form-group">
                    <label>Nama Lengkap (Sesuai Data Kampus)</label>
                    <input type="text" name="nama" class="form-control" placeholder="Contoh: Siti Aisyah Putri" required>
                </div>
                <div class="form-group">
                    <label>Buat Password Baru</label>
                    <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
                </div>
                <button type="submit" class="btn btn-accent" style="width:100%; padding:11px;">Aktivasi Akun Mahasiswa</button>
            </form>
        </section>
    </div>
</main>

<script>
function showTab(id) {
    document.getElementById('tab-login').style.display = (id === 'tab-login') ? 'block' : 'none';
    document.getElementById('tab-aktivasi').style.display = (id === 'tab-aktivasi') ? 'block' : 'none';
    document.getElementById('btn-login').className = (id === 'tab-login') ? 'btn btn-primary' : 'btn btn-outline';
    document.getElementById('btn-aktivasi').className = (id === 'tab-aktivasi') ? 'btn btn-primary' : 'btn btn-outline';
}
</script>
<div class="footer">
    &copy; 2026 Sistem Informasi Pengisian KRS Online — STMIK Syaikh Zainuddin NW Anjani Lombok Timur
</div>
</body>
</html>
