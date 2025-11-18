<?php
include_once('../session_check.php');
include 'koneksi.php';

// Hapus Tim beserta semua data terkait
if (isset($_GET['delete_team']) && isset($_GET['team_id'])) {
    $team_id = intval($_GET['team_id']);

    // Mulai transaction
    $conn->begin_transaction();

    try {
        // 1. Ambil semua schedules yang melibatkan tim
        $stmt = $conn->prepare("SELECT id FROM schedules WHERE team_home_id = ? OR team_away_id = ?");
        $stmt->bind_param("ii", $team_id, $team_id);
        $stmt->execute();
        $schedules = $stmt->get_result();

        // 2. Hapus data matches berdasarkan schedule_id
        $stmt_match = $conn->prepare("DELETE FROM matches WHERE schedule_id = ?");
        while ($sc = $schedules->fetch_assoc()) {
            $sid = $sc['id'];
            $stmt_match->bind_param("i", $sid);
            $stmt_match->execute();
        }
        $stmt_match->close();
        $stmt->close();

        // 3. Hapus lineups berdasarkan match_id (yang merupakan schedule_id)
        $stmt = $conn->prepare("DELETE FROM lineups WHERE match_id IN (SELECT id FROM schedules WHERE team_home_id = ? OR team_away_id = ?)");
        $stmt->bind_param("ii", $team_id, $team_id);
        $stmt->execute();
        $stmt->close();

        // 4. Hapus jadwal pada tabel schedules
        $stmt = $conn->prepare("DELETE FROM schedules WHERE team_home_id = ? OR team_away_id = ?");
        $stmt->bind_param("ii", $team_id, $team_id);
        $stmt->execute();
        $stmt->close();

        // 5. Hapus standings
        $stmt = $conn->prepare("DELETE FROM standings WHERE team_id = ?");
        $stmt->bind_param("i", $team_id);
        $stmt->execute();
        $stmt->close();

        // 6. Hapus pemain
        $stmt = $conn->prepare("DELETE FROM players WHERE team_id = ?");
        $stmt->bind_param("i", $team_id);
        $stmt->execute();
        $stmt->close();

        // 7. Hapus pembayaran
        $stmt = $conn->prepare("DELETE FROM payments WHERE team_id = ?");
        $stmt->bind_param("i", $team_id);
        $stmt->execute();
        $stmt->close();

        // 8. Hapus tim dari tabel teams
        $stmt = $conn->prepare("DELETE FROM teams WHERE id = ?");
        $stmt->bind_param("i", $team_id);
        $stmt->execute();
        $stmt->close();

        // Commit
        $conn->commit();

        echo "
        <script>
            alert('Tim dan seluruh data terkait berhasil dihapus.');
            window.location.href = 'konfirmasi_pembayaran.php';
        </script>";
        exit();

    } catch (Exception $e) {
        // Rollback jika gagal
        $conn->rollback();
        echo "
        <script>
            alert('Gagal menghapus tim: " . addslashes($e->getMessage()) . "');
            window.location.href = 'konfirmasi_pembayaran.php';
        </script>";
        exit();
    }
}


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

// Ambil data pembayaran dengan team_id
$result = $conn->query("
    SELECT payments.payment_id, payments.registration_date, payments.payment_status, payments.payment_proof, 
           teams.team_name, teams.id as team_id
    FROM payments
    JOIN teams ON payments.team_id = teams.id
    ORDER BY payments.registration_date DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Konfirmasi Pembayaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
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
        .btn-danger {
            background: linear-gradient(135deg, #ff7675 0%, #d63031 100%);
            border: none;
        }
        .btn-danger:hover {
            background: linear-gradient(135deg, #d63031 0%, #ff7675 100%);
            transform: translateY(-2px);
        }
        .btn-outline-primary {
            border-color: #4facfe;
            color: #4facfe;
        }
        .btn-outline-primary:hover {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            border-color: #4facfe;
            color: white;
        }
        .btn-outline-secondary {
            border-color: #a8b8d8;
            color: #6c757d;
        }
        .btn-outline-secondary:hover {
            background: linear-gradient(135deg, #a8b8d8 0%, #d4dff0 100%);
            border-color: #a8b8d8;
            color: #333;
        }
        .btn-outline-danger {
            border-color: #ff7675;
            color: #d63031;
        }
        .btn-outline-danger:hover {
            background: linear-gradient(135deg, #ff7675 0%, #d63031 100%);
            border-color: #ff7675;
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
        .badge.bg-success {
            background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%) !important;
        }
        .badge.bg-danger {
            background: linear-gradient(135deg, #ff7675 0%, #d63031 100%) !important;
        }
        .form-control:focus, .form-select:focus {
            border-color: #4facfe;
            box-shadow: 0 0 0 0.2rem rgba(79, 172, 254, 0.25);
        }
    </style>
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2 class="text-center mb-4">💳 Konfirmasi Pembayaran Tim</h2>

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <a href="dashboard_admin.php" class="btn btn-secondary btn-sm"> Kembali</a>
        
        <div class="d-flex gap-2 align-items-center">
            <select id="statusFilter" class="form-select form-select-sm" style="width: 150px;" onchange="filterStatus()">
                <option value="">Semua Status</option>
                <option value="Sudah Bayar">Sudah Bayar</option>
                <option value="Belum Bayar">Belum Bayar</option>
            </select>
            
            <div class="input-group" style="width: 250px;">
                <input type="text" id="search" class="form-control form-control-sm" placeholder="Cari nama tim..." onkeyup="filterTable()">
                <span class="input-group-text" style="cursor: pointer;" onclick="filterTable()">
                    <i class="bi bi-search"></i>
                </span>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="paymentTable">
                    <thead>
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
                                <td><?= date('d-m-Y', strtotime($row['registration_date'])) ?></td>
                                <td>
                                    <?php if ($row['payment_status'] == 'Sudah Bayar'): ?>
                                        <span class="badge bg-success">Sudah Bayar</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Belum Bayar</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row['payment_proof']): ?>
                                        <a href="uploads/payment_proofs/<?= $row['payment_proof'] ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-eye"></i> Lihat Bukti
                                        </a>
                                    <?php else: ?>
                                        <form action="upload_bukti.php" method="POST" enctype="multipart/form-data" class="d-inline-block">
                                            <input type="hidden" name="payment_id" value="<?= $row['payment_id'] ?>">
                                            <div class="input-group input-group-sm mb-1">
                                                <input type="file" name="proof" required accept="image/jpeg,image/jpg,image/png,image/gif" class="form-control form-control-sm">
                                            </div>
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-upload"></i> Upload
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-1 flex-wrap">
                                        <?php if ($row['payment_status'] == 'Belum Bayar'): ?>
                                            <?php if ($row['payment_proof']): ?>
                                                <a href="?id=<?= $row['payment_id'] ?>&status=bayar" class="btn btn-success btn-sm" onclick="return confirm('Tandai tim ini sudah bayar?')">
                                                    <i class="bi bi-check-circle"></i> Sudah Bayar
                                                </a>
                                            <?php else: ?>
                                                <button class="btn btn-success btn-sm" disabled title="Upload bukti dulu">
                                                    <i class="bi bi-check-circle"></i> Sudah Bayar
                                                </button>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <a href="?id=<?= $row['payment_id'] ?>&status=belum" class="btn btn-danger btn-sm" onclick="return confirm('Tandai tim ini belum bayar?')">
                                                <i class="bi bi-x-circle"></i> Belum Bayar
                                            </a>
                                        <?php endif; ?>
                                        
                                        <!-- Tombol Hapus Tim -->
                                       <a href="?delete_team=1&team_id=<?= $row['team_id'] ?>" 
                                            class="btn btn-outline-danger btn-sm"
                                            onclick="return confirm('Yakin ingin menghapus tim <?= htmlspecialchars($row['team_name']) ?>?\n\nSemua data terkait akan terhapus permanen dan tindakan tidak dapat dibatalkan.')">
                                                <i class="bi bi-trash"></i> Hapus Tim
                                       </a>

                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function filterTable() {
    let input = document.getElementById('search').value.toLowerCase();
    let statusFilter = document.getElementById('statusFilter').value;
    let rows = document.querySelectorAll('.payment-row');

    rows.forEach(row => {
        let teamName = row.cells[0].textContent.toLowerCase();
        let status = row.getAttribute('data-status');
        
        let matchesSearch = teamName.includes(input);
        let matchesStatus = (statusFilter === '' || status === statusFilter);
        
        row.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
    });
}

function filterStatus() {
    filterTable(); // Panggil fungsi filterTable untuk apply filter gabungan
}

// Auto filter saat mengetik di search box
document.getElementById('search').addEventListener('keyup', filterTable);
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>