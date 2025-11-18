<?php
#include_once('../session_check.php'); // sesuaikan path sesuai struktur folder
include 'koneksi.php';

// =================================================================
// FUNGSI 1: MEMBUAT JADWAL KNOCKOUT (Top 4) - TIDAK ADA PERUBAHAN
// =================================================================
function generateKnockoutSchedule($conn) {
    
    // 1. Cek Kualifikasi (Ambil 4 Tim Terbaik dari Klasemen)
    $top_teams_query = "SELECT team_id FROM standings 
                         ORDER BY points DESC, goal_diff DESC, goals_for DESC 
                         LIMIT 4";
    $result = $conn->query($top_teams_query);
    $qualifiers = [];
    while ($row = $result->fetch_assoc()) {
        $qualifiers[] = $row['team_id'];
    }

    if (count($qualifiers) < 4) {
        return "Gagal: Minimal 4 tim harus terdaftar di klasemen untuk memulai babak gugur.";
    }

    $team1 = $qualifiers[0]; $team2 = $qualifiers[1]; $team3 = $qualifiers[2]; $team4 = $qualifiers[3]; 
    
    // 2. Tentukan Tanggal Semifinal dan Final
    $last_match = $conn->query("SELECT MAX(match_date) as last_date FROM schedules WHERE round_type='Penyisihan' AND status='finished'")->fetch_assoc();
    $start_date_str = $last_match['last_date'] ? $last_match['last_date'] : (new DateTime('+1 week'))->format('Y-m-d');
    
    $semi_final_date = (new DateTime($start_date_str))->modify('+7 days')->format('Y-m-d'); 
    $final_date = (new DateTime($semi_final_date))->modify('+3 days')->format('Y-m-d');
    $time = "19:00";
    $location_final = "Stadion Utama"; 

    // 3. Masukkan SLOT FINAL sebagai placeholder
    $conn->query("INSERT INTO schedules 
                  (match_date, time, round_type, team_home_id, team_away_id, location, status) 
                  VALUES ('$final_date', '$time', 'Final', NULL, NULL, '$location_final', 'pending_qualification')");
    $final_match_id = $conn->insert_id; 

    // 4. Masukkan Jadwal Semifinal 1 (SF1: Rank 1 vs 4)
    $stmt_sf1 = $conn->prepare("INSERT INTO schedules 
                                 (match_date, time, round_type, team_home_id, team_away_id, next_match_id, location, status) 
                                 VALUES (?, ?, 'SemiFinal', ?, ?, ?, ?, 'pending')");
    $stmt_sf1->bind_param("ssiiis", $semi_final_date, $time, $team1, $team4, $final_match_id, $location_final);
    $stmt_sf1->execute();
    $stmt_sf1->close();
    
    // 5. Masukkan Jadwal Semifinal 2 (SF2: Rank 2 vs 3)
    $stmt_sf2 = $conn->prepare("INSERT INTO schedules 
                                 (match_date, time, round_type, team_home_id, team_away_id, next_match_id, location, status) 
                                 VALUES (?, ?, 'SemiFinal', ?, ?, ?, ?, 'pending')");
    $stmt_sf2->bind_param("ssiiis", $semi_final_date, $time, $team2, $team3, $final_match_id, $location_final);
    $stmt_sf2->execute();
    $stmt_sf2->close();
    
    return "Jadwal Semifinal dan Final berhasil dibuat secara otomatis!";
}


// =================================================================
// LOGIKA UTAMA PENJADWALAN PENYISIHAN (HANYA MENDUKUNG N GENAP)
// =================================================================
if (isset($_GET['buat_jadwal'])) {
    
    // 🛑 CEK GLOBAL DUPLIKAT: JADWAL PENYISIHAN
    $existing_schedule_check = $conn->query("SELECT COUNT(*) FROM schedules WHERE round_type='Penyisihan'");
    $count_existing = $existing_schedule_check->fetch_row()[0];
    
    if ($count_existing > 0) {
        header("Location: penjadwalan_otomatis.php?action=jadwal_sudah_ada");
        exit();
    }
    
    // START LOGIKA 1: Cek Pembayaran & Ambil Tim
    $teams_query = "
        SELECT t.id, t.team_name, CONCAT('Stadion ', t.team_name) AS location 
        FROM teams t
        JOIN payments p ON t.id = p.team_id
        WHERE p.payment_status = 'Sudah Bayar'
    ";
    $teams_result = $conn->query($teams_query); 
    $team_list = [];
    while ($team = $teams_result->fetch_assoc()) {
        $team_list[] = $team;
    }

    $N = count($team_list);

    if ($N < 2) {
        header("Location: penjadwalan_otomatis.php?action=tim_kurang"); 
        exit();
    }
    
    // 🔥 PERUBAHAN UTAMA 1: Tolak jika Ganjil
    if ($N % 2 != 0) { // Jika N TIDAK habis dibagi 2 (GANJIL)
        header("Location: penjadwalan_otomatis.php?action=gagal_ganjil"); // Mengarahkan ke pesan error Ganjil
        exit();
    }
    // END LOGIKA 1

    // 🔥 PERUBAHAN UTAMA 2: ALGORITMA ROTASI UNTUK N GENAP
    // N Genap membutuhkan N-1 putaran
    $rounds = $N - 1; 
    $teams_to_rotate = $team_list; 
    $N_rot = $N; 
    $matchups_per_round = $N / 2;
    
    // Pisahkan tim pertama (pivot) dan sisanya
    $fixed_team = array_shift($teams_to_rotate); 
    
    $last_scheduled_teams = []; 
    $first_match_date = new DateTime('+1 week');
    $interval = new DateInterval('P3D');
    $rest_days = 2; 

    for ($round = 1; $round <= $rounds; $round++) {
        
        for ($match = 0; $match < $matchups_per_round; $match++) {
            
            if ($match == 0) {
                // Pertandingan pertama: Tim Pivot vs Tim yang sedang dirotasi
                $home_team_data = $fixed_team;
                $away_team_data = $teams_to_rotate[$N_rot - 2]; // Tim terakhir dari rotasi
            } else {
                // Pertandingan lainnya
                $home_team_data = $teams_to_rotate[$match - 1];
                $away_team_data = $teams_to_rotate[$N_rot - 1 - $match];
            }
            
            $home = $home_team_data['id']; 
            $away = $away_team_data['id']; 
            $location = $home_team_data['location']; 
            

            
            $match_date_dt = clone $first_match_date;
            $found_date = false;

            while (!$found_date) {
                $is_conflict = false;
                for ($d = 0; $d <= $rest_days; $d++) { 
                    $check_date_dt = clone $match_date_dt;
                    if ($d > 0) { $check_date_dt->modify("-$d day"); }
                    $check_date = $check_date_dt->format('Y-m-d');

                    $stmt_gap = $conn->prepare("
                        SELECT COUNT(*) FROM schedules 
                        WHERE match_date = ? 
                        AND (team_home_id = ? OR team_away_id = ?)
                    ");
                    $stmt_gap->bind_param("sii", $check_date, $home, $away);
                    $stmt_gap->execute();
                    $stmt_gap->bind_result($count_conflict);
                    $stmt_gap->fetch();
                    $stmt_gap->close();

                    if ($count_conflict > 0) {
                        $is_conflict = true;
                        break; 
                    }
                }
                
                if ($is_conflict) {
                    $match_date_dt->add(new DateInterval('P1D'));
                } else {
                    $found_date = true; 
                }
            }
            
            $date = $match_date_dt->format('Y-m-d');
            $first_match_date = $match_date_dt; 
            $first_match_date->add($interval); 
            
            $time = "15:00";

            // INSERT schedule 
            $stmt = $conn->prepare("INSERT INTO schedules (match_date, time, team_home_id, team_away_id, location, round_type, status) 
                                     VALUES (?, ?, ?, ?, ?, 'Penyisihan', 'pending')");
            $stmt->bind_param("ssiis", $date, $time, $home, $away, $location); 
            $stmt->execute();
            $stmt->close();
            
            $last_scheduled_teams = [$home, $away];
        }
        
        // Rotasi Tim untuk Putaran Berikutnya
        $temp = array_pop($teams_to_rotate);
        array_unshift($teams_to_rotate, $temp);
    }
    
    header("Location: penjadwalan_otomatis.php?action=buat_jadwal");
    exit();
} 
// =================================================================
// TANGANI POST REQUEST (UPDATE STATUS, TERMASUK OTOMATISASI KNOCKOUT) - TIDAK ADA PERUBAHAN
// =================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id'])) {
        $id = $_POST['id'];

        if (isset($_POST['update']) && isset($_POST['match_date'], $_POST['time'], $_POST['location'])) {
            $date = $_POST['match_date']; $time = $_POST['time']; $location = $_POST['location'];
            $stmt = $conn->prepare("UPDATE schedules SET match_date = ?, time = ?, location = ? WHERE id = ?");
            $stmt->bind_param("sssi", $date, $time, $location, $id);
            $stmt->execute(); $stmt->close();
            header("Location: penjadwalan_otomatis.php?action=update");
            exit();

        } elseif (isset($_POST['set_approved'])) {
            $stmt = $conn->prepare("UPDATE schedules SET status = 'approved' WHERE id = ?");
            $stmt->bind_param("i", $id); $stmt->execute(); $stmt->close();
            header("Location: penjadwalan_otomatis.php?action=set_approved");
            exit();

        } elseif (isset($_POST['set_pending'])) {
            $stmt = $conn->prepare("UPDATE schedules SET status = 'pending' WHERE id = ?");
            $stmt->bind_param("i", $id); $stmt->execute(); $stmt->close();
            header("Location: penjadwalan_otomatis.php?action=set_pending");
            exit();

        } elseif (isset($_POST['selesai'])) {
            // 1. Set status pertandingan saat ini menjadi finished
            $stmt = $conn->prepare("UPDATE schedules SET status = 'finished' WHERE id = ?");
            $stmt->bind_param("i", $id); $stmt->execute(); 
            
            // 2. Cek apakah ini adalah pertandingan terakhir Penyisihan
            $total_rr = $conn->query("SELECT COUNT(*) FROM schedules WHERE round_type='Penyisihan'")->fetch_row()[0];
            $finished_rr = $conn->query("SELECT COUNT(*) FROM schedules WHERE round_type='Penyisihan' AND status='finished'")->fetch_row()[0];
            
            $action = 'selesai';
            
            if ($total_rr > 0 && $total_rr == $finished_rr) { 
                // 3. Cek apakah babak Knockout sudah pernah dibuat
                $knockout_exists = $conn->query("SELECT COUNT(*) FROM schedules WHERE round_type='SemiFinal' OR round_type='Final'")->fetch_row()[0];
                
                if ($knockout_exists == 0) {
                    // 4. Buat babak gugur secara otomatis
                    $auto_message = generateKnockoutSchedule($conn);
                    $action = 'auto_knockout';
                }
            }
            $stmt->close();
            
            header("Location: penjadwalan_otomatis.php?action=" . $action . (isset($auto_message) ? "&msg=" . urlencode($auto_message) : ''));
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
    SELECT s.id, th.team_name AS team_home, ta.team_name AS team_away, s.match_date, s.time, s.location, s.status, s.round_type
    FROM schedules s
    LEFT JOIN teams th ON s.team_home_id = th.id
    LEFT JOIN teams ta ON s.team_away_id = ta.id
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
        }
        .btn-success {
            background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%);
            border: none;
        }
        .btn-success:hover {
            background: linear-gradient(135deg, #a8e063 0%, #56ab2f 100%);
            transform: translateY(-2px);
        }
        .btn-info {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            border: none;
        }
        .btn-info:hover {
            background: linear-gradient(135deg, #00f2fe 0%, #4facfe 100%);
            transform: translateY(-2px);
        }
        .btn-filter {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            border: none;
            color: white;
        }
        .btn-filter:hover {
            background: linear-gradient(135deg, #00f2fe 0%, #4facfe 100%);
            transform: translateY(-2px);
            color: white;
        }
        .table {
            background: transparent;
            border: none;
        }
        .table thead th {
            background: rgba(255, 255, 255, 0.9);
            color: #333;
            border: 1px solid rgba(0, 0, 0, 0.08);
            font-weight: 600;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        .table tbody td {
            background: rgba(255, 255, 255, 0.6);
            border: 1px solid rgba(0, 0, 0, 0.06);
            vertical-align: middle;
        }
        .table tbody tr {
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }
        .table tbody tr:hover {
            background: rgba(79, 172, 254, 0.05);
            box-shadow: 0 2px 8px rgba(79, 172, 254, 0.15);
            transition: all 0.3s ease;
        }
        .badge.bg-warning {
            background: linear-gradient(135deg, #ffeaa7 0%, #fdcb6e 100%) !important;
            color: #2d3436;
        }
        .badge.bg-success {
            background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%) !important;
        }
        .badge.bg-info {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%) !important;
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
<body>
    
<div class="container mt-5">
    <h2 class="text-center mb-4">⚽ Kelola Jadwal Pertandingan</h2>

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <a href="dashboard_admin.php" class="btn btn-secondary"> Kembali</a>
        <div class="d-flex gap-2">
            <a href="?buat_jadwal=1" class="btn btn-primary">📅 Buat Jadwal</a>
        </div>
        
        <form class="d-flex gap-2" method="GET">
            <input type="date" name="filter_date" class="form-control" value="<?= htmlspecialchars($filter_date) ?>">
            <input type="text" name="search" class="form-control" placeholder="Cari tim..." value="<?= htmlspecialchars($search) ?>">
            <button class="btn btn-filter">🔍 Filter</button>
            <a href="penjadwalan_otomatis.php" class="btn btn-secondary">🔄 Reset</a>
        </form>
    </div>

    <div class="card p-4">
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tuan Rumah</th>
                    <th>Tamu</th>
                    <th>Tanggal</th>
                    <th>Waktu</th>
                    <th>Lokasi</th>
                    <th>Babak</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php $i = 1; while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <form method="POST" class="d-flex gap-2 align-items-center">
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($row['team_home'] ?? 'Pemenang SF1') ?></td> 
                        <td><?= htmlspecialchars($row['team_away'] ?? 'Pemenang SF2') ?></td>
                        <td><input type="date" name="match_date" class="form-control" value="<?= $row['match_date'] ?>" required></td>
                        <td><input type="time" name="time" class="form-control" value="<?= $row['time'] ?>" required></td>
                        <td><input type="text" name="location" class="form-control" value="<?= htmlspecialchars($row['location']) ?>" required></td>
                        <td><?= htmlspecialchars($row['round_type']) ?></td>
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
                            <button name="set_approved" class="btn btn-success btn-sm" type="submit">✓ Setujui</button>
                            <button name="set_pending" class="btn btn-secondary btn-sm" type="submit">⏸ Pending</button>
                            </div>
                            <div class="d-flex gap-1">
                            <button name="update" class="btn btn-primary btn-sm" type="submit">✎ Update</button>
                            <button name="selesai" class="btn btn-info btn-sm" type="submit">✓ Selesai</button>
                            </div>
                        </div>
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                        </td>

                    </form>
                </tr>
            <?php endwhile; ?>
            <?php if ($result->num_rows == 0): ?>
                <tr class="no-data-row">
                    <td colspan="9" class="text-center py-4">
                        Belum ada jadwal pertandingan yang tercatat. Silakan klik <strong>"Buat Jadwal"</strong> untuk memulai liga.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
<?php 
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $msg = '';
    
    switch ($action) {
        case 'buat_jadwal':
            $msg = 'Jadwal Penyisihan berhasil dibuat!';
            break;
        case 'jadwal_sudah_ada':
            $msg = 'Gagal membuat jadwal. Jadwal Penyisihan sudah ada di database!';
            break;
        case 'gagal_genap':
            $msg = 'Gagal membuat jadwal. Algoritma ini hanya mendukung jumlah tim ganjil (N).';
            break;
        case 'gagal_ganjil': // 🔥 PERUBAHAN PESAN JADI LEBIH RINGKAS
            $msg = 'Gagal membuat jadwal. Jumlah tim harus genap (2, 4, 6, dst.).';
            break;
        case 'selesai':
            $msg = 'Status pertandingan berhasil ditandai selesai!';
            break;
        case 'auto_knockout':
            if (isset($_GET['msg'])) {
                $msg = urldecode($_GET['msg']);
            } else {
                $msg = 'Status selesai, dan Babak Gugur berhasil dibuat secara otomatis!';
            }
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
        case 'tim_kurang':
             $msg = 'Gagal membuat jadwal. Minimal diperlukan 2 tim dengan status pembayaran "Sudah Bayar".';
             break;
    }
    
    if ($msg) {
        echo "window.onload = function() { alert('" . addslashes($msg) . "'); };";
    }
}
?>
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>