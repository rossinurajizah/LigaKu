<?php
#include_once('../session_check.php'); // sesuaikan path sesuai struktur folder
include 'koneksi.php';

// Buat jadwal otomatis
if (isset($_GET['buat_jadwal'])) {
    $teams = $conn->query("SELECT * FROM teams");
    $team_list = [];
    while ($team = $teams->fetch_assoc()) {
        $team_list[] = $team;
    }

    $first_match_date = new DateTime('+1 week');
    $interval = new DateInterval('P3D');

    for ($i = 0; $i < count($team_list); $i++) {
        for ($j = $i + 1; $j < count($team_list); $j++) {
            $home = $team_list[$i]['id'];
            $away = $team_list[$j]['id'];

            // Cek apakah pertandingan antara 2 tim sudah pernah dijadwalkan (dua arah)
            $cek = $conn->prepare("
                SELECT COUNT(*) FROM schedules 
                WHERE (team_home_id = ? AND team_away_id = ?) 
                   OR (team_home_id = ? AND team_away_id = ?)
            ");
            $cek->bind_param("iiii", $home, $away, $away, $home);
            $cek->execute();
            $cek->bind_result($count);
            $cek->fetch();
            $cek->close();

            if ($count == 0) {
                $date = $first_match_date->format('Y-m-d');
                $first_match_date->add($interval);
                $time = "15:00";
                $location = "Stadion " . $team_list[$i]['team_name'];

                // cek bentrok jadwal di hari yang sama
                $cek_bentrok = $conn->prepare("
                    SELECT COUNT(*) FROM schedules 
                    WHERE match_date = ? AND 
                    (team_home_id = ? OR team_away_id = ? OR team_home_id = ? OR team_away_id = ?)
                ");
                $cek_bentrok->bind_param("siiii", $date, $home, $home, $away, $away);
                $cek_bentrok->execute();
                $cek_bentrok->bind_result($bentrok);
                $cek_bentrok->fetch();
                $cek_bentrok->close();

                if ($bentrok > 0) {
                    continue; // skip jadwal jika tim sudah bertanding hari itu
                }

                $stmt = $conn->prepare("INSERT INTO schedules (match_date, time, team_home_id, team_away_id, location, status) VALUES (?, ?, ?, ?, ?, 'pending')");
                $stmt->bind_param("sssii", $date, $time, $home, $away, $location);
                $stmt->execute();
                $stmt->close();
            }
        }
    }
    header("Location: penjadwalan_otomatis.php?action=buat_jadwal");
    exit();
}

// Tangani aksi dari tombol
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id'])) {
        $id = $_POST['id'];

        if (isset($_POST['update']) && isset($_POST['match_date'], $_POST['time'], $_POST['location'])) {
            // Update tanggal, waktu, lokasi
            $date = $_POST['match_date'];
            $time = $_POST['time'];
            $location = $_POST['location'];

            $stmt = $conn->prepare("UPDATE schedules SET match_date = ?, time = ?, location = ? WHERE id = ?");
            $stmt->bind_param("sssi", $date, $time, $location, $id);
            $stmt->execute();
            $stmt->close();

            header("Location: penjadwalan_otomatis.php?action=update");
            exit();

        } elseif (isset($_POST['set_approved'])) {
            $stmt = $conn->prepare("UPDATE schedules SET status = 'approved' WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();

            header("Location: penjadwalan_otomatis.php?action=set_approved");
            exit();

        } elseif (isset($_POST['set_pending'])) {
            $stmt = $conn->prepare("UPDATE schedules SET status = 'pending' WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();

            header("Location: penjadwalan_otomatis.php?action=set_pending");
            exit();

        } elseif (isset($_POST['selesai'])) {
            $stmt = $conn->prepare("UPDATE schedules SET status = 'finished' WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();

            header("Location: penjadwalan_otomatis.php?action=selesai");
            exit();
        }
    }
}

// Filter & Search
$filter_date = $_GET['filter_date'] ?? '';
$search = $_GET['search'] ?? '';

$where = "WHERE 1=1";
if ($filter_date) {
    $where .= " AND s.match_date = '" . $conn->real_escape_string($filter_date) . "'";
}
if ($search) {
    $s = $conn->real_escape_string($search);
    $where .= " AND (th.team_name LIKE '%$s%' OR ta.team_name LIKE '%$s%')";
}

$result = $conn->query("
    SELECT s.id, th.team_name AS team_home, ta.team_name AS team_away, s.match_date, s.time, s.location, s.status 
    FROM schedules s
    JOIN teams th ON s.team_home_id = th.id
    JOIN teams ta ON s.team_away_id = ta.id
    $where
    ORDER BY s.match_date ASC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Jadwal Pertandingan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    
<div class="container mt-5">
    <h2 class="text-center mb-4">Kelola Jadwal Pertandingan</h2>

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <a href="dashboard_admin.php" class="btn btn-secondary">Kembali</a>
        <a href="?buat_jadwal=1" class="btn btn-primary">Buat Jadwal Otomatis</a>
        <form class="d-flex gap-2" method="GET">
            <input type="date" name="filter_date" class="form-control" value="<?= htmlspecialchars($filter_date) ?>">
            <input type="text" name="search" class="form-control" placeholder="Cari tim..." value="<?= htmlspecialchars($search) ?>">
            <button class="btn btn-dark">Filter</button>
            <a href="penjadwalan_otomatis.php" class="btn btn-secondary">Reset</a>
        </form>
    </div>

    <div class="card shadow p-4">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>No</th>
                    <th>Tuan Rumah</th>
                    <th>Tamu</th>
                    <th>Tanggal</th>
                    <th>Waktu</th>
                    <th>Lokasi</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php $i = 1; while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <form method="POST" class="d-flex gap-2 align-items-center">
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($row['team_home']) ?></td>
                        <td><?= htmlspecialchars($row['team_away']) ?></td>
                        <td><input type="date" name="match_date" class="form-control" value="<?= $row['match_date'] ?>" required></td>
                        <td><input type="time" name="time" class="form-control" value="<?= $row['time'] ?>" required></td>
                        <td><input type="text" name="location" class="form-control" value="<?= htmlspecialchars($row['location']) ?>" required></td>
                        <td>
                            <?php
                                $status = $row['status'];
                                $badgeClass = 'warning';
                                if ($status === 'approved') {
                                    $badgeClass = 'success';
                                } elseif ($status === 'finished') {
                                    $badgeClass = 'info';
                                }
                            ?>
                            <span class="badge bg-<?= $badgeClass ?>">
                                <?= ucfirst($status) ?>
                            </span>
                        </td>
                        <td>
                        <div class="d-grid gap-1" style="width: max-content;">
                            <div class="d-flex gap-1">
                            <button name="set_approved" class="btn btn-success btn-sm" type="submit">Setujui</button>
                            <button name="set_pending" class="btn btn-secondary btn-sm" type="submit">Pending</button>
                            </div>
                            <div class="d-flex gap-1">
                            <button name="update" class="btn btn-primary btn-sm" type="submit">Update</button>
                            <button name="selesai" class="btn btn-info btn-sm" type="submit">Selesai</button>
                            </div>
                        </div>
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                        </td>

                    </form>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
<?php if (isset($_GET['action'])): ?>
    <?php
    $msg = '';
    switch ($_GET['action']) {
        case 'buat_jadwal':
            $msg = 'Jadwal pertandingan berhasil dibuat!';
            break;
        case 'update':
            $msg = 'Jadwal berhasil diperbarui!';
            break;
        case 'set_approved':
            $msg = 'Jadwal berhasil disetujui!';
            break;
        case 'set_pending':
            $msg = 'Jadwal berhasil diubah menjadi pending!';
            break;
        case 'selesai':
            $msg = 'Jadwal berhasil ditandai selesai!';
            break;
    }
    ?>
    alert("<?= addslashes($msg) ?>");
<?php endif; ?>
</script>

</body>
</html>
