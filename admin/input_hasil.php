<?php
include_once('../session_check.php'); // sesuaikan path sesuai struktur folder
include 'koneksi.php';

// --- Perbaikan Logika PHP ---
// Ambil data jadwal pertandingan dan ID Tim
$matches_result = $conn->query("
    SELECT 
        s.id, s.match_date, 
        t1.team_name AS home_team, t2.team_name AS away_team,
        t1.id AS home_team_id, t2.id AS away_team_id
    FROM schedules s
    JOIN teams t1 ON s.team_home_id = t1.id
    JOIN teams t2 ON s.team_away_id = t2.id 
    WHERE s.status = 'approved' OR s.status = 'pending'
    ORDER BY s.match_date ASC
");
// Simpan semua data pertandingan, termasuk ID tim, ke dalam array JS
$matches_data = [];
while ($row = $matches_result->fetch_assoc()) {
    $matches_data[] = $row;
}


// Ambil data pemain (dengan ID tim)
$players_result = $conn->query("
    SELECT p.id, p.name, t.team_name, t.id as team_id 
    FROM players p 
    JOIN teams t ON p.team_id = t.id
");
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
    <style>
        body {
            background: #f8f9fa;
        }
        h2 {
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
        .btn-outline-success {
            border-color: #56ab2f;
            color: #56ab2f;
        }
        .btn-outline-success:hover {
            background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%);
            border-color: #56ab2f;
            color: white;
        }
        .btn-outline-danger {
            border-color: #d63031;
            color: #d63031;
        }
        .btn-outline-danger:hover {
            background: linear-gradient(135deg, #ff7675 0%, #d63031 100%);
            border-color: #d63031;
            color: white;
        }
        .btn-danger {
            background: linear-gradient(135deg, #ff7675 0%, #d63031 100%);
            border: none;
        }
        .btn-danger:hover {
            background: linear-gradient(135deg, #d63031 0%, #ff7675 100%);
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="bg-light">

<div class="container py-5">
    <h2 class="text-center mb-4">📝 Input Hasil Pertandingan</h2>
    <form method="POST" action="proses_hasil.php" id="formHasil">
        <div class="mb-3">
            <label>Pilih Jadwal Pertandingan</label>
            <select name="schedule_id" id="schedule_id" class="form-select" required>
                <option value="" data-home-id="" data-away-id="">-- Pilih Jadwal --</option>
                <?php foreach ($matches_data as $row): ?>
                    <option 
                        value="<?= $row['id'] ?>" 
                        data-home-id="<?= $row['home_team_id'] ?>"
                        data-away-id="<?= $row['away_team_id'] ?>"
                        data-home-name="<?= htmlspecialchars($row['home_team']) ?>"
                        data-away-name="<?= htmlspecialchars($row['away_team']) ?>"
                    >
                        <?= $row['home_team'] ?> vs <?= $row['away_team'] ?> (<?= $row['match_date'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="row mb-3">
            <div class="col">
                <label id="label_score_a">Skor Tim A</label>
                <input type="number" id="score_a" name="score_a" class="form-control" value="0" readonly>
            </div>
            <div class="col">
                <label id="label_score_b">Skor Tim B</label>
                <input type="number" id="score_b" name="score_b" class="form-control" value="0" readonly>
            </div>
        </div>

        <hr>

        <h5>⚽ Gol</h5>
        <div id="goals"></div>
        <button type="button" onclick="addGoal()" class="btn btn-outline-success mb-3">+ Tambah Gol</button>

        <hr>

        <h5>🚫 Pelanggaran (Kartu)</h5>
        <div id="fouls"></div>
        <button type="button" onclick="addFoul()" class="btn btn-outline-danger mb-3">+ Tambah Pelanggaran</button>

        <div class="d-flex justify-content-between mt-4">
            <a href="dashboard_admin.php" class="btn btn-secondary"> Kembali</a>
            <button type="submit" class="btn btn-primary"> Simpan</button>
        </div>

    </form>
</div>

<script>
// Data diambil dari PHP
const ALL_PLAYERS = <?= json_encode($players_data) ?>;
const ALL_TEAMS = <?= json_encode($teams_data) ?>;
const ALL_MATCHES = <?= json_encode($matches_data) ?>; 
const GOALS_CONTAINER = document.getElementById('goals');
const FOULS_CONTAINER = document.getElementById('fouls');
const SCHEDULE_SELECT = document.getElementById('schedule_id');

// Fungsi Utama untuk Membuat Dropdown Pemain yang DIFILTER
function createPlayerOptions(nameAttr, currentHomeId, currentAwayId) {
    const select = document.createElement('select');
    select.name = nameAttr;
    select.className = 'form-select player-select';
    select.required = true;

    const defaultOpt = document.createElement('option');
    defaultOpt.value = '';
    defaultOpt.textContent = '-- Pilih Pemain --';
    select.appendChild(defaultOpt);

    const filteredPlayers = ALL_PLAYERS.filter(p => 
        p.team_id == currentHomeId || p.team_id == currentAwayId
    );

    if (filteredPlayers.length === 0) {
        const noPlayerOpt = document.createElement('option');
        noPlayerOpt.textContent = 'Tidak ada pemain terdaftar di tim ini.';
        noPlayerOpt.disabled = true;
        select.appendChild(noPlayerOpt);
    }
    
    filteredPlayers.forEach(p => {
        const opt = document.createElement('option');
        opt.value = p.id;
        opt.textContent = `${p.name} (${p.team_name})`;
        opt.setAttribute('data-team-id', p.team_id);
        select.appendChild(opt);
    });
    
    if (nameAttr.includes('goals')) {
        select.addEventListener('change', updateScores);
    }
    return select;
}

// Fungsi untuk Mengubah Dropdown Pemain saat Jadwal Berubah
function refreshDynamicFields() {
    const scheduleSelect = SCHEDULE_SELECT;
    const selectedOption = scheduleSelect.options[scheduleSelect.selectedIndex];

    const homeId = selectedOption.getAttribute('data-home-id');
    const awayId = selectedOption.getAttribute('data-away-id');
    const homeName = selectedOption.getAttribute('data-home-name');
    const awayName = selectedOption.getAttribute('data-away-name');
    
    document.getElementById('label_score_a').textContent = `Skor ${homeName || 'Tim A'}`;
    document.getElementById('label_score_b').textContent = `Skor ${awayName || 'Tim B'}`;

    GOALS_CONTAINER.innerHTML = '';
    FOULS_CONTAINER.innerHTML = '';

    if (!homeId) {
        document.getElementById('score_a').value = 0;
        document.getElementById('score_b').value = 0;
        return;
    }
}


function addGoal() {
    const scheduleSelect = SCHEDULE_SELECT;
    const selectedOption = scheduleSelect.options[scheduleSelect.selectedIndex];
    const homeId = selectedOption.getAttribute('data-home-id');
    const awayId = selectedOption.getAttribute('data-away-id');

    if (!homeId) {
        alert("Pilih jadwal pertandingan terlebih dahulu.");
        return;
    }

    const row = document.createElement('div');
    row.className = 'row mb-2 align-items-center';

    const playerCol = document.createElement('div');
    playerCol.className = 'col-md-5';
    playerCol.appendChild(createPlayerOptions('goals[player_id][]', homeId, awayId));

    const minuteCol = document.createElement('div');
    minuteCol.className = 'col-md-3';
    minuteCol.innerHTML = '<input type="number" name="goals[minute][]" class="form-control" placeholder="Menit" min="0" required>';

    const removeCol = document.createElement('div');
    removeCol.className = 'col-md-4';
    removeCol.innerHTML = '<button type="button" class="btn btn-sm btn-danger" onclick="this.closest(\'.row\').remove(); updateScores();">Hapus Gol</button>';

    row.appendChild(playerCol);
    row.appendChild(minuteCol);
    row.appendChild(removeCol);
    GOALS_CONTAINER.appendChild(row);

    updateScores();
}

function addFoul() {
    const scheduleSelect = SCHEDULE_SELECT;
    const selectedOption = scheduleSelect.options[scheduleSelect.selectedIndex];
    const homeId = selectedOption.getAttribute('data-home-id');
    const awayId = selectedOption.getAttribute('data-away-id');
    const homeName = selectedOption.getAttribute('data-home-name');
    const awayName = selectedOption.getAttribute('data-away-name');

    if (!homeId) {
        alert("Pilih jadwal pertandingan terlebih dahulu.");
        return;
    }

    const row = document.createElement('div');
    row.className = 'row mb-2 align-items-center';

    // Kolom Pemain (difilter)
    const playerCol = document.createElement('div');
    playerCol.className = 'col-md-2'; 
    playerCol.appendChild(createPlayerOptions('fouls[player_id][]', homeId, awayId));
    
    // Kolom Tim Pelanggar
    const teamCol = document.createElement('div');
    teamCol.className = 'col-md-2'; 
    const teamSelect = document.createElement('select');
    teamSelect.name = 'fouls[team_id][]';
    teamSelect.className = 'form-select';
    teamSelect.required = true;
    
    const defaultOpt = document.createElement('option');
    defaultOpt.value = '';
    defaultOpt.textContent = 'Tim...';
    teamSelect.appendChild(defaultOpt);
    
    if (homeId) {
        const homeOpt = document.createElement('option');
        homeOpt.value = homeId;
        homeOpt.textContent = homeName;
        teamSelect.appendChild(homeOpt);
    }
    if (awayId) {
        const awayOpt = document.createElement('option');
        awayOpt.value = awayId;
        awayOpt.textContent = awayName;
        teamSelect.appendChild(awayOpt);
    }
    teamCol.appendChild(teamSelect);


    // Kolom Menit
    const minuteCol = document.createElement('div');
    minuteCol.className = 'col-md-1'; 
    minuteCol.innerHTML = '<input type="number" name="fouls[minute][]" class="form-control" placeholder="Mnt" min="0" required>';

    // Kolom Kartu
    const cardCol = document.createElement('div');
    cardCol.className = 'col-md-2'; 
    cardCol.innerHTML = `
      <select name="fouls[card][]" class="form-select" required>
        <option value="">Kartu</option>
        <option value="yellow">Kuning</option>
        <option value="red">Merah</option>
      </select>
    `;

    // Kolom Deskripsi Pelanggaran (DITAMBAH KEMBALI)
    const descCol = document.createElement('div');
    descCol.className = 'col-md-3';
    descCol.innerHTML = '<input type="text" name="fouls[description][]" class="form-control" placeholder="Deskripsi Pelanggaran" required>';
    
    // Kolom Hapus
    const removeCol = document.createElement('div');
    removeCol.className = 'col-md-2'; 
    removeCol.innerHTML = '<button type="button" class="btn btn-sm btn-danger" onclick="this.closest(\'.row\').remove()">Hapus Pelanggaran</button>';
    
    row.appendChild(playerCol);
    row.appendChild(teamCol);
    row.appendChild(minuteCol);
    row.appendChild(cardCol);
    row.appendChild(descCol); // Pastikan ini ditambahkan
    row.appendChild(removeCol);

    FOULS_CONTAINER.appendChild(row);
}


function updateScores() {
    const scheduleSelect = SCHEDULE_SELECT;
    const selectedOption = scheduleSelect.options[scheduleSelect.selectedIndex];

    const homeTeamId = selectedOption.getAttribute('data-home-id');
    const awayTeamId = selectedOption.getAttribute('data-away-id');

    let scoreA = 0;
    let scoreB = 0;

    const goalPlayerSelects = GOALS_CONTAINER.querySelectorAll('select[name="goals[player_id][]"]');
    
    goalPlayerSelects.forEach(sel => {
        const selectedPlayerOption = sel.options[sel.selectedIndex];
        if (selectedPlayerOption.value) {
            const teamId = selectedPlayerOption.getAttribute('data-team-id');
            if (teamId == homeTeamId) {
                scoreA++;
            } else if (teamId == awayTeamId) {
                scoreB++;
            }
        }
    });

    document.getElementById('score_a').value = scoreA;
    document.getElementById('score_b').value = scoreB;
}

// Pemicu utama saat jadwal dipilih
SCHEDULE_SELECT.addEventListener('change', () => {
    refreshDynamicFields(); 
    updateScores(); 
});

// Jalankan saat load
window.onload = () => {
    refreshDynamicFields();
};
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>