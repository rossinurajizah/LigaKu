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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background: #f8f9fa;
            min-height: 100vh;
            padding: 20px 0;
        }
        .container {
            margin-top: 50px;
        }
        h3 {
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
        .card-header {
            background: transparent;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }
        .card-footer {
            background: transparent;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }
        .btn-success {
            background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%);
            border: none;
        }
        .btn-success:hover {
            background: linear-gradient(135deg, #a8e063 0%, #56ab2f 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(86, 171, 47, 0.4);
        }
        .btn-info {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            border: none;
        }
        .btn-info:hover {
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
        .form-select {
            border: 1px solid rgba(0, 0, 0, 0.1);
            background: rgba(255, 255, 255, 0.9);
        }
        .form-select:focus {
            border-color: #4facfe;
            box-shadow: 0 0 0 0.2rem rgba(79, 172, 254, 0.25);
            background: #fff;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header text-center">
                    <h3>📊 Export Laporan Pertandingan</h3>
                </div>
                <div class="card-body">
                    <p class="text-center">Pilih pertandingan dan format ekspor:</p>
                    <div class="mb-3">
                        <select id="matchSelect" class="form-select">
                            <option value="" disabled selected>-- Pilih Pertandingan --</option>
                            <?php while($row = mysqli_fetch_assoc($pertandingan)): ?>
                                <option value="<?= htmlspecialchars($row['match_id']); ?>">
                                    <?= htmlspecialchars($row['home_team']) . " vs " . htmlspecialchars($row['away_team']) . " [{$row['match_date']}]" ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="card-footer">
                    <!-- Semua button sejajar dalam 1 baris di tengah -->
                    <div class="d-flex justify-content-center gap-2 mb-3">
                        <a href="dashboard_admin.php" class="btn btn-secondary">Kembali</a>
                        <button id="exportCsvBtn" class="btn btn-success">📄 Export CSV</button>
                        <button id="exportPdfBtn" class="btn btn-info">📑 Export PDF</button>
                    </div>
                    <div class="text-center">
                        <small>© 2025 LigaKu - Laporan Pertandingan Lengkap</small>
                    </div>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>