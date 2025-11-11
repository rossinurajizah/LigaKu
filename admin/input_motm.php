<?php
include_once('../session_check.php');
include 'koneksi.php';

$alert = ''; // variabel untuk menyimpan pesan alert

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $match_id = $_POST['match_id'];
    $player_id = $_POST['player_id'];

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
}

// Ambil data pertandingan dan pemain
$matches = $conn->query("
    SELECT m.id AS match_id, 
           t1.team_name AS team_home, 
           t2.team_name AS team_away 
    FROM matches m
    JOIN schedules s ON m.schedule_id = s.id
    JOIN teams t1 ON s.team_home_id = t1.id
    JOIN teams t2 ON s.team_away_id = t2.id
    ORDER BY s.match_date DESC
");

$players = $conn->query("SELECT id, name FROM players ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Input Man of the Match</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-light">
    <div class="container my-5">
        <h2 class="mb-4 text-center">⚽ Input Man of the Match (MOTM)</h2>

        <!-- Tampilkan alert jika ada -->
        <?= $alert ?>

        <form method="POST" class="card p-4 shadow border border-primary">
            <div class="mb-3">
                <label for="match_id" class="form-label">Pilih Pertandingan</label>
                <select name="match_id" class="form-select" required>
                    <option value="">-- Pilih Pertandingan --</option>
                    <?php while ($row = $matches->fetch_assoc()): ?>
                        <option value="<?= $row['match_id'] ?>">
                            <?= $row['team_home'] ?> vs <?= $row['team_away'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="player_id" class="form-label">Pilih Pemain</label>
                <select name="player_id" class="form-select" required>
                    <option value="">-- Pilih Pemain --</option>
                    <?php while ($row = $players->fetch_assoc()): ?>
                        <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="d-flex justify-content-between">
                <a href="dashboard_admin.php" class="btn btn-secondary w-48">Kembali</a>
                <button type="submit" class="btn btn-primary w-48">Simpan</button>
            </div>
        </form>
    </div>
</body>
</html>
