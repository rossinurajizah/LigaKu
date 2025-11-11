<?php
session_start();

// Fungsi redirect aman
function redirectTo($url) {
    header("Location: $url");
    exit();
}

// Ambil nama file yang sedang diakses
$currentFile = basename($_SERVER['SCRIPT_NAME']);
$requestUri = $_SERVER['REQUEST_URI'];

// Cek: Jika berada di folder admin, harus login sebagai admin
if (strpos($requestUri, '/Ligaku/admin/') !== false) {
    if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        redirectTo('/Ligaku/admin/login.php');
    }
}

// Cek: Jika mengakses input_pemain.php tapi belum daftar tim
if ($currentFile === 'input_pemain.php') {
    if (empty($_SESSION['team_id'])) {
        echo "<script>
            alert('Silakan daftar tim terlebih dahulu.');
            window.location.href = 'daftar_tim.php';
        </script>";
        exit();
    }
}
