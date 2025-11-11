<?php
$host = "localhost";      // Ganti jika bukan localhost
$user = "root";           // Username database kamu
$pass = "";               // Password database (kosong jika default)
$db   = "ligaku"; // Nama database sesuai file .sql yang kamu import

$conn = mysqli_connect($host, $user, $pass, $db);

// Cek koneksi
if (!$conn) {
  die("Koneksi gagal: " . mysqli_connect_error());
}
?>
