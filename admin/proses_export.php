<?php
include 'koneksi.php';

$match_id = isset($_GET['match_id']) ? intval($_GET['match_id']) : 0;
$export = $_GET['export'] ?? 'pdf';

// Fetch main data
$match = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT m.id, s.match_date, s.time, s.location, 
           t1.team_name AS home_team, t2.team_name AS away_team,
           m.score_a, m.score_b, m.result
    FROM matches m
    JOIN schedules s ON m.schedule_id = s.id
    JOIN teams t1 ON s.team_home_id = t1.id
    JOIN teams t2 ON s.team_away_id = t2.id
    WHERE m.id = $match_id
"));

// Fetch all other components
function fetchAll($conn, $query) {
    return mysqli_fetch_all(mysqli_query($conn, $query), MYSQLI_ASSOC);
}

$lineups = fetchAll($conn, "
    SELECT p.name, t.team_name, l.is_starting
    FROM lineups l
    JOIN players p ON l.player_id = p.id
    JOIN teams t ON p.team_id = t.id
    WHERE l.match_id = (SELECT schedule_id FROM matches WHERE id = {$match['id']})
");


$goals = fetchAll($conn, "
    SELECT p.name, g.minute, t.team_name
    FROM goals g
    JOIN players p ON g.player_id = p.id
    JOIN teams t ON p.team_id = t.id
    WHERE g.match_id = {$match['id']}
");

$fouls = fetchAll($conn, "
    SELECT p.name, f.minute, f.card, f.description, t.team_name
    FROM fouls f
    JOIN players p ON f.player_id = p.id
    JOIN teams t ON p.team_id = t.id
    WHERE f.match_id = {$match['id']}
");

$motm = fetchAll($conn, "
    SELECT p.name, t.team_name
    FROM motm m
    JOIN players p ON m.player_id = p.id
    JOIN teams t ON p.team_id = t.id
    WHERE m.match_id = {$match['id']}
");

if ($export === 'csv') {
    header('Content-Type: text/csv');
    header("Content-Disposition: attachment; filename=laporan_pertandingan_{$match_id}.csv");
    $out = fopen('php://output', 'w');

    // Match info
    fputcsv($out, ["Informasi Pertandingan"]);
    fputcsv($out, ["ID", "Tanggal", "Waktu", "Lokasi", "Tim A", "Tim B", "Skor A", "Skor B", "Hasil"]);
    fputcsv($out, [$match['id'], $match['match_date'], $match['time'], $match['location'], $match['home_team'], $match['away_team'], $match['score_a'], $match['score_b'], $match['result']]);
    fputcsv($out, []);

    // Line-up
    fputcsv($out, ["Line-up Pemain"]);
    fputcsv($out, ["Nama Pemain", "Tim", "Status"]);
    foreach ($lineups as $l) {
        $status = $l['is_starting'] ? "Starter" : "Cadangan";
        fputcsv($out, [$l['name'], $l['team_name'], $status]);
    }
    fputcsv($out, []);

    // Gol
    fputcsv($out, ["Pencetak Gol"]);
    fputcsv($out, ["Nama Pemain", "Tim", "Menit"]);
    foreach ($goals as $g) {
        fputcsv($out, [$g['name'], $g['team_name'], $g['minute']]);
    }
    fputcsv($out, []);

    // Pelanggaran
    fputcsv($out, ["Pelanggaran & Kartu"]);
    fputcsv($out, ["Nama Pemain", "Tim", "Menit", "Kartu", "Deskripsi"]);
    foreach ($fouls as $f) {
        fputcsv($out, [$f['name'], $f['team_name'], $f['minute'], $f['card'], $f['description']]);
    }
    fputcsv($out, []);

    // MOTM
    fputcsv($out, ["Man of The Match"]);
    fputcsv($out, ["Nama Pemain", "Tim"]);
    foreach ($motm as $m) {
        fputcsv($out, [$m['name'], $m['team_name']]);
    }

    fclose($out);
    exit;

} elseif ($export === 'pdf') {
    require('fpdf.php');
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(190, 10, "Laporan Pertandingan #{$match['id']}", 0, 1, 'C');

    $pdf->SetFont('Arial', '', 12);
    $pdf->MultiCell(0, 8, "
Tanggal     : {$match['match_date']} {$match['time']}
Lokasi      : {$match['location']}
Tim         : {$match['home_team']} vs {$match['away_team']}
Skor Akhir  : {$match['score_a']} - {$match['score_b']} ({$match['result']})
", 0);

    $pdf->Ln(5); $pdf->SetFont('Arial','B',12); $pdf->Cell(0, 10, 'Line-up:', 0, 1);
    $pdf->SetFont('Arial','',11);
    foreach ($lineups as $l) {
        $status = $l['is_starting'] ? "Starter" : "Cadangan";
        $pdf->Cell(0, 7, "{$l['team_name']} - {$l['name']} ({$status})", 0, 1);
    }

    $pdf->Ln(5); $pdf->SetFont('Arial','B',12); $pdf->Cell(0, 10, 'Pencetak Gol:', 0, 1);
    $pdf->SetFont('Arial','',11);
    foreach ($goals as $g) {
        $pdf->Cell(0, 7, "{$g['team_name']} - {$g['name']} ({$g['minute']}')", 0, 1);
    }

    $pdf->Ln(5); $pdf->SetFont('Arial','B',12); $pdf->Cell(0, 10, 'Pelanggaran & Kartu:', 0, 1);
    $pdf->SetFont('Arial','',11);
    foreach ($fouls as $f) {
        $pdf->Cell(0, 7, "{$f['team_name']} - {$f['name']} ({$f['minute']}') - {$f['card']} - {$f['description']}", 0, 1);
    }

    $pdf->Ln(5); $pdf->SetFont('Arial','B',12); $pdf->Cell(0, 10, 'Man of The Match:', 0, 1);
    $pdf->SetFont('Arial','',11);
    foreach ($motm as $m) {
        $pdf->Cell(0, 7, "{$m['team_name']} - {$m['name']}", 0, 1);
    }

    $pdf->Output('D', "laporan_pertandingan_{$match_id}.pdf");
    exit;

} else {
    echo "Format tidak dikenali (gunakan csv atau pdf).";
}
?>
