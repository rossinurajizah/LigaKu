<?php
include_once('../session_check.php');
include 'koneksi.php';

if (isset($_POST['submit'])) {
    $team_name = trim($_POST['team_name']);

    // Cek apakah nama tim sudah ada di database
    $checkSql = "SELECT team_name FROM teams WHERE LOWER(team_name) = LOWER('$team_name')";
    $checkResult = $conn->query($checkSql);

    if ($checkResult->num_rows > 0) {
        echo "
        <script>
            alert('Nama tim \"$team_name\" sudah terdaftar! Silakan pilih nama tim lain.');
            window.history.back();
        </script>
        ";
        exit();
    }

    // Simpan nama tim ke SESSION saja, belum ke database
    $_SESSION['temp_team_name'] = $team_name;
    
    echo "
    <script>
        alert('Nama tim tersimpan! Silakan lanjut input pemain minimal 15 orang.');
        window.location.href = 'input_pemain.php';
    </script>
    ";
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Tim</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
        }
        .card-header {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            border: none;
            padding: 20px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #00f2fe 0%, #4facfe 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(79, 172, 254, 0.4);
        }
        .btn-secondary {
            background: linear-gradient(135deg, #a8b8d8 0%, #d4dff0 100%);
            border: none;
            color: #333;
        }
        .btn-secondary:hover {
            background: linear-gradient(135deg, #d4dff0 0%, #a8b8d8 100%);
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <div class="card shadow-lg mx-auto" style="max-width: 500px;">
        <div class="card-header text-center text-white">
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
                    <button type="submit" name="submit" class="btn btn-primary">Lanjut Input Pemain</button>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>