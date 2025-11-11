<?php
include_once('../session_check.php'); // sesuaikan path sesuai struktur folder
include 'koneksi.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="dashboard_admin.css">
</head>
<body>

<div class="sidebar">
  <h4 class="text-center text-white mb-4">⚽ Ligaku</h4>
  <a href="daftar_tim.php">📝 Pendaftaran Tim</a>
  <a href="input_pemain.php">👤 Input Pemain</a>
  <a href="konfirmasi_pembayaran.php">💳 Konfirmasi Pembayaran</a>
  <a href="penjadwalan_otomatis.php">📅 Penjadwalan</a>
  <a href="input_lineup.php">📋 Input Line Up</a>
  <a href="input_hasil.php">🏆 Input Hasil Pertandingan</a>
  <a href="input_motm.php">🏅 Input MOTM</a>
  <a href="export_laporan.php">📤 Export Laporan</a>
  <a href="logout.php" class="text-danger">🚪 Logout</a>
</div>



<div class="content gradient-blue-white">
  <div class="dashboard-hero">
    <div class="container-fluid py-5 text-center">
      <h2 class="display-5 fw-bold mb-3">Selamat Datang, <?= htmlspecialchars($_SESSION['username']) ?>!</h2>
      <p class="fs-5 mb-4">Anda login sebagai <strong>Admin</strong>. Kelola hasil pertandingan, data pemain, klasemen, dan info liga dengan mudah melalui dashboard ini.</p>
      <a href="penjadwalan_otomatis.php" class="btn btn-light btn-lg rounded-pill px-4 py-2 fw-semibold">Kelola Data Liga</a>
    </div>
  </div>
</div>



  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>
</body>
</html>
