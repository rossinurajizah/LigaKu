<?php
include_once('../session_check.php'); // sesuaikan path sesuai struktur folder
include 'koneksi.php';

// Update status pembayaran
if (isset($_GET['id']) && isset($_GET['status'])) {
    $id = intval($_GET['id']);
    $status = $_GET['status'] === 'bayar' ? 'Sudah Bayar' : 'Belum Bayar';

    $conn->query("UPDATE payments SET payment_status='$status' WHERE payment_id=$id");

    $data = $conn->query("SELECT team_id FROM payments WHERE payment_id=$id")->fetch_assoc();
    $team_id = $data['team_id'];

    if ($status === 'Sudah Bayar') {
        $cek = $conn->query("SELECT * FROM standings WHERE team_id=$team_id");
        if ($cek->num_rows == 0) {
            $conn->query("INSERT INTO standings (team_id, matches_played, wins, draws, losses, goals_for, goals_against, goal_diff, points) 
                          VALUES ($team_id, 0, 0, 0, 0, 0, 0, 0, 0)");
        }
    } else {
        $conn->query("DELETE FROM standings WHERE team_id=$team_id");
    }

    header("Location: konfirmasi_pembayaran.php");
    exit();
}

// Ambil data pembayaran
$result = $conn->query("
    SELECT payments.payment_id, payments.registration_date, payments.payment_status, payments.payment_proof, teams.team_name
    FROM payments
    JOIN teams ON payments.team_id = teams.id
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Konfirmasi Pembayaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="dashboard_admin.php" class="btn btn-secondary btn-sm">Kembali ke Dashboard</a>
        <div class="input-group" style="max-width: 300px;">
            <input type="text" id="search" class="form-control" placeholder="Cari nama tim...">
            <span class="input-group-text" style="cursor: pointer;" onclick="filterTable()">
                <i class="bi bi-search"></i>
            </span>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-header bg-warning text-white text-center">
            <h4>💳 Konfirmasi Pembayaran Tim</h4>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label for="statusFilter" class="form-label">Filter Status Pembayaran:</label>
                <select id="statusFilter" class="form-select" onchange="filterStatus()">
                    <option value="">Semua</option>
                    <option value="Sudah Bayar">Sudah Bayar</option>
                    <option value="Belum Bayar">Belum Bayar</option>
                </select>
            </div>

            <table class="table table-bordered table-striped" id="paymentTable">
                <thead class="table-dark">
                    <tr>
                        <th>Nama Tim</th>
                        <th>Tanggal Pendaftaran</th>
                        <th>Status Pembayaran</th>
                        <th>Bukti Pembayaran</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr class="payment-row" data-status="<?= $row['payment_status'] ?>">
                            <td><?= htmlspecialchars($row['team_name']) ?></td>
                            <td><?= htmlspecialchars($row['registration_date']) ?></td>
                            <td>
                                <?php if ($row['payment_status'] == 'Sudah Bayar'): ?>
                                    <span class="badge bg-success">Sudah Bayar</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Belum Bayar</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($row['payment_proof']): ?>
                                    <a href="uploads/payment_proofs/<?= $row['payment_proof'] ?>" target="_blank" class="btn btn-outline-primary btn-sm">Lihat Bukti</a>
                                <?php else: ?>
                                    <form action="upload_bukti.php" method="POST" enctype="multipart/form-data" style="display:inline;">
                                        <input type="hidden" name="payment_id" value="<?= $row['payment_id'] ?>">
                                        <input type="file" name="proof" required accept="image/*,.pdf" class="form-control form-control-sm mb-1">
                                        <button type="submit" class="btn btn-sm btn-outline-secondary">Upload</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($row['payment_status'] == 'Belum Bayar'): ?>
                                    <?php if ($row['payment_proof']): ?>
                                        <a href="?id=<?= $row['payment_id'] ?>&status=bayar" class="btn btn-success btn-sm">✔ Tandai Sudah Bayar</a>
                                    <?php else: ?>
                                        <button class="btn btn-success btn-sm" disabled>✔ Tandai Sudah Bayar</button>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <a href="?id=<?= $row['payment_id'] ?>&status=belum" class="btn btn-danger btn-sm">✖ Tandai Belum Bayar</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function filterTable() {
    let input = document.getElementById('search').value.toLowerCase();
    let rows = document.querySelectorAll('.payment-row');

    rows.forEach(row => {
        let teamName = row.cells[0].textContent.toLowerCase();
        row.style.display = teamName.includes(input) ? '' : 'none';
    });
}

function filterStatus() {
    let selected = document.getElementById('statusFilter').value;
    let rows = document.querySelectorAll('.payment-row');

    rows.forEach(row => {
        let status = row.getAttribute('data-status');
        row.style.display = (selected === '' || status === selected) ? '' : 'none';
    });
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
