<?php
include_once('../session_check.php'); // sesuaikan path sesuai struktur folder
include 'koneksi.php';
function js_alert($message, $redirect = null) {
    echo "<script>alert(" . json_encode($message) . ");";
    if ($redirect) {
        echo "window.location.href = " . json_encode($redirect) . ";";
    }
    echo "</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    js_alert('Access denied.', 'index.php');  // arahkan ke halaman utama atau form
}

// Ambil data dari form
$schedule_id = $_POST['schedule_id'] ?? null;
$score_a = $_POST['score_a'] ?? null;
$score_b = $_POST['score_b'] ?? null;
$goals = $_POST['goals'] ?? [];
$fouls = $_POST['fouls'] ?? [];

// Validasi input
if (!$schedule_id || $score_a === null || $score_b === null) {
    js_alert("Invalid input: match data incomplete.", 'form_input.php');
}

// Cek apakah hasil pertandingan sudah ada
$cek = $conn->prepare("SELECT id FROM matches WHERE schedule_id = ?");
$cek->bind_param("i", $schedule_id);
$cek->execute();
$cek->store_result();

if ($cek->num_rows > 0) {
    $cek->close();
    js_alert("❌ Hasil pertandingan untuk jadwal ini sudah pernah disimpan.", 'form_input.php');
}
$cek->close();

// Tentukan hasil pertandingan SESUAI ENUM
if ($score_a > $score_b) {
    $result = 'Home Team Win';
} elseif ($score_b > $score_a) {
    $result = 'Away Team Win';
} else {
    $result = 'Draw';
}

// Simpan hasil pertandingan
$stmt = $conn->prepare("INSERT INTO matches (schedule_id, score_a, score_b, result) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iiis", $schedule_id, $score_a, $score_b, $result);
$stmt->execute();
$match_id = $stmt->insert_id;
$stmt->close();

// Simpan gol
if (!empty($goals['player_id'])) {
    $stmt = $conn->prepare("INSERT INTO goals (match_id, player_id, minute) VALUES (?, ?, ?)");
    for ($i = 0; $i < count($goals['player_id']); $i++) {
        $pid = $goals['player_id'][$i];
        $min = $goals['minute'][$i];
        $stmt->bind_param("iii", $match_id, $pid, $min);
        $stmt->execute();
    }
    $stmt->close();
}

// Simpan pelanggaran
if (!empty($fouls['player_id'])) {
    $stmt = $conn->prepare("INSERT INTO fouls (match_id, player_id, team_id, minute, description, card) VALUES (?, ?, ?, ?, ?, ?)");
    for ($i = 0; $i < count($fouls['player_id']); $i++) {
        $pid = $fouls['player_id'][$i];
        $tid = $fouls['team_id'][$i];
        $min = $fouls['minute'][$i];
        $desc = $fouls['description'][$i] ?? '';
        $card = $fouls['card'][$i] ?? null;
        $stmt->bind_param("iiiiss", $match_id, $pid, $tid, $min, $desc, $card);
        $stmt->execute();
    }
    $stmt->close();
}

// Ambil ID tim dari jadwal
$stmt = $conn->prepare("SELECT team_home_id, team_away_id FROM schedules WHERE id = ?");
$stmt->bind_param("i", $schedule_id);
$stmt->execute();
$stmt->bind_result($team_a_id, $team_b_id);
$stmt->fetch();
$stmt->close();

// Fungsi update klasemen
function updateStandings($conn, $team_id, $gf, $ga, $win, $draw, $loss) {
    $points = ($win * 3) + ($draw * 1);
    $gd = $gf - $ga;

    $sql = "INSERT INTO standings (team_id, matches_played, wins, draws, losses, goals_for, goals_against, goal_diff, points)
            VALUES ($team_id, 1, $win, $draw, $loss, $gf, $ga, $gd, $points)
            ON DUPLICATE KEY UPDATE
                matches_played = matches_played + 1,
                wins = wins + $win,
                draws = draws + $draw,
                losses = losses + $loss,
                goals_for = goals_for + $gf,
                goals_against = goals_against + $ga,
                goal_diff = goal_diff + $gd,
                points = points + $points";

    $conn->query($sql);
}

// Update klasemen
if ($score_a > $score_b) {
    updateStandings($conn, $team_a_id, $score_a, $score_b, 1, 0, 0);
    updateStandings($conn, $team_b_id, $score_b, $score_a, 0, 0, 1);
} elseif ($score_b > $score_a) {
    updateStandings($conn, $team_a_id, $score_a, $score_b, 0, 0, 1);
    updateStandings($conn, $team_b_id, $score_b, $score_a, 1, 0, 0);
} else {
    updateStandings($conn, $team_a_id, $score_a, $score_b, 0, 1, 0);
    updateStandings($conn, $team_b_id, $score_b, $score_a, 0, 1, 0);
}

// Sukses
js_alert("✅ Hasil pertandingan berhasil disimpan & klasemen diperbarui.", 'dashboard_admin.php');
?>
