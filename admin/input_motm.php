<?php
include_once('../session_check.php');
include 'koneksi.php';

$alert = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi input
    $match_id = filter_input(INPUT_POST, 'match_id', FILTER_VALIDATE_INT);
    $player_id = filter_input(INPUT_POST, 'player_id', FILTER_VALIDATE_INT);

    if (!$match_id || !$player_id) {
        $alert = "<div class='alert alert-danger alert-dismissible fade show text-center mt-3' role='alert'>
                    ❌ Data tidak valid.
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                  </div>";
    } else {
        // Cek apakah MOTM untuk pertandingan ini sudah ada
        $check_stmt = $conn->prepare("SELECT id FROM motm WHERE match_id = ?");
        $check_stmt->bind_param("i", $match_id);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows > 0) {
            $alert = "<div class='alert alert-warning alert-dismissible fade show text-center mt-3' role='alert'>
                        ⚠️ MOTM untuk pertandingan ini sudah ada.
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                      </div>";
        } else {
            // Insert MOTM
            $stmt = $conn->prepare("INSERT INTO motm (match_id, player_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $match_id, $player_id);

            if ($stmt->execute()) {
                $alert = "<div class='alert alert-success alert-dismissible fade show text-center mt-3' role='alert'>
                            ✅ MOTM berhasil disimpan.
                            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                          </div>";
            } else {
                $alert = "<div class='alert alert-danger alert-dismissible fade show text-center mt-3' role='alert'>
                            ❌ Gagal menyimpan MOTM: " . htmlspecialchars($stmt->error) . "
                            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                          </div>";
            }
            $stmt->close();
        }
        $check_stmt->close();
    }
}

// Ambil data pertandingan yang belum memiliki MOTM
$matches = $conn->query("
    SELECT m.id AS match_id, 
           t1.team_name AS team_home, 
           t2.team_name AS team_away 
    FROM matches m
    JOIN schedules s ON m.schedule_id = s.id
    JOIN teams t1 ON s.team_home_id = t1.id
    JOIN teams t2 ON s.team_away_id = t2.id
    LEFT JOIN motm mo ON m.id = mo.match_id
    WHERE mo.id IS NULL
    ORDER BY s.match_date DESC
");

$players = $conn->query("SELECT id, name FROM players ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Man of the Match</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
            min-height: 100vh;
            padding: 20px 0;
        }
        h2 {
            color: #000000ff;
            font-weight: 600;
        }
        .card {
            border: none;
            border-radius: 15px;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
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
            box-shadow: 0 5px 15px rgba(168, 184, 216, 0.4);
        }
        .form-control {
            border: 1px solid rgba(0, 0, 0, 0.1);
            background: rgba(255, 255, 255, 0.9);
        }
        .form-control:focus {
            border-color: #4facfe;
            box-shadow: 0 0 0 0.2rem rgba(79, 172, 254, 0.25);
            background: #fff;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container my-5">
        <h2 class="mb-4 text-center">⚽ Input Man of the Match (MOTM)</h2>

        <?= $alert ?>

        <form method="POST" id="motmForm" class="card p-4">
            <div class="mb-3">
                <label for="match_id" class="form-label">Pilih Pertandingan</label>
                <select name="match_id" id="match_id" class="form-select" required>
                    <option value="">-- Pilih Pertandingan --</option>
                    <?php while ($row = $matches->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($row['match_id']) ?>">
                            <?= htmlspecialchars($row['team_home']) ?> vs <?= htmlspecialchars($row['team_away']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="player_id" class="form-label">Pilih Pemain</label>
                <select name="player_id" id="player_id" class="form-select" required>
                    <option value="">-- Pilih Pemain --</option>
                    <?php while ($row = $players->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($row['id']) ?>">
                            <?= htmlspecialchars($row['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </form>

        <div class="d-flex justify-content-between mt-3">
            <a href="dashboard_admin.php" class="btn btn-secondary">Kembali</a>
            <button type="submit" form="motmForm" class="btn btn-primary">Simpan</button>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>