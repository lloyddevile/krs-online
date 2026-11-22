<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('Asia/Makassar'); // WITA - Lombok Timur

// Replace these values with the MySQL details from your hosting control panel.
$host = "localhost";
$user = "root";
$pass = "";
$db   = "krs_stmik_sznw";

$conn = @mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("<div style='font-family:sans-serif;padding:30px;max-width:600px;margin:40px auto;background:#fef2f2;border:1px solid #ef4444;border-radius:12px;color:#991b1b;'>
        <h2>Koneksi Database Gagal</h2>
        <p>Pastikan MySQL aktif di XAMPP/Laragon dan Anda sudah mengimpor file <b>database_krs_stmik_sznw.sql</b> ke database <code>krs_stmik_sznw</code>.</p>
        <p><small>Error: " . htmlspecialchars(mysqli_connect_error()) . "</small></p>
    </div>");
}
mysqli_set_charset($conn, "utf8mb4");

function esc($str) {
    global $conn;
    return mysqli_real_escape_string($conn, trim((string)$str));
}

function rupiah($angka) {
    return "Rp " . number_format((float)$angka, 0, ',', '.');
}

?>
