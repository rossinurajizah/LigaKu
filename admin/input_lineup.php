<?php
include_once('../session_check.php'); // sesuaikan path sesuai struktur folder
include 'koneksi.php';

$successMessage = '';
$errorMessage = '';

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
            $insert = "INSERT INTO lineups (match_id, player_id, is_starting, created_at)
                       VALUES ('$match_id', '$player_id', '$is_starting', NOW())";
            if (!mysqli_query($conn, $insert)) {
                $errorMessage = "Error: " . mysqli_error($conn);
                $is_saved = false;
                break;
            }
        }

        if ($is_saved) {
            $successMessage = "Lineup berhasil disimpan!";
        } else {
            $errorMessage = "Gagal menyimpan lineup. Silakan coba lagi.";
        }
    } else {
        $errorMessage = "Gagal menghapus lineup lama. Silakan coba lagi.";
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
if (isset($_POST['match_id'])) {
    $match_id = $_POST['match_id'];

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
        .radio-inline {
            margin-right: 15px;
        }
        .btn-block {
            margin-top: 20px;
        }
    </style>
</head>
<body class="container mt-4">
    <h3 class="text-center mb-4">Input Line-Up Pertandingan</h3>

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
                        <thead>
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
                                            <label class="btn btn-outline-success">
                                                <input type="radio" name="lineup[<?= $p['id'] ?>]" value="inti"> Inti
                                            </label>
                                            <label class="btn btn-outline-secondary">
                                                <input type="radio" name="lineup[<?= $p['id'] ?>]" value="cadangan"> Cadangan
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
                        <thead>
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
                                            <label class="btn btn-outline-success">
                                                <input type="radio" name="lineup[<?= $p['id'] ?>]" value="inti"> Inti
                                            </label>
                                            <label class="btn btn-outline-secondary">
                                                <input type="radio" name="lineup[<?= $p['id'] ?>]" value="cadangan"> Cadangan
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <button type="submit" name="final_submit" class="btn btn-success btn-block">Simpan Lineup</button>
        </form>
    <?php endif; ?>

<!-- Modal Pesan -->
<div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true" style="background-color: rgba(0,0,0,0.5);">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header <?= $successMessage ? 'bg-success' : 'bg-danger' ?> text-white">
        <h5 class="modal-title" id="messageModalLabel">
          <?= $successMessage ? 'Sukses' : 'Error' ?>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <?= $successMessage ?: $errorMessage ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
  $(document).ready(function() {
    <?php if ($successMessage || $errorMessage): ?>
      $('#messageModal').modal('show');

      // Redirect ke dashboard_admin saat modal ditutup
      $('#messageModal').on('hidden.bs.modal', function () {
        window.location.href = 'dashboard_admin.php';
      });
    <?php endif; ?>
  });
</script>
</body>
</html>
