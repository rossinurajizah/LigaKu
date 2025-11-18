<?php
// Tentukan content type sebagai JSON
header('Content-Type: application/json');

#include_once('../session_check.php'); 
include 'koneksi.php'; 

// =================================================================
// FUNGSI UTILITY UNTUK RESPON JSON
// =================================================================
function sendJsonResponse($status, $message, $data = null, $http_code = 200) {
    http_response_code($http_code);
    $response = [
        'status' => $status,
        'message' => $message
    ];
    if ($data !== null) {
        $response['detail'] = $data; // Menggunakan 'detail'
    }
    echo json_encode($response);
    exit();
}

// =================================================================
// FUNGSI 1: MEMBUAT JADWAL KNOCKOUT (TETAP SAMA)
// =================================================================
function generateKnockoutSchedule($conn) {
    
    // 1. Cek Kualifikasi
    $top_teams_query = "SELECT team_id FROM standings 
                          ORDER BY points DESC, goal_diff DESC, goals_for DESC 
                          LIMIT 4";
    $result = $conn->query($top_teams_query);
    $qualifiers = [];
    while ($row = $result->fetch_assoc()) {
        $qualifiers[] = $row['team_id'];
    }

    if (count($qualifiers) < 4) {
        return ['status' => 'error', 'message' => "Gagal: Minimal 4 tim harus terdaftar di klasemen untuk memulai babak gugur.", 'http_code' => 400];
    }

    $team1 = $qualifiers[0]; $team2 = $qualifiers[1]; $team3 = $qualifiers[2]; $team4 = $qualifiers[3]; 
    
    // 2. Tentukan Tanggal Semifinal dan Final
    $last_match = $conn->query("SELECT MAX(match_date) as last_date FROM schedules WHERE round_type='Penyisihan' AND status='finished'")->fetch_assoc();
    $start_date_str = $last_match['last_date'] ? $last_match['last_date'] : (new DateTime('+1 week'))->format('Y-m-d');
    
    $semi_final_date = (new DateTime($start_date_str))->modify('+7 days')->format('Y-m-d'); 
    $final_date = (new DateTime($semi_final_date))->modify('+3 days')->format('Y-m-d');
    $time = "19:00";
    $location_final = "Stadion Utama"; 

    // 3. Masukkan SLOT FINAL
    $conn->query("INSERT INTO schedules 
                  (match_date, time, round_type, team_home_id, team_away_id, location, status) 
                  VALUES ('$final_date', '$time', 'Final', NULL, NULL, '$location_final', 'pending_qualification')");
    $final_match_id = $conn->insert_id; 

    // 4. Masukkan Jadwal Semifinal 1
    $stmt_sf1 = $conn->prepare("INSERT INTO schedules 
                                 (match_date, time, round_type, team_home_id, team_away_id, next_match_id, location, status) 
                                 VALUES (?, ?, 'SemiFinal', ?, ?, ?, ?, 'pending')");
    $stmt_sf1->bind_param("ssiiis", $semi_final_date, $time, $team1, $team4, $final_match_id, $location_final);
    $stmt_sf1->execute();
    $stmt_sf1->close();
    
    // 5. Masukkan Jadwal Semifinal 2
    $stmt_sf2 = $conn->prepare("INSERT INTO schedules 
                                 (match_date, time, round_type, team_home_id, team_away_id, next_match_id, location, status) 
                                 VALUES (?, ?, 'SemiFinal', ?, ?, ?, ?, 'pending')");
    $stmt_sf2->bind_param("ssiiis", $semi_final_date, $time, $team2, $team3, $final_match_id, $location_final);
    $stmt_sf2->execute();
    $stmt_sf2->close();
    
    return ['status' => 'success', 'message' => "Jadwal Semifinal dan Final berhasil dibuat secara otomatis!", 'http_code' => 201];
}


// =================================================================
// LOGIKA UTAMA PENJADWALAN (DENGAN STATISTIK DAN VALIDASI N GENAP)
// =================================================================
if (isset($_GET['buat_jadwal'])) {
    
    $stats = [
        'dibuat' => 0,
        'duplikat_dilompati' => 0,
        'bentrok_dilompati' => 0,
        'total_tim_diproses' => 0
    ];

    // START LOGIKA 1: Cek Pembayaran & Ambil Tim
    $teams_query = "
        SELECT t.id, t.team_name, CONCAT('Stadion ', t.team_name) AS location 
        FROM teams t
        JOIN payments p ON t.id = p.team_id
        WHERE p.payment_status = 'Sudah Bayar'
    ";
    $teams_result = $conn->query($teams_query); 
    $team_list = [];
    while ($team = $teams_result->fetch_assoc()) {
        $team_list[] = $team;
    }

    $N = count($team_list);
    $stats['total_tim_diproses'] = $N; 

    // Hitung total pertandingan yang seharusnya
    $total_possible_matches = ($N > 1) ? ($N * ($N - 1) / 2) : 0;
    
    // TCW-004: JALUR VALIDASI INPUT < 2 TIM
    if ($N < 2) {
        $stats['tim_ditemukan'] = $N;
        sendJsonResponse('gagal', 'Input tim tidak memadai. Minimal dibutuhkan 2 tim untuk membuat jadwal.', $stats, 400);
    }
    
    // TCW-003: JALUR GAGAL N GANJIL
    if ($N % 2 != 0) { 
        $stats['tim_ditemukan'] = $N;
        sendJsonResponse('gagal', 'Gagal membuat jadwal. Algoritma ini hanya mendukung jumlah tim genap.', $stats, 400);
    }
    
    // 🛑 CEK GLOBAL DUPLIKAT: JADWAL PENYISIHAN (JALUR TCW-002)
    $existing_schedule_check = $conn->query("SELECT COUNT(*) FROM schedules WHERE round_type='Penyisihan'");
    $count_existing = $existing_schedule_check->fetch_row()[0];
    
    if ($count_existing > 0) {
        $stats['duplikat_dilompati'] = $total_possible_matches; 
        sendJsonResponse('selesai', "Tidak ada jadwal baru dibuat, $total_possible_matches pasangan tim sudah ada.", $stats, 200); 
    }
    
    // ALGORITMA ROTASI UNTUK N GENAP
    $rounds = $N - 1; 
    $teams_to_rotate = $team_list; 
    $N_rot = $N; 
    $matchups_per_round = $N / 2;
    
    $fixed_team = array_shift($teams_to_rotate); 
    
    $last_scheduled_teams = []; 
    $first_match_date = new DateTime('+1 week');
    $interval = new DateInterval('P3D');
    $rest_days = 2; 

    for ($round = 1; $round <= $rounds; $round++) {
        
        for ($match = 0; $match < $matchups_per_round; $match++) {
            
            if ($match == 0) {
                $home_team_data = $fixed_team;
                $away_team_data = $teams_to_rotate[$N_rot - 2];
            } else {
                $home_team_data = $teams_to_rotate[$match - 1];
                $away_team_data = $teams_to_rotate[$N_rot - 1 - $match];
            }
            
            $home = $home_team_data['id']; 
            $away = $away_team_data['id']; 
            $location = $home_team_data['location']; 
            
            // --- Cek Konflik (Bentrok) ---
            
            // Logika is_sequential_conflict (yang menyebabkan bug 21/28) DIHILANGKAN
            
            $match_date_dt = clone $first_match_date;
            $found_date = false;

            while (!$found_date) {
                $is_conflict = false;
                for ($d = 0; $d <= $rest_days; $d++) { 
                    $check_date_dt = clone $match_date_dt;
                    if ($d > 0) { $check_date_dt->modify("-$d day"); }
                    $check_date = $check_date_dt->format('Y-m-d');

                    $stmt_gap = $conn->prepare("
                        SELECT COUNT(*) FROM schedules 
                        WHERE match_date = ? 
                        AND (team_home_id = ? OR team_away_id = ?)
                    ");
                    $stmt_gap->bind_param("sii", $check_date, $home, $away);
                    $stmt_gap->execute();
                    $stmt_gap->bind_result($count_conflict);
                    $stmt_gap->fetch();
                    $stmt_gap->close();

                    if ($count_conflict > 0) {
                        $is_conflict = true;
                        break; 
                    }
                }
                
                if ($is_conflict) {
                    $match_date_dt->add(new DateInterval('P1D'));
                } else {
                    $found_date = true; 
                }
            }
            
            $date = $match_date_dt->format('Y-m-d');
            $first_match_date = $match_date_dt; 
            $first_match_date->add($interval); 
            
            $time = "15:00";

            // INSERT schedule 
            $stmt = $conn->prepare("INSERT INTO schedules (match_date, time, team_home_id, team_away_id, location, round_type, status) 
                                     VALUES (?, ?, ?, ?, ?, 'Penyisihan', 'pending')");
            $stmt->bind_param("ssiis", $date, $time, $home, $away, $location); 
            $stmt->execute();
            $stmt->close();
            
            $stats['dibuat']++; 
            $last_scheduled_teams = [$home, $away];
        }
        
        // Rotasi Tim untuk Putaran Berikutnya
        $temp = array_pop($teams_to_rotate);
        array_unshift($teams_to_rotate, $temp);
    }
    
    // Respons Sukses TCW-001
    $message_success = "{$stats['dibuat']} jadwal baru berhasil dibuat.";
    sendJsonResponse('selesai', $message_success, $stats, 201); 
} 
// =================================================================
// TANGANI POST REQUEST & TAMPILKAN JADWAL (DEFAULT RESPONSE GET)
// =================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id'])) {
        $id = $_POST['id'];
        $message = '';
        $http_code = 200;

        if (isset($_POST['update']) && isset($_POST['match_date'], $_POST['time'], $_POST['location'])) {
            $date = $_POST['match_date']; $time = $_POST['time']; $location = $_POST['location'];
            $stmt = $conn->prepare("UPDATE schedules SET match_date = ?, time = ?, location = ? WHERE id = ?");
            $stmt->bind_param("sssi", $date, $time, $location, $id);
            $stmt->execute(); $stmt->close();
            $message = 'Jadwal berhasil diperbarui!';

        } elseif (isset($_POST['set_approved'])) {
            $stmt = $conn->prepare("UPDATE schedules SET status = 'approved' WHERE id = ?");
            $stmt->bind_param("i", $id); $stmt->execute(); $stmt->close();
            $message = 'Jadwal berhasil disetujui!';

        } elseif (isset($_POST['set_pending'])) {
            $stmt = $conn->prepare("UPDATE schedules SET status = 'pending' WHERE id = ?");
            $stmt->bind_param("i", $id); $stmt->execute(); $stmt->close();
            $message = 'Jadwal berhasil diubah menjadi pending!';

        } elseif (isset($_POST['selesai'])) {
            $stmt = $conn->prepare("UPDATE schedules SET status = 'finished' WHERE id = ?");
            $stmt->bind_param("i", $id); $stmt->execute(); 
            
            $total_rr = $conn->query("SELECT COUNT(*) FROM schedules WHERE round_type='Penyisihan'")->fetch_row()[0];
            $finished_rr = $conn->query("SELECT COUNT(*) FROM schedules WHERE round_type='Penyisihan' AND status='finished'")->fetch_row()[0];
            
            $message = 'Status pertandingan berhasil ditandai selesai!';
            
            if ($total_rr > 0 && $total_rr == $finished_rr) { 
                $knockout_exists = $conn->query("SELECT COUNT(*) FROM schedules WHERE round_type='SemiFinal' OR round_type='Final'")->fetch_row()[0];
                
                if ($knockout_exists == 0) {
                    $auto_response = generateKnockoutSchedule($conn);
                    $message = $auto_response['message'];
                    $http_code = $auto_response['http_code'];
                } else {
                    $message .= " Namun, babak gugur sudah pernah dibuat.";
                }
            }
            $stmt->close();
        }

        if (isset($message)) {
             sendJsonResponse('success', $message, null, $http_code);
        } else {
             sendJsonResponse('error', 'Aksi tidak valid atau id tidak ditemukan.', null, 400);
        }
    }
}


// --- TAMPILKAN JADWAL (DEFAULT RESPONSE GET) ---
$filter_date = $_GET['filter_date'] ?? '';
$search = $_GET['search'] ?? '';

$where = "WHERE 1=1";
if ($filter_date) {
    $where .= " AND s.match_date = '" . $conn->real_escape_string($filter_date) . "'";
}
if ($search) {
    $s = $conn->real_escape_string($search);
    $where .= " AND (th.team_name LIKE '%$s%' OR ta.team_name LIKE '%$s%')";
}

$query = "
    SELECT s.id, th.team_name AS team_home, ta.team_name AS team_away, s.match_date, s.time, s.location, s.status, s.round_type
    FROM schedules s
    LEFT JOIN teams th ON s.team_home_id = th.id
    LEFT JOIN teams ta ON s.team_away_id = ta.id
    $where
    ORDER BY s.match_date ASC
";

$result = $conn->query($query);
$schedules = [];
while ($row = $result->fetch_assoc()) {
    $schedules[] = [
        'id' => $row['id'],
        'team_home' => $row['team_home'] ?? 'N/A',
        'team_away' => $row['team_away'] ?? 'N/A',
        'match_date' => $row['match_date'],
        'time' => $row['time'],
        'location' => $row['location'],
        'round_type' => $row['round_type'],
        'status' => $row['status']
    ];
}

// Respon Default (GET request)
$total_matches = count($schedules);
sendJsonResponse('success', "$total_matches pertandingan berhasil dibuat.", ['matches' => $schedules, 'total' => $total_matches]); 
?>

<?php
/*
======================================================================================================
BLOK HTML, CSS, DAN JAVASCRIPT POPUP LAMA (DIKOMENTARI)
======================================================================================================

// ... (Semua HTML/CSS/JS lama tetap dikomentari di sini) ...

*/
?>