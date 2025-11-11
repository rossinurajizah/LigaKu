<?php
include_once('../session_check.php'); // sesuaikan path sesuai struktur folder
include 'koneksi.php';

if (isset($_POST['submit'])) {
    $team_name = $_POST['team_name'];

    // Insert data tim
    $sql = "INSERT INTO teams (team_name) VALUES ('$team_name')";
    
    if ($conn->query($sql) === TRUE) {
        $team_id = $conn->insert_id;
        $_SESSION['team_id'] = $team_id;

        // Insert data pembayaran otomatis
        $registration_date = date('Y-m-d');
        $payment_status = 'Belum Bayar';
        $payment_proof = '';

        $sqlPayment = "INSERT INTO payments (team_id, registration_date, payment_status, payment_proof)
                       VALUES ($team_id, '$registration_date', '$payment_status', '$payment_proof')";

        if ($conn->query($sqlPayment) === TRUE) {
            echo "
            <script>
                if (confirm('Tim berhasil dibuat! Lanjut input pemain?')) {
                    window.location.href = 'input_pemain.php';
                } else {
                    window.location.href = 'dashboard_admin.php';
                }
            </script>
            ";
            exit();
        } else {
            echo "Gagal insert data pembayaran. Error: " . $conn->error;
        }
    } else {
        echo "Gagal menyimpan tim. Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Tim</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow-lg mx-auto" style="max-width: 500px;">
        <div class="card-header text-center bg-primary text-white">
            <h4>📋 Pendaftaran Tim</h4>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label for="team_name" class="form-label">Nama Tim</label>
                    <input type="text" name="team_name" class="form-control" id="team_name" required>
                </div>
                <div class="d-flex justify-content-between">
                    <a href="dashboard_admin.php" class="btn btn-secondary">Kembali</a>
                    <button type="submit" name="submit" class="btn btn-success">Daftar</button>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
