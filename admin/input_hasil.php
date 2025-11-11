<?php
include_once('../session_check.php'); // sesuaikan path sesuai struktur folder
include 'koneksi.php';

// Ambil data jadwal pertandingan
$matches_result = $conn->query("SELECT s.id, s.match_date, t1.team_name AS home_team, t2.team_name AS away_team 
    FROM schedules s
    JOIN teams t1 ON s.team_home_id = t1.id
    JOIN teams t2 ON s.team_away_id = t2.id 
    ORDER BY s.match_date ASC");

// Ambil data pemain
$players_result = $conn->query("SELECT p.id, p.name, t.team_name, t.id as team_id 
    FROM players p 
    JOIN teams t ON p.team_id = t.id");
$players_data = [];
while ($row = $players_result->fetch_assoc()) {
    $players_data[] = $row;
}

// Ambil data tim
$teams_result = $conn->query("SELECT id, team_name FROM teams");
$teams_data = [];
while ($row = $teams_result->fetch_assoc()) {
    $teams_data[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Input Hasil Pertandingan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <h2>📝 Input Hasil Pertandingan</h2>
    <form method="POST" action="proses_hasil.php" id="formHasil">
        <div class="mb-3">
            <label>Pilih Jadwal Pertandingan</label>
            <select name="schedule_id" id="schedule_id" class="form-select" required>
                <option value="">-- Pilih Jadwal --</option>
                <?php 
                // Reset pointer supaya bisa dipakai ulang di JS nanti
                $matches_result->data_seek(0);
                while ($row = $matches_result->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>" 
                        data-home="<?= htmlspecialchars($row['home_team']) ?>"
                        data-away="<?= htmlspecialchars($row['away_team']) ?>"
                    ><?= $row['home_team'] ?> vs <?= $row['away_team'] ?> (<?= $row['match_date'] ?>)</option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="row mb-3">
            <div class="col">
                <label>Skor Tim A</label>
                <input type="number" id="score_a" name="score_a" class="form-control" value="0" readonly>
            </div>
            <div class="col">
                <label>Skor Tim B</label>
                <input type="number" id="score_b" name="score_b" class="form-control" value="0" readonly>
            </div>
        </div>

        <h5>⚽ Gol</h5>
        <div id="goals"></div>
        <button type="button" onclick="addGoal()" class="btn btn-outline-success mb-3">+ Tambah Gol</button>

        <h5>🚫 Pelanggaran</h5>
        <div id="fouls"></div>
        <button type="button" onclick="addFoul()" class="btn btn-outline-danger mb-3">+ Tambah Pelanggaran</button>

        <div class="d-flex justify-content-between mt-4">
            <a href="dashboard_admin.php" class="btn btn-secondary">Kembali</a>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </div>

    </form>
</div>

<script>
const players = <?= json_encode($players_data) ?>;
const teams = <?= json_encode($teams_data) ?>;

function playerOptions(nameAttr) {
    const select = document.createElement('select');
    select.name = nameAttr;
    select.className = 'form-select player-select';
    select.required = true;
    players.forEach(p => {
        const opt = document.createElement('option');
        opt.value = p.id;
        opt.textContent = `${p.name} (${p.team_name})`;
        opt.setAttribute('data-team-id', p.team_id);
        select.appendChild(opt);
    });
    // Update skor setiap kali pilihan berubah
    select.addEventListener('change', updateScores);
    return select;
}

function addGoal() {
    const row = document.createElement('div');
    row.className = 'row mb-2 align-items-center';

    const playerCol = document.createElement('div');
    playerCol.className = 'col-md-5';
    playerCol.appendChild(playerOptions('goals[player_id][]'));

    const minuteCol = document.createElement('div');
    minuteCol.className = 'col-md-3';
    minuteCol.innerHTML = '<input type="number" name="goals[minute][]" class="form-control" placeholder="Menit" min="0" required>';

    const removeCol = document.createElement('div');
    removeCol.className = 'col-md-2';
    removeCol.innerHTML = '<button type="button" class="btn btn-sm btn-danger" onclick="this.closest(\'.row\').remove(); updateScores();">Hapus</button>';

    row.appendChild(playerCol);
    row.appendChild(minuteCol);
    row.appendChild(removeCol);
    document.getElementById('goals').appendChild(row);

    updateScores();
}

function addFoul() {
    const row = document.createElement('div');
    row.className = 'row mb-2 align-items-center';

    const playerCol = document.createElement('div');
    playerCol.className = 'col-md-3';
    playerCol.appendChild(playerOptions('fouls[player_id][]'));

    const teamCol = document.createElement('div');
    teamCol.className = 'col-md-2';
    const teamSelect = document.createElement('select');
    teamSelect.name = 'fouls[team_id][]';
    teamSelect.className = 'form-select';
    const defaultOpt = document.createElement('option');
    defaultOpt.value = '';
    defaultOpt.textContent = 'Tim';
    teamSelect.appendChild(defaultOpt);
    teams.forEach(t => {
        const opt = document.createElement('option');
        opt.value = t.id;
        opt.textContent = t.team_name;
        teamSelect.appendChild(opt);
    });
    teamCol.appendChild(teamSelect);

    const minuteCol = document.createElement('div');
    minuteCol.className = 'col-md-2';
    minuteCol.innerHTML = '<input type="number" name="fouls[minute][]" class="form-control" placeholder="Menit" min="0" required>';

    const descCol = document.createElement('div');
    descCol.className = 'col-md-3';
    descCol.innerHTML = '<input type="text" name="fouls[description][]" class="form-control" placeholder="Deskripsi">';

    const cardCol = document.createElement('div');
    cardCol.className = 'col-md-2';
    cardCol.innerHTML = `
      <select name="fouls[card][]" class="form-select" required>
        <option value="">Pilih Kartu</option>
        <option value="yellow">Kuning</option>
        <option value="red">Merah</option>
      </select>
    `;

    const removeCol = document.createElement('div');
    removeCol.className = 'col-md-12 mt-2';
    removeCol.innerHTML = '<button type="button" class="btn btn-sm btn-danger" onclick="this.closest(\'.row\').remove()">Hapus</button>';

    row.appendChild(playerCol);
    row.appendChild(teamCol);
    row.appendChild(minuteCol);
    row.appendChild(descCol);
    row.appendChild(cardCol);
    row.appendChild(removeCol);

    document.getElementById('fouls').appendChild(row);
}

function updateScores() {
    const scheduleSelect = document.getElementById('schedule_id');
    const selectedOption = scheduleSelect.options[scheduleSelect.selectedIndex];

    const homeTeamName = selectedOption.getAttribute('data-home');
    const awayTeamName = selectedOption.getAttribute('data-away');

    let scoreA = 0;
    let scoreB = 0;

    const goalPlayerSelects = document.querySelectorAll('select[name="goals[player_id][]"]');
    
    goalPlayerSelects.forEach(sel => {
        const playerId = sel.value;
        const player = players.find(p => p.id == playerId);
        if (player) {
            if (player.team_name === homeTeamName) {
                scoreA++;
            } else if (player.team_name === awayTeamName) {
                scoreB++;
            }
        }
    });

    document.getElementById('score_a').value = scoreA;
    document.getElementById('score_b').value = scoreB;
}

document.getElementById('schedule_id').addEventListener('change', updateScores);

window.onload = () => {
    updateScores();
};
</script>
</body>
</html>
