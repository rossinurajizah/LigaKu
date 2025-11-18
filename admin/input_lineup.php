<?php
include_once('../session_check.php'); // sesuaikan path sesuai struktur folder
include 'koneksi.php';

// Ambil data lineup lama (jika ada) untuk ditandai pada form
$existing_lineup = [];
function get_existing_lineup($conn, $match_id) {
    $lineup_query = mysqli_query($conn, "SELECT player_id, is_starting FROM lineups WHERE match_id = '$match_id'");
    $lineup = [];
    while($row = mysqli_fetch_assoc($lineup_query)) {
        $lineup[$row['player_id']] = $row['is_starting'] == 1 ? 'inti' : 'cadangan';
    }
    return $lineup;
}

// Fungsi helper untuk menentukan status radio button
function is_checked($player_id, $status, $existing_lineup) {
    if (isset($existing_lineup[$player_id])) {
        return $existing_lineup[$player_id] === $status ? 'checked' : '';
    }
    return '';
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['final_submit'])) {
    $match_id = $_POST['match_id'];
    $lineup_data = $_POST['lineup'];

    // Hapus lineup lama
    $delete_query = mysqli_query($conn, "DELETE FROM lineups WHERE match_id = '$match_id'");

    if ($delete_query) {
        // Simpan data lineup baru
        $is_saved = true;
        foreach ($lineup_data as $player_id => $status) {
            $is_starting = ($status === 'inti') ? 1 : 0;
            
            // Menggunakan prepared statement untuk keamanan (lebih baik dari mysqli_query langsung)
            $stmt = $conn->prepare("INSERT INTO lineups (match_id, player_id, is_starting, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("iii", $match_id, $player_id, $is_starting);
            
            if (!$stmt->execute()) {
                $error = "Error: " . $stmt->error;
                $is_saved = false;
                $stmt->close();
                break;
            }
            $stmt->close();
        }

        if ($is_saved) {
            // Menggunakan alert() dan redirect setelah SUKSES
            echo "
            <script>
                alert('Lineup berhasil disimpan!');
                window.location.href = 'dashboard_admin.php';
            </script>
            ";
            exit();
        } else {
            // Menggunakan alert() untuk ERROR saat insert
            echo "
            <script>
                alert('Gagal menyimpan lineup. Silakan coba lagi. Rincian: " . addslashes($error ?? 'Tidak diketahui') . "');
                window.location.href = 'input_lineup.php'; // Kembali ke halaman ini
            </script>
            ";
            exit();
        }
    } else {
        // Menggunakan alert() untuk ERROR saat delete
        echo "
        <script>
            alert('Gagal menghapus lineup lama. Silakan coba lagi.');
            window.location.href = 'input_lineup.php'; // Kembali ke halaman ini
        </script>
        ";
        exit();
    }
}

// Ambil semua pertandingan
$matches_query = "
    SELECT s.id, t1.team_name AS home_team, t2.team_name AS away_team, s.match_date
    FROM schedules s
    JOIN teams t1 ON s.team_home_id = t1.id
    JOIN teams t2 ON s.team_away_id = t2.id
    ORDER BY s.match_date ASC
";
$matches_result = mysqli_query($conn, $matches_query);

// Ambil pemain jika ada POST match
$players_team_a = $players_team_b = [];
$team_a_name = $team_b_name = '';
$match_id = $_POST['match_id'] ?? '';

if (!empty($match_id)) {
    // Cari info tim A dan tim B berdasarkan jadwal
    $match_info_query = "
        SELECT s.team_home_id, s.team_away_id, t1.team_name AS home_team, t2.team_name AS away_team
        FROM schedules s
        JOIN teams t1 ON s.team_home_id = t1.id
        JOIN teams t2 ON s.team_away_id = t2.id
        WHERE s.id = '$match_id'
    ";
    $match_info_result = mysqli_query($conn, $match_info_query);
    $match_info = mysqli_fetch_assoc($match_info_result);

    $team_a_id = $match_info['team_home_id'];
    $team_b_id = $match_info['team_away_id'];
    $team_a_name = $match_info['home_team'];
    $team_b_name = $match_info['away_team'];

    // Ambil lineup yang sudah ada untuk menandai radio button
    $existing_lineup = get_existing_lineup($conn, $match_id);

    $players_team_a = mysqli_query($conn, "SELECT * FROM players WHERE team_id = '$team_a_id'");
    $players_team_b = mysqli_query($conn, "SELECT * FROM players WHERE team_id = '$team_b_id'");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Input Line-Up</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        html {
            scroll-behavior: smooth;
        }
        body {
            background: #f8f9fa;
        }
        .radio-inline {
            margin-right: 15px;
        }
        .btn-block {
            margin-top: 20px;
        }
        h3 {
            color: #000000ff;
            font-weight: 600;
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
        .btn-success {
            background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%);
            border: none;
        }
        .btn-success:hover {
            background: linear-gradient(135deg, #a8e063 0%, #56ab2f 100%);
            transform: translateY(-2px);
        }
        .btn-outline-success:hover {
            background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%);
            border-color: #56ab2f;
        }
        .table {
            background: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="dashboard_admin.php" class="btn btn-secondary"> Kembali</a>
        <h3 class="mb-0">⚽ Input Line-Up Pertandingan</h3>
        <div style="width: 100px;"></div> </div>

    <form method="POST" class="mb-4">
        <div class="form-group">
            <label>Pilih Pertandingan</label>
            <select name="match_id" class="form-control" required>
                <option value="">-- Pilih Pertandingan --</option>
                <?php mysqli_data_seek($matches_result, 0); while ($m = mysqli_fetch_assoc($matches_result)) { ?>
                    <option value="<?= $m['id'] ?>" <?= isset($match_id) && $match_id == $m['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($m['home_team']) ?> vs <?= htmlspecialchars($m['away_team']) ?> (<?= $m['match_date'] ?>)
                    </option>
                <?php } ?>
            </select>
        </div>

        <button class="btn btn-primary btn-block" type="submit">Tampilkan Pemain</button>
    </form>

    <?php if (!empty($players_team_a) && !empty($players_team_b)): ?>
        <form method="POST" action="#lineup">
            <input type="hidden" name="match_id" value="<?= $match_id ?>">

            <div class="row">
                <div class="col-md-6">
                    <h5 class="text-center"><?= htmlspecialchars($team_a_name) ?></h5>
                    <table class="table table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th>Nama Pemain</th>
                                <th>No Punggung</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($p = mysqli_fetch_assoc($players_team_a)) { ?>
                                <tr>
                                    <td><?= htmlspecialchars($p['name']) ?></td>
                                    <td><?= htmlspecialchars($p['back_number']) ?></td>
                                    <td>
                                        <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                            <label class="btn btn-outline-success <?= is_checked($p['id'], 'inti', $existing_lineup) ? 'active' : '' ?>">
                                                <input type="radio" name="lineup[<?= $p['id'] ?>]" value="inti" <?= is_checked($p['id'], 'inti', $existing_lineup) ? 'checked' : '' ?> required> Inti
                                            </label>
                                            <label class="btn btn-outline-secondary <?= is_checked($p['id'], 'cadangan', $existing_lineup) ? 'active' : '' ?>">
                                                <input type="radio" name="lineup[<?= $p['id'] ?>]" value="cadangan" <?= is_checked($p['id'], 'cadangan', $existing_lineup) ? 'checked' : '' ?> required> Cadangan
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <div class="col-md-6">
                    <h5 class="text-center"><?= htmlspecialchars($team_b_name) ?></h5>
                    <table class="table table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th>Nama Pemain</th>
                                <th>No Punggung</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($p = mysqli_fetch_assoc($players_team_b)) { ?>
                                <tr>
                                    <td><?= htmlspecialchars($p['name']) ?></td>
                                    <td><?= htmlspecialchars($p['back_number']) ?></td>
                                    <td>
                                        <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                            <label class="btn btn-outline-success <?= is_checked($p['id'], 'inti', $existing_lineup) ? 'active' : '' ?>">
                                                <input type="radio" name="lineup[<?= $p['id'] ?>]" value="inti" <?= is_checked($p['id'], 'inti', $existing_lineup) ? 'checked' : '' ?> required> Inti
                                            </label>
                                            <label class="btn btn-outline-secondary <?= is_checked($p['id'], 'cadangan', $existing_lineup) ? 'active' : '' ?>">
                                                <input type="radio" name="lineup[<?= $p['id'] ?>]" value="cadangan" <?= is_checked($p['id'], 'cadangan', $existing_lineup) ? 'checked' : '' ?> required> Cadangan
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <button type="submit" name="final_submit" class="btn btn-success btn-block">💾 Simpan Lineup</button>
        </form>
    <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>