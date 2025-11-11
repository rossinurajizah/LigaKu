<?php
include_once('../session_check.php'); // sesuaikan path sesuai struktur folder
include 'koneksi.php';
header('input_lineup.php');

// Query untuk ambil semua pemain
$sql = "SELECT id, name FROM players ORDER BY name";
$result = $conn->query($sql);

$players = [];

if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $players[] = [
      'id' => $row['id'],
      'name' => $row['name'] 
    ];
  }
}

echo json_encode($players);
?>
