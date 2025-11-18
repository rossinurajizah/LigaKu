<?php
include_once('../session_check.php');
include 'koneksi.php';

// VALIDASI KETAT: Cek apakah sudah isi nama tim dari halaman daftar_tim.php
$temp_team_name = $_SESSION['temp_team_name'] ?? null;

if (!$temp_team_name || trim($temp_team_name) === '') {
    echo "
    <script>
        alert('ERROR: Anda harus mendaftar tim terlebih dahulu!');
        window.location.href = 'daftar_tim.php';
    </script>
    ";
    exit();
}

// VALIDASI TAMBAHAN: Cek apakah nama tim yang disimpan di session sudah ada di database
// Jika sudah ada, berarti user mengakses ulang padahal sudah pernah submit
$checkSql = "SELECT team_name FROM teams WHERE LOWER(team_name) = LOWER(?)";
$stmt_check = $conn->prepare($checkSql);
$stmt_check->bind_param("s", $temp_team_name);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    // Tim sudah ada di database, berarti sudah pernah disimpan
    unset($_SESSION['temp_team_name']);
    unset($_SESSION['temp_players']);
    echo "
    <script>
        alert('ERROR: Tim dengan nama ini sudah tersimpan di database! Silakan daftar dengan nama tim lain.');
        window.location.href = 'daftar_tim.php';
    </script>
    ";
    exit();
}
$stmt_check->close();

// Inisialisasi array pemain di session jika belum ada
if (!isset($_SESSION['temp_players'])) {
    $_SESSION['temp_players'] = [];
}

// Daftar posisi yang valid
$valid_positions = ['Goalkeeper', 'Defender', 'Midfielder', 'Forward'];

// Ambil alert dari session
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

// Tambah data pemain ke SESSION
if (isset($_POST['tambah'])) {
    $pemain_count = count($_SESSION['temp_players']);
    
    // Validasi jumlah maksimum sebelum menambah
    if ($pemain_count >= 20) {
        $_SESSION['alert'] = "Jumlah pemain maksimal (20 orang) sudah tercapai! Tidak bisa menambah pemain lagi.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    
    $name = trim($_POST['name']);
    $position = $_POST['position'];
    $back_number = intval($_POST['back_number']);

    // Validasi
    $errors = validasiPemain($name, $position, $back_number, $valid_positions);

    // Cek nomor punggung unik
    if (empty($errors)) {
        foreach ($_SESSION['temp_players'] as $player) {
            if ($player['back_number'] == $back_number) {
                $errors[] = "Nomor punggung $back_number sudah dipakai pemain lain!";
                break;
            }
        }
    }

    if (!empty($errors)) {
        $_SESSION['alert'] = implode("<br>", $errors);
    } else {
        // Tambahkan ke session
        $_SESSION['temp_players'][] = [
            'id' => count($_SESSION['temp_players']) + 1,
            'name' => $name,
            'position' => $position,
            'back_number' => $back_number
        ];

        $_SESSION['alert'] = "Pemain berhasil ditambahkan!";
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Update data pemain di SESSION
if (isset($_POST['update'])) {
    $id = intval($_POST['id_edit']);
    $name = trim($_POST['name']);
    $position = $_POST['position'];
    $back_number = intval($_POST['back_number']);

    // Validasi
    $errors = validasiPemain($name, $position, $back_number, $valid_positions);

    if (empty($errors)) {
        // Cek nomor punggung unik kecuali untuk pemain yang sedang diedit
        foreach ($_SESSION['temp_players'] as $player) {
            if ($player['back_number'] == $back_number && $player['id'] != $id) {
                $errors[] = "Nomor punggung $back_number sudah dipakai pemain lain!";
                break;
            }
        }
    }

    if (!empty($errors)) {
        $_SESSION['alert'] = implode("<br>", $errors);
    } else {
        // Update di session
        foreach ($_SESSION['temp_players'] as &$player) {
            if ($player['id'] == $id) {
                $player['name'] = $name;
                $player['position'] = $position;
                $player['back_number'] = $back_number;
                break;
            }
        }

        $_SESSION['alert'] = "Data berhasil diedit!";
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Hapus data pemain dari SESSION
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    // Hapus dari session
    $_SESSION['temp_players'] = array_filter($_SESSION['temp_players'], function($player) use ($id) {
        return $player['id'] != $id;
    });

    // Reindex array
    $_SESSION['temp_players'] = array_values($_SESSION['temp_players']);

    $_SESSION['alert'] = "Pemain berhasil dihapus!";

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Ambil data untuk edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $id_edit = intval($_GET['edit']);
    foreach ($_SESSION['temp_players'] as $player) {
        if ($player['id'] == $id_edit) {
            $edit_data = $player;
            break;
        }
    }
}

// Proses selesai - SIMPAN KE DATABASE
if (isset($_POST['selesai'])) {
    $pemain_count = count($_SESSION['temp_players']);

    // Validasi jumlah pemain minimal dan maksimal
    if ($pemain_count < 15) {
        $_SESSION['alert'] = "Pemain belum lengkap. Harus ada minimal 15 pemain!";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } elseif ($pemain_count >20) {
        $_SESSION['alert'] = "Jumlah pemain melebihi batas! Maksimal 20 pemain per tim.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    // Cek lagi apakah nama tim sudah ada (double check sebelum insert)
    $checkSql = "SELECT team_name FROM teams WHERE LOWER(team_name) = LOWER(?)";
    $stmt_check_final = $conn->prepare($checkSql);
    $stmt_check_final->bind_param("s", $temp_team_name);
    $stmt_check_final->execute();
    $result_check_final = $stmt_check_final->get_result();

    if ($result_check_final->num_rows > 0) {
        unset($_SESSION['temp_team_name']);
        unset($_SESSION['temp_players']);
        echo "
        <script>
            alert('ERROR: Nama tim sudah terdaftar! Silakan daftar dengan nama tim lain.');
            window.location.href = 'daftar_tim.php';
        </script>
        ";
        exit();
    }
    $stmt_check_final->close();

    // Mulai transaction
    $conn->begin_transaction();

    try {
        // 1. Insert tim ke database
        $stmt_team = $conn->prepare("INSERT INTO teams (team_name) VALUES (?)");
        $stmt_team->bind_param("s", $temp_team_name);
        $stmt_team->execute();
        $team_id = $conn->insert_id;
        $stmt_team->close();

        // 2. Insert semua pemain ke database
        $stmt_player = $conn->prepare("INSERT INTO players (name, position, back_number, team_id) VALUES (?, ?, ?, ?)");
        
        foreach ($_SESSION['temp_players'] as $player) {
            $stmt_player->bind_param("ssii", $player['name'], $player['position'], $player['back_number'], $team_id);
            $stmt_player->execute();
        }
        $stmt_player->close();

        // 3. Insert data pembayaran otomatis
        $registration_date = date('Y-m-d');
        $payment_status = 'Belum Bayar';
        $payment_proof = '';

        $stmt_payment = $conn->prepare("INSERT INTO payments (team_id, registration_date, payment_status, payment_proof) VALUES (?, ?, ?, ?)");
        $stmt_payment->bind_param("isss", $team_id, $registration_date, $payment_status, $payment_proof);
        $stmt_payment->execute();
        $stmt_payment->close();

        // Commit transaction
        $conn->commit();

        // Hapus session temporary
        unset($_SESSION['temp_team_name']);
        unset($_SESSION['temp_players']);
        unset($_SESSION['alert']);
        unset($_SESSION['alert_set']);

        echo "
        <script>
            alert('Tim \"$temp_team_name\" dan $pemain_count pemain berhasil disimpan ke database!');
            window.location.href = 'dashboard_admin.php';
        </script>
        ";
        exit();

    } catch (Exception $e) {
        // Rollback jika ada error
        $conn->rollback();
        $_SESSION['alert'] = "Gagal menyimpan data: " . $e->getMessage();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Hitung jumlah pemain
$pemain_count = count($_SESSION['temp_players']);

// Set alert jika pemain sudah lengkap
if ($pemain_count >= 15 && !isset($_SESSION['alert_set'])) {
    $_SESSION['alert'] = "Pemain sudah lengkap minimal 15 orang! Kamu bisa klik tombol 'Selesai' untuk menyimpan.";
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
    <style>
        body {
            min-height: 100vh;
            padding: 20px 0;
        }
        .card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
        }
        .card-header {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            border: none;
            padding: 20px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #9ecdffff 0%, #00f2fe 100%);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #00f2fe 0%, #4facfe 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(79, 172, 254, 0.4);
        }
        .btn-warning {
            background: linear-gradient(135deg, #ffeaa7 0%, #ffffff 100%);
            border: none;
            color: #2d3436;
        }
        .btn-warning:hover {
            background: linear-gradient(135deg, #fdcb6e 0%, #ffeaa7 100%);
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
        .btn-secondary {
            background: linear-gradient(135deg, #a8b8d8 0%, #d4dff0 100%);
            border: none;
            color: #333;
        }
        .btn-secondary:hover {
            background: linear-gradient(135deg, #d4dff0 0%, #a8b8d8 100%);
            transform: translateY(-2px);
        }
        .alert-warning {
            background: linear-gradient(135deg, #fff3cd 0%, #ffe8a1 100%);
            border: none;
            color: #856404;
        }
        .table {
            background: white;
        }
        .table thead {
            background: linear-gradient(135deg, #ffffffff 0%, #bbdefb 100%);
        }
        .team-info {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <div class="card shadow-lg">
        <div class="card-header text-white text-center">
            <h4>👥 Tambah Pemain Tim <?= htmlspecialchars($temp_team_name) ?></h4>
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
                        <?php if ($pemain_count >= 20): ?>
                            <button type="button" class="btn btn-primary" disabled title="Maksimal 20 pemain sudah tercapai">Tambah</button>
                        <?php else: ?>
                            <button type="submit" name="tambah" class="btn btn-primary">Tambah</button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </form>

            <!-- Daftar pemain -->
            <h5>📋 Daftar Pemain (Belum Tersimpan ke Database)</h5>
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
                if (empty($_SESSION['temp_players'])) {
                    echo "<tr><td colspan='5' class='text-center'>Belum ada pemain. Silakan tambah pemain terlebih dahulu.</td></tr>";
                } else {
                    $no = 1;
                    foreach ($_SESSION['temp_players'] as $player) {
                        echo "<tr>
                            <td>" . $no++ . "</td>
                            <td>" . htmlspecialchars($player['name']) . "</td>
                            <td>" . htmlspecialchars($player['position']) . "</td>
                            <td>" . htmlspecialchars($player['back_number']) . "</td>
                            <td>
                                <a href='?edit=" . intval($player['id']) . "' class='btn btn-sm btn-warning'>Edit</a>
                                <a href='?delete=" . intval($player['id']) . "' class='btn btn-sm btn-danger' onclick='return confirm(\"Yakin ingin menghapus pemain ini?\")'>Hapus</a>
                            </td>
                        </tr>";
                    }
                }
                ?>
                </tbody>
            </table>

            <!-- Tombol selesai dan kembali -->
            <div class="d-flex justify-content-between mt-4">
                <a href="daftar_tim.php" class="btn btn-secondary">
                     Kembali
                </a>
                <?php if ($pemain_count >= 15 && $pemain_count <= 20): ?>
                    <form method="POST" class="mb-0" onsubmit="return confirm('Apakah Anda yakin ingin menyimpan tim dan semua pemain ke database?');">
                        <button type="submit" name="selesai" class="btn btn-primary">
                            Simpan
                        </button>
                    </form>
                <?php else: ?>
                    <button class="btn btn-primary" disabled title="<?= $pemain_count < 15 ? 'Minimal 15 pemain' : 'Maksimal 20 pemain' ?>">
                        Simpan
                    </button>
                <?php endif; ?>
            </div>

            <?php if ($pemain_count < 15): ?>
                <div class="alert alert-warning mt-3 text-center">
                    ⚠️ Pemain belum lengkap. Harus ada minimal 15 pemain! (Saat ini: <?= $pemain_count ?>/15)
                </div>
            <?php elseif ($pemain_count > 20): ?>
                <div class="alert alert-warning mt-3 text-center">
                    ⚠️ Jumlah pemain melebihi batas maksimal 20 orang! (Saat ini: <?= $pemain_count ?>/20)
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