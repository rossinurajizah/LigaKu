<?php
include_once('../session_check.php');
include 'koneksi.php';

// Ambil daftar pertandingan
$pertandingan = mysqli_query($conn, "
    SELECT m.id AS match_id, 
           t1.team_name AS home_team, 
           t2.team_name AS away_team,
           s.match_date
    FROM matches m
    JOIN schedules s ON m.schedule_id = s.id
    JOIN teams t1 ON s.team_home_id = t1.id
    JOIN teams t2 ON s.team_away_id = t2.id
    ORDER BY s.match_date DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Export Laporan Pertandingan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        .container {
            margin-top: 50px;
        }
        .card {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .btn-custom {
            background-color: #4CAF50;
            color: white;
        }
        .btn-custom:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header text-center">
                    <h3>Export Laporan Pertandingan</h3>
                </div>
                <div class="card-body">
                    <p class="text-center">Pilih pertandingan dan format ekspor:</p>
                    <div class="mb-3">
                        <select id="matchSelect" class="form-select">
                            <option value="" disabled selected>-- Pilih Pertandingan --</option>
                            <?php while($row = mysqli_fetch_assoc($pertandingan)): ?>
                                <option value="<?= $row['match_id']; ?>">
                                    <?= $row['home_team'] . " vs " . $row['away_team'] . " [{$row['match_date']}]" ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="d-flex justify-content-around">
                        <button id="exportCsvBtn" class="btn btn-custom btn-lg">Export ke CSV</button>
                        <button id="exportPdfBtn" class="btn btn-custom btn-lg">Export ke PDF</button>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <small>© 2025 LigaKu - Laporan Pertandingan Lengkap</small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const matchSelect = document.getElementById('matchSelect');
    const exportCsvBtn = document.getElementById('exportCsvBtn');
    const exportPdfBtn = document.getElementById('exportPdfBtn');

    exportCsvBtn.addEventListener('click', () => {
        const matchId = matchSelect.value;
        if (!matchId) return alert("Silakan pilih pertandingan terlebih dahulu.");
        window.location.href = `proses_export.php?match_id=${matchId}&export=csv`;
    });

    exportPdfBtn.addEventListener('click', () => {
        const matchId = matchSelect.value;
        if (!matchId) return alert("Silakan pilih pertandingan terlebih dahulu.");
        window.location.href = `proses_export.php?match_id=${matchId}&export=pdf`;
    });
</script>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
