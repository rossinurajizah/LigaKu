
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard Liga Sepak Bola</title>
  <link rel="stylesheet" href="/Ligaku/pengunjung/style.css"> 
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<?php
$conn = new mysqli("localhost", "root", "", "ligaku");
?>
  <!-- Sidebar -->
  <div class="sidebar d-flex flex-column">
    <h4 class="text-center text-white mb-4">⚽ Ligaku</h4>
  <!-- Profile Icon below the title -->
  <div class="profile-container text-center">
  <span class="profile-icon" onclick="toggleProfileMenu()">👤</span>
  <div class="profile-menu" id="profileMenu">
    <a href="#login" onclick="window.location.href='/Ligaku/admin/login.php'">Login</a>
    <a href="#register" onclick="window.location.href='/Ligaku/admin/register.php'">Daftar</a>
  </div>
</div>

    <nav class="nav flex-column">
      <a class="nav-link" href="#beranda">🏠 Beranda</a>
      <a class="nav-link" href="#jadwal">📅 Jadwal</a>
      <a class="nav-link" href="#hasil">⚽ Hasil Pertandingan</a>
      <a class="nav-link" href="#lineup">📋 Line Up</a>
      <a class="nav-link" href="#motm">🏅 MOTM</a>
      <a class="nav-link" href="#topskor">🥇 Top Skor</a>
      <a class="nav-link" href="#pemain terbaik">🥇 Pemain Terbaik</a>
      <a class="nav-link" href="#klasemen">📊 Klasemen</a>

    </nav>
  </div>

  <!-- Main Content -->
  <div class="content">

   <!-- Beranda -->
<section id="beranda" class="mb-5">
  <div class="p-5 mb-4 rounded-3 gradient-blue-white" >
    <div class="container-fluid py-5 text-center">
      <h1 class="display-4 fw-bold mb-3">⚽ Selamat Datang di Ligaku!</h1>
      <p class="fs-5 mb-4">Pantau hasil pertandingan, klasmen, susunan pemain, top skor, pemain terbaik, dan berita terbaru tentang liga favoritmu di satu tempat!</p>
      <a href="#hasil" class="btn btn-light btn-lg rounded-pill px-4 py-2 fw-semibold">Lihat Hasil Pertandingan</a>
    </div>
  </div>
</section>

<!-- Jadwal Pertandingan -->
  <section id="jadwal" class="mb-5">
    <h2>📅 Jadwal Pertandingan</h2>
    <table class="table mt-3">
      <thead>
        <tr>
          <th>Tanggal</th>
          <th>Waktu</th>
          <th>Tim Kandang</th>
          <th>VS</th>
          <th>Tim Tandang</th>
          <th>Lokasi</th>
        </tr>
      </thead>
      <tbody>
        <?php
        // Query untuk mengambil data pertandingan yang akan datang
        $jadwal = $conn->query("SELECT * FROM schedules WHERE match_date > NOW() AND status = 'approved' ORDER BY match_date ASC LIMIT 5");

        // Menampilkan hasil
        while ($row = $jadwal->fetch_assoc()) {
          // Mendapatkan nama tim kandang
          $team_home = $conn->query("SELECT team_name FROM teams WHERE id={$row['team_home_id']}")->fetch_assoc()['team_name'];
          // Mendapatkan nama tim tandang
          $team_away = $conn->query("SELECT team_name FROM teams WHERE id={$row['team_away_id']}")->fetch_assoc()['team_name'];

          echo "<tr>
                  <td>" . $row['match_date'] . "</td>
                  <td>" . $row['time'] . "</td>
                  <td>" . htmlspecialchars($team_home) . "</td>
                  <td>VS</td>
                  <td>" . htmlspecialchars($team_away) . "</td>
                  <td>" . htmlspecialchars($row['location']) . "</td>
                </tr>";
        }
        ?>
      </tbody>
    </table>
  </section>

    
<section id="lineup" class="mb-5">
    <h2>📋 Line Up</h2>

    <?php
    // Fungsi ambil lineup semua pemain (dideklarasikan di luar loop)
    function getLineup($conn, $match_id, $team_id) {
        $stmt = $conn->prepare("
            SELECT p.name, p.position, p.back_number, l.is_starting
            FROM lineups l
            JOIN players p ON l.player_id = p.id
            WHERE l.match_id = ? AND p.team_id = ?
        ");
        $stmt->bind_param("ii", $match_id, $team_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $list = [];
        while ($row = $result->fetch_assoc()) {
            $list[] = $row;
        }
        $stmt->close();
        return $list;
    }

    // Fungsi untuk urutkan pemain: starting dulu, cadangan belakangan
    function sortPlayersByStartingStatus($players) {
        usort($players, function($a, $b) {
            return $b['is_starting'] <=> $a['is_starting'];
        });
        return $players;
    }

    // Ambil 1 match_id teratas (misalnya match_id terbesar)
    $stmt = $conn->query("SELECT DISTINCT match_id FROM lineups ORDER BY match_id DESC LIMIT 1");

    if ($stmt && $stmt->num_rows > 0) {
        $row = $stmt->fetch_assoc();
        $match_id = $row['match_id'];

        // Ambil info pertandingan berdasarkan match_id
        $match_stmt = $conn->prepare("
            SELECT s.match_date, s.time, s.location,
                  th.team_name AS home_name, ta.team_name AS away_name,
                  th.id AS home_id, ta.id AS away_id
            FROM schedules s
            JOIN teams th ON s.team_home_id = th.id
            JOIN teams ta ON s.team_away_id = ta.id
            WHERE s.status = 'approved' AND s.id = ?
            LIMIT 1
        ");
        $match_stmt->bind_param("i", $match_id);
        $match_stmt->execute();
        $match_result = $match_stmt->get_result();

        if ($match_result && $match_result->num_rows > 0) {
            $data = $match_result->fetch_assoc();
            $home_id = $data['home_id'];
            $away_id = $data['away_id'];

            $home_players = getLineup($conn, $match_id, $home_id);
            $away_players = getLineup($conn, $match_id, $away_id);

            $home_players = sortPlayersByStartingStatus($home_players);
            $away_players = sortPlayersByStartingStatus($away_players);
            ?>

            <hr>
            <h4><?= htmlspecialchars($data['home_name']) ?> vs <?= htmlspecialchars($data['away_name']) ?></h4>
            <p>📅 <?= htmlspecialchars($data['match_date']) ?> | ⏰ <?= htmlspecialchars($data['time']) ?> | 📍 <?= htmlspecialchars($data['location']) ?></p>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th colspan="4"><?= htmlspecialchars($data['home_name']) ?></th>
                        <th colspan="4"><?= htmlspecialchars($data['away_name']) ?></th>
                    </tr>
                    <tr>
                        <th>Nama</th><th>Posisi</th><th>No Punggung</th><th>Status</th>
                        <th>Nama</th><th>Posisi</th><th>No Punggung</th><th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $max_count = max(count($home_players), count($away_players));
                    for ($i = 0; $i < $max_count; $i++): ?>
                        <tr>
                            <td><?= htmlspecialchars($home_players[$i]['name'] ?? '') ?></td>
                            <td><?= htmlspecialchars($home_players[$i]['position'] ?? '') ?></td>
                            <td><?= htmlspecialchars($home_players[$i]['back_number'] ?? '') ?></td>
                            <td>
                                <?= isset($home_players[$i]) ? ($home_players[$i]['is_starting'] ? 'Inti' : 'Cadangan') : '' ?>
                            </td>

                            <td><?= htmlspecialchars($away_players[$i]['name'] ?? '') ?></td>
                            <td><?= htmlspecialchars($away_players[$i]['position'] ?? '') ?></td>
                            <td><?= htmlspecialchars($away_players[$i]['back_number'] ?? '') ?></td>
                            <td>
                                <?= isset($away_players[$i]) ? ($away_players[$i]['is_starting'] ? 'Inti' : 'Cadangan') : '' ?>
                            </td>
                        </tr>
                    <?php endfor; ?>
                </tbody>
            </table>

            <?php
        } else {
            echo "<p>⚠️ Pertandingan tidak ditemukan.</p>";
        }
        $match_stmt->close();
    } else {
        echo "<p>⚠️ Tidak ada lineup yang ditemukan di database.</p>";
    }
    ?>
</section>



<section id="hasil" class="mb-5">
  <div class="container-fluid">
    <h2 class="text-center">⚽ Hasil Pertandingan Terbaru</h2>
    
    <!-- Tabel Hasil Pertandingan -->
    <table class="table table-striped table-bordered">
      <thead>
        <tr>
          <th scope="col">No</th>
          <th scope="col">Pertandingan</th>
          <th scope="col">Tanggal</th>
          <th scope="col">Skor</th>
          <th scope="col">Hasil</th>
        </tr>
      </thead>
      <tbody>
        <?php
        // Ambil data hasil pertandingan terbaru
        $matches_result = $conn->query("SELECT m.id, m.schedule_id, m.score_a, m.score_b, m.result, s.match_date, t1.team_name AS home_team, t2.team_name AS away_team
          FROM matches m
          JOIN schedules s ON m.schedule_id = s.id
          JOIN teams t1 ON s.team_home_id = t1.id
          JOIN teams t2 ON s.team_away_id = t2.id
          ORDER BY s.match_date DESC LIMIT 5"); // Menampilkan 5 pertandingan terakhir

        if ($matches_result->num_rows > 0):
          $no = 1;
          while ($match = $matches_result->fetch_assoc()):
        ?>
            <tr>
              <td><?= $no++ ?></td>
              <td><?= htmlspecialchars($match['home_team']) ?> vs <?= htmlspecialchars($match['away_team']) ?></td>
              <td><?= htmlspecialchars($match['match_date']) ?></td>
              <td><?= htmlspecialchars($match['score_a']) ?> - <?= htmlspecialchars($match['score_b']) ?></td>
              <td class="text-success"><?= htmlspecialchars($match['result']) ?></td>
            </tr>
        <?php
          endwhile;
        else:
          echo "<tr><td colspan='5' class='text-center'>Tidak ada hasil pertandingan terbaru.</td></tr>";
        endif;
        ?>
      </tbody>
    </table>

    <!-- Tambahkan section Pelanggaran di bawahnya -->
    <h3 class="text-center text-danger mt-5 mb-4">🚩 Pelanggaran Terakhir</h3>
    <table class="table table-striped table-bordered">
      <thead>
        <tr>
          <th>No</th>
          <th>Menit</th>
          <th>Nama Pemain</th>
          <th>Tim</th>
          <th>Deskripsi Pelanggaran</th>
          <th>Kartu</th>
        </tr>
      </thead>
      <tbody>
        <?php
        // Query pelanggaran terbaru, join untuk dapat nama pemain dan tim
        $fouls_query = "
          SELECT f.minute, f.description, f.card, p.name AS player_name, t.team_name
          FROM fouls f
          JOIN players p ON f.player_id = p.id
          JOIN teams t ON f.team_id = t.id
          ORDER BY f.id DESC
          LIMIT 5"; // Batasi 5 pelanggaran terakhir

        $fouls_result = $conn->query($fouls_query);

        if ($fouls_result && $fouls_result->num_rows > 0) {
          $no = 1;
          while ($foul = $fouls_result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $no++ . "</td>";
            echo "<td>" . htmlspecialchars($foul['minute']) . "</td>";
            echo "<td>" . htmlspecialchars($foul['player_name']) . "</td>";
            echo "<td>" . htmlspecialchars($foul['team_name']) . "</td>";
            echo "<td>" . htmlspecialchars($foul['description']) . "</td>";
            echo "<td>" . htmlspecialchars($foul['card']) . "</td>";
            echo "</tr>";
          }
        } else {
          echo "<tr><td colspan='6' class='text-center'>Belum ada pelanggaran tercatat.</td></tr>";
        }
        ?>
      </tbody>
    </table>

  </div>
</section>

   <!-- Man of the Match -->
<section id="motm" class="mb-5">
  <h2>🏅 Man of the Match</h2>
  <?php
  $motm = $conn->query("
    SELECT motm.*, players.name AS player_name, teams.team_name AS player_team,
           t1.team_name AS team_home, t2.team_name AS team_away
    FROM motm
    JOIN players ON motm.player_id = players.id
    JOIN teams ON players.team_id = teams.id
    JOIN matches m ON motm.match_id = m.id
    JOIN schedules s ON m.schedule_id = s.id
    JOIN teams t1 ON s.team_home_id = t1.id
    JOIN teams t2 ON s.team_away_id = t2.id
    ORDER BY motm.id DESC
    LIMIT 1
  ");

  if ($motm && $motm->num_rows > 0) {
    $row = $motm->fetch_assoc();
  ?>
  <div class="card mt-3">
    <div class="card-body">
      <h5 class="card-title"><?= htmlspecialchars($row['player_name']) ?> <small class="text-muted">(<?= htmlspecialchars($row['player_team']) ?>)</small></h5>
      <p class="card-text">
        Penampilan luar biasa dari <strong><?= htmlspecialchars($row['player_name']) ?></strong> dalam pertandingan <strong><?= htmlspecialchars($row['team_home']) ?> vs <?= htmlspecialchars($row['team_away']) ?></strong> membantu tim tampil optimal.
      </p>
    </div>
  </div>
  <?php } else { ?>
  <p class="text-muted">Belum ada data Man of the Match.</p>
  <?php } ?>
</section>

<!-- Top Skor -->
<section id="topskor" class="mb-5">
  <h2>🥇 Top Skor Liga</h2>
  <table class="table mt-3">
    <thead>
      <tr>
        <th>Nama</th>
        <th>Tim</th>
        <th>Jumlah Gol</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $topskor = $conn->query("
        SELECT 
          players.name AS player_name,
          teams.team_name,
          COUNT(goals.id) AS total_goals  -- Menghitung jumlah gol per pemain
        FROM goals
        JOIN players ON goals.player_id = players.id
        JOIN teams ON players.team_id = teams.id
        GROUP BY goals.player_id
        ORDER BY total_goals DESC
        LIMIT 10
      ");

      if ($topskor && $topskor->num_rows > 0) {
        while ($row = $topskor->fetch_assoc()) {
          echo "<tr>
                  <td>" . htmlspecialchars($row['player_name']) . "</td>
                  <td>" . htmlspecialchars($row['team_name']) . "</td>
                  <td>" . htmlspecialchars($row['total_goals']) . "</td>
                </tr>";
        }
      } else {
        echo "<tr><td colspan='3' class='text-muted'>Belum ada data top skor.</td></tr>";
      }
      ?>
    </tbody>
  </table>
</section>


   <!-- Best Player -->
<section id="bestplayer" class="mb-5">
  <h2>🏆 Best Player</h2>
  <table class="table mt-3">
    <thead>
      <tr>
        <th>Nama</th>
        <th>Tim</th>
        <th>Jumlah MOTM</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $bestplayer = $conn->query("
        SELECT 
          players.name AS player_name,
          teams.team_name,
          COUNT(motm.id) AS motm_count
        FROM motm
        JOIN players ON motm.player_id = players.id
        JOIN teams ON players.team_id = teams.id
        GROUP BY motm.player_id
        ORDER BY motm_count DESC
        LIMIT 10
      ");

      if ($bestplayer && $bestplayer->num_rows > 0) {
        while ($row = $bestplayer->fetch_assoc()) {
          echo "<tr>
                  <td>" . htmlspecialchars($row['player_name']) . "</td>
                  <td>" . htmlspecialchars($row['team_name']) . "</td>
                  <td>" . htmlspecialchars($row['motm_count']) . "</td>
                </tr>";
        }
      } else {
        echo "<tr><td colspan='3' class='text-muted'>Belum ada data MOTM.</td></tr>";
      }
      ?>
    </tbody>
  </table>
</section>

<!-- Klasemen -->
<section id="klasemen" class="mb-5">
  <h2>📊 Klasemen Liga</h2>
  <table class="table table-bordered mt-3">
    <thead class="table-light">
      <tr>
    <th>Posisi</th>
    <th>Tim</th>
    <th>Main</th> <!-- matches_played -->
    <th>Menang</th>
    <th>Seri</th>
    <th>Kalah</th>
    <th>Gol Masuk</th>
    <th>Gol Kebobolan</th>
    <th>Selisih Gol</th>
    <th>Poin</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $posisi = 1;

      // Join standings table with teams table to get team_name
      $klasemen = $conn->query("SELECT t.team_name, s.matches_played, s.wins, s.draws, s.losses, s.goals_for, s.goals_against, s.goal_diff, s.points
                                FROM standings s
                                JOIN teams t ON s.team_id = t.id
                                ORDER BY s.points DESC");

      // Loop through and output the data
      while ($row = $klasemen->fetch_assoc()) {
        echo "<tr>
                <td>$posisi</td>
                <td>" . htmlspecialchars($row['team_name']) . "</td>
                <td>" . $row['matches_played'] . "</td>
                <td>" . $row['wins'] . "</td>
                <td>" . $row['draws'] . "</td>
                <td>" . $row['losses'] . "</td>
                <td>" . $row['goals_for'] . "</td>
                <td>" . $row['goals_against'] . "</td>
                <td>" . $row['goal_diff'] . "</td>
                <td>" . $row['points'] . "</td>
              </tr>";
        $posisi++;
      }
      ?>
    </tbody>
  </table>
</section>

  </div>

  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>
 <script>
  // Toggle profile menu visibility
  function toggleProfileMenu() {
    const menu = document.getElementById('profileMenu');
    menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
  }

  // Close profile menu when clicking outside
  document.addEventListener('click', function(event) {
    const menu = document.getElementById('profileMenu');
    const profileIcon = document.querySelector('.profile-icon');
    if (!profileIcon.contains(event.target) && !menu.contains(event.target)) {
      menu.style.display = 'none';
    }
  });
</script>

</body>
</html>
