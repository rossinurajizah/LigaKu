<?php
include_once('../session_check.php'); // sesuaikan path sesuai struktur folder
include 'koneksi.php';

// Proses Tambah Line-Up (CREATE)
if (isset($_POST['submit'])) {
    $match_id = $_POST['match_id'];
    $player_id = $_POST['player_id'];
    $is_starting = $_POST['is_starting'];

    // Cek apakah pemain sudah terdaftar di line-up
    $cek = mysqli_query($conn, "SELECT * FROM lineups WHERE match_id='$match_id' AND player_id='$player_id'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Pemain sudah terdaftar di pertandingan ini!'); window.location='input_lineup.php?match_id=$match_id';</script>";
    } else {
        // Pastikan pemain ada di tabel players
        $player_check = mysqli_query($conn, "SELECT * FROM players WHERE id = '$player_id' AND team_id = (SELECT team_id FROM players WHERE id = '$player_id')");
        if (mysqli_num_rows($player_check) > 0) {
            // Menambahkan line-up baru
            $insert = mysqli_query($conn, "INSERT INTO lineups (match_id, player_id, is_starting, created_at)
                                           VALUES ('$match_id', '$player_id', '$is_starting', NOW())");
            if ($insert) {
                echo "<script>alert('Line-up berhasil ditambahkan'); window.location='input_lineup.php?match_id=$match_id';</script>";
            } else {
                echo "<script>alert('Gagal menambahkan line-up'); window.location='input_lineup.php?match_id=$match_id';</script>";
            }
        } else {
            echo "<script>alert('Pemain tidak ditemukan atau tidak cocok dengan tim!'); window.location='input_lineup.php?match_id=$match_id';</script>";
        }
    }
}


// Proses Edit Line-Up (UPDATE)
if (isset($_GET['edit']) && isset($_GET['match_id'])) {
    $id = $_GET['edit'];
    $match_id = $_GET['match_id'];

    // Ambil data line-up berdasarkan ID
    $edit_query = "SELECT * FROM lineups WHERE id = '$id'";
    $edit_result = mysqli_query($conn, $edit_query);
    $lineup_data = mysqli_fetch_assoc($edit_result);

    // Jika data tidak ditemukan
    if (!$lineup_data) {
        echo "<script>alert('Data line-up tidak ditemukan!'); window.location='input_lineup.php?match_id=$match_id';</script>";
        exit;
    }

    // Proses form edit jika disubmit
    if (isset($_POST['update'])) {
        $player_id = $_POST['player_id'];
        $is_starting = $_POST['is_starting'];

        // Validasi input player_id
        if (!is_numeric($player_id)) {
            echo "<script>alert('Player ID tidak valid!'); window.location='input_lineup.php?match_id=$match_id';</script>";
            exit;
        }

        $update_query = "UPDATE lineups SET player_id = '$player_id', is_starting = '$is_starting', updated_at = NOW() WHERE id = '$id'";
        $update_result = mysqli_query($conn, $update_query);

        if ($update_result) {
            echo "<script>alert('Line-up berhasil diperbarui'); window.location='input_lineup.php?match_id=$match_id';</script>";
        } else {
            echo "<script>alert('Gagal memperbarui line-up: " . mysqli_error($conn) . "'); window.location='input_lineup.php?match_id=$match_id';</script>";
        }
    }
}

// Proses Hapus Line-Up (DELETE)
if (isset($_GET['delete']) && isset($_GET['match_id'])) {
    $id = $_GET['delete'];
    $match_id = $_GET['match_id'];

    // Validasi input ID
    if (!is_numeric($id)) {
        echo "<script>alert('ID tidak valid'); window.location='input_lineup.php?match_id=$match_id';</script>";
        exit;
    }

    // Menghapus line-up pemain
    $delete = mysqli_query($conn, "DELETE FROM lineups WHERE id='$id'");
    if ($delete) {
        echo "<script>alert('Line-up berhasil dihapus'); window.location='input_lineup.php?match_id=$match_id';</script>";
    } else {
        echo "<script>alert('Gagal menghapus line-up: " . mysqli_error($conn) . "'); window.location='input_lineup.php?match_id=$match_id';</script>";
    }
}
?>
