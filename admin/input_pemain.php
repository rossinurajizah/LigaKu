<?php
include_once('../session_check.php'); // sesuaikan path sesuai struktur folder
include 'koneksi.php';

$team_id = $_SESSION['team_id'] ?? null;

if (!$team_id) {
    die("Team ID tidak tersedia. Pastikan sudah login atau memilih tim.");
}

// Daftar posisi yang valid
$valid_positions = ['Goalkeeper', 'Defender', 'Midfielder', 'Forward'];

// Hitung jumlah pemain
$stmt_count = $conn->prepare("SELECT COUNT(*) AS total FROM players WHERE team_id = ?");
$stmt_count->bind_param("i", $team_id);
$stmt_count->execute();
$result = $stmt_count->get_result();
$pemain_count = $result->fetch_assoc()['total'];
$stmt_count->close();



// Ambil alert dari session, lalu hapus supaya tidak muncul lagi setelah reload
$alert = isset($_SESSION['alert']) ? $_SESSION['alert'] : '';
unset($_SESSION['alert']);

// Fungsi untuk validasi input pemain
function validasiPemain($name, $position, $back_number, $valid_positions) {
    $errors = [];

    if (trim($name) === '') {
        $errors[] = "Nama pemain tidak boleh kosong.";
    }

    if (!in_array($position, $valid_positions)) {
        $errors[] = "Posisi pemain tidak valid.";
    }

    if (!is_numeric($back_number) || $back_number <= 0) {
        $errors[] = "Nomor punggung harus angka positif.";
    }

    return $errors;
}

// Tambah data pemain
if (isset($_POST['tambah'])) {
    $name = trim($_POST['name']);
    $position = $_POST['position'];
    $back_number = intval($_POST['back_number']);

    // Validasi
    $errors = validasiPemain($name, $position, $back_number, $valid_positions);

    // Cek nomor punggung unik di tim
    if (empty($errors)) {
        $stmt_check = $conn->prepare("SELECT COUNT(*) FROM players WHERE team_id = ? AND back_number = ?");
        $stmt_check->bind_param("ii", $team_id, $back_number);
        $stmt_check->execute();
        $stmt_check->bind_result($count);
        $stmt_check->fetch();
        $stmt_check->close();

        if ($count > 0) {
            $errors[] = "Nomor punggung $back_number sudah dipakai pemain lain di tim ini!";
        }
    }

    if (!empty($errors)) {
        $_SESSION['alert'] = implode("<br>", $errors);
    } else {
        $stmt = $conn->prepare("INSERT INTO players (name, position, back_number, team_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssii", $name, $position, $back_number, $team_id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['alert'] = "Pemain berhasil ditambahkan!";
        unset($_SESSION['alert_set']);
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Update data pemain
if (isset($_POST['update'])) {
    $id = intval($_POST['id_edit']);
    $name = trim($_POST['name']);
    $position = $_POST['position'];
    $back_number = intval($_POST['back_number']);

    // Validasi
    $errors = validasiPemain($name, $position, $back_number, $valid_positions);

    if (empty($errors)) {
        // Cek nomor punggung unik kecuali untuk pemain yang sedang diedit
        $stmt_check = $conn->prepare("SELECT COUNT(*) FROM players WHERE team_id = ? AND back_number = ? AND id <> ?");
        $stmt_check->bind_param("iii", $team_id, $back_number, $id);
        $stmt_check->execute();
        $stmt_check->bind_result($count);
        $stmt_check->fetch();
        $stmt_check->close();

        if ($count > 0) {
            $errors[] = "Nomor punggung $back_number sudah dipakai pemain lain di tim ini!";
        }
    }

    if (!empty($errors)) {
        $_SESSION['alert'] = implode("<br>", $errors);
    } else {
        $stmt = $conn->prepare("UPDATE players SET name=?, position=?, back_number=? WHERE id=? AND team_id=?");
        $stmt->bind_param("sssii", $name, $position, $back_number, $id, $team_id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['alert'] = "Data berhasil diedit!";
        unset($_SESSION['alert_set']);
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Hapus data pemain
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    $stmt = $conn->prepare("DELETE FROM players WHERE id=? AND team_id=?");
    $stmt->bind_param("ii", $id, $team_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['alert'] = "Pemain berhasil dihapus!";
    unset($_SESSION['alert_set']);

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Ambil data untuk edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $id_edit = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM players WHERE id=? AND team_id=?");
    $stmt->bind_param("ii", $id_edit, $team_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_data = $result->fetch_assoc();
    $stmt->close();
}


// Proses selesai
if (isset($_POST['selesai'])) {
    if ($pemain_count < 15) {
        $_SESSION['alert'] = "Pemain belum lengkap. Harus ada minimal 15 pemain!";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    // Reset session team_id karena sudah selesai input pemain
    unset($_SESSION['team_id']);
    header("Location: dashboard_admin.php");
    exit();
}
// Hitung ulang jumlah pemain setelah aksi tambah/edit/hapus
$stmt_count = $conn->prepare("SELECT COUNT(*) AS total FROM players WHERE team_id = ?");
$stmt_count->bind_param("i", $team_id);
$stmt_count->execute();
$result = $stmt_count->get_result();
$pemain_count = $result->fetch_assoc()['total'];
$stmt_count->close();

// Set alert jika pemain sudah lengkap (minimal 15) dan belum pernah diset alert sebelumnya
if ($pemain_count >= 15 && !isset($_SESSION['alert_set'])) {
    $_SESSION['alert'] = "Pemain sudah lengkap minimal 15 orang! Kamu bisa klik tombol 'Selesai' untuk melanjutkan.";
    $_SESSION['alert_set'] = true;
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Input Pemain</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow-lg">
        <div class="card-header bg-success text-white text-center">
            <h4>👥 Tambah Pemain</h4>
        </div>
        <div class="card-body">

            <!-- Form tambah/edit pemain -->
            <form method="POST" class="row g-3 mb-4">
                <input type="hidden" name="id_edit" value="<?= htmlspecialchars($edit_data['id'] ?? '') ?>">

                <div class="col-md-4">
                    <label class="form-label">Nama Pemain</label>
                    <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($edit_data['name'] ?? '') ?>" />
                </div>

                <div class="col-md-4">
                    <label class="form-label">Posisi</label>
                    <select name="position" class="form-select" required>
                        <option value="">-</option>
                        <?php
                        foreach ($valid_positions as $pos) {
                            $selected = ($edit_data && $edit_data['position'] === $pos) ? 'selected' : '';
                            echo "<option value='" . htmlspecialchars($pos) . "' $selected>" . htmlspecialchars($pos) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">No Punggung</label>
                    <input type="number" name="back_number" class="form-control" required min="1" value="<?= htmlspecialchars($edit_data['back_number'] ?? '') ?>" />
                </div>

                <div class="col-12 text-end">
                    <?php if ($edit_data): ?>
                        <button type="submit" name="update" class="btn btn-warning">Update</button>
                        <a href="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" class="btn btn-secondary">Batal</a>
                    <?php else: ?>
                        <button type="submit" name="tambah" class="btn btn-primary">Tambah</button>
                    <?php endif; ?>
                </div>
            </form>

            <!-- Daftar pemain -->
            <h5>📋 Daftar Pemain</h5>
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Posisi</th>
                    <th>No Punggung</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $no = 1;
                $stmt = $conn->prepare("SELECT * FROM players WHERE team_id = ?");
                $stmt->bind_param("i", $team_id);
                $stmt->execute();
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                        <td>" . $no++ . "</td>
                        <td>" . htmlspecialchars($row['name']) . "</td>
                        <td>" . htmlspecialchars($row['position']) . "</td>
                        <td>" . htmlspecialchars($row['back_number']) . "</td>
                        <td>
                            <a href='?edit=" . intval($row['id']) . "' class='btn btn-sm btn-warning'>Edit</a>
                            <a href='?delete=" . intval($row['id']) . "' class='btn btn-sm btn-danger' onclick='return confirm(\"Yakin ingin menghapus pemain ini?\")'>Hapus</a>
                        </td>
                    </tr>";
                }
                $stmt->close();
                ?>
                </tbody>
            </table>

            <!-- Tombol selesai -->
            <?php if ($pemain_count >= 15): ?>
                <form method="POST" class="mt-4 text-center">
                    <button type="submit" name="selesai" class="btn btn-success w-50">Selesai & Kembali</button>
                </form>
            <?php else: ?>
                <div class="alert alert-warning mt-4 text-center">
                    Pemain belum lengkap. Harus ada minimal 15 pemain!
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- Modal Alert -->
<?php if ($alert): ?>
<script>
    alert(`<?= addslashes($alert) ?>`);
</script>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
