<?php
include 'koneksi.php';
$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = trim($_POST['username']);
  $email    = trim($_POST['email']);
  $password = $_POST['password'];
  $confirm_password = $_POST['confirm_password'];

  // Validasi konfirmasi password
  if ($password !== $confirm_password) {
    $error = "Password dan konfirmasi password tidak sama!";
  } else {
    // Cek username
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
      $error = "Username sudah digunakan, silakan pilih username lain.";
    }
    $stmt->close();

    // Cek email
    if (!$error) {
      $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
      $stmt->bind_param("s", $email);
      $stmt->execute();
      $stmt->store_result();
      if ($stmt->num_rows > 0) {
        $error = "Email sudah digunakan, silakan gunakan email lain.";
      }
      $stmt->close();
    }

    // Simpan data
    if (!$error) {
      $password_hash = password_hash($password, PASSWORD_DEFAULT);
      $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
      $stmt->bind_param("sss", $username, $email, $password_hash);
      if ($stmt->execute()) {
        $success = "Registrasi berhasil! Silahkan Login</a>.";
      } else {
        $error = "Terjadi kesalahan saat registrasi, silakan coba lagi.";
      }
      $stmt->close();
    }
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <title>Daftar Akun</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #fceabb 0%, #007bff 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 50px 20px;
    }
    .register-box {
      background: #fff;
      padding: 40px 30px;
      border-radius: 16px;
      box-shadow: 0 12px 25px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 500px;
    }
    .register-title {
      font-weight: 600;
      font-size: 1.8rem;
      text-align: center;
      margin-bottom: 25px;
      color: #333;
    }
    .form-label {
      font-weight: 500;
    }
    .btn-register {
      background-color: #007bff;
      border: none;
      color: white;
      width: 100%;
      font-weight: bold;
      transition: 0.3s ease;
    }
    .btn-register:hover {
      background-color: #0056b3;
      transform: scale(1.03);
    }
    .login-link {
      text-align: center;
      display: block;
      margin-top: 20px;
      font-size: 0.9rem;
      color: #333;
    }
    .login-link:hover {
      text-decoration: underline;
      color: #f39c12;
    }
  </style>
</head>
<body>
  <div class="register-box">
    <div class="register-title">📝 Daftar Akun</div>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php elseif ($success): ?>
      <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label" for="username">👤 Username</label>
          <input type="text" id="username" name="username" required class="form-control" placeholder="Masukkan username..." value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" />
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label" for="email">📧 Email</label>
          <input type="email" id="email" name="email" required class="form-control" placeholder="Masukkan email..." value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" />
        </div>
      </div>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label" for="password">🔒 Password</label>
          <input type="password" id="password" name="password" required class="form-control" placeholder="Masukkan password..." />
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label" for="confirm_password">🔒 Konfirmasi Password</label>
          <input type="password" id="confirm_password" name="confirm_password" required class="form-control" placeholder="Ulangi password..." />
        </div>
      </div>
      <button type="submit" class="btn btn-register mt-2">🚀 Daftar</button>
    </form>
    <a href="login.php" class="login-link">Sudah punya akun? Login di sini 🔑</a>
  </div>
</body>
</html>
