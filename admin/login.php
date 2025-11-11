<?php
session_start();
include 'koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $email = mysqli_real_escape_string($conn, $_POST['email']);
  $password = $_POST['password'];

  $result = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
  $user = mysqli_fetch_assoc($result);

  if ($user && password_verify($password, $user['password'])) {
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = 'admin';  // Semua yang login dianggap admin

    header("Location: dashboard_admin.php");
    exit;
  } else {
    $error = "❌ Email atau password salah!";
  }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login | Ligaku</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #007bff,rgb(255, 255, 255));
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .login-box {
      background: #fff;
      padding: 40px;
      border-radius: 20px;
      box-shadow: 0 12px 30px rgba(0,0,0,0.1);
      width: 100%;
      max-width: 400px;
      transition: transform 0.3s ease-in-out;
    }
    .login-box:hover {
      transform: translateY(-5px);
    }
    .login-title {
      font-weight: 600;
      font-size: 2rem;
      text-align: center;
      margin-bottom: 1.5rem;
      color: #333;
    }
    .form-label {
      font-weight: 500;
    }
    .btn-login {
      background-color: #007bff;
      border: none;
      color: white;
      width: 100%;
      font-weight: bold;
      transition: 0.3s ease;
    }
    .btn-login:hover {
      background-color: #007bff;
      transform: scale(1.05);
    }
    .register-link {
      text-align: center;
      margin-top: 1rem;
      display: block;
      color: #333;
      font-size: 0.9rem;
    }
    .register-link:hover {
      color: #00b0e0;
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="login-box">
    <div class="login-title">⚽ Login ke Ligaku</div>
    <?php if (isset($error)) echo "<div class='alert alert-danger text-center'>$error</div>"; ?>
    <form method="POST">
      <div class="mb-3">
        <label class="form-label">📧 Email</label>
        <input type="email" name="email" class="form-control" required placeholder="Masukkan email..." />
      </div>
      <div class="mb-3">
        <label class="form-label">🔒 Password</label>
        <input type="password" name="password" class="form-control" required placeholder="Masukkan password..." />
      </div>
      <button type="submit" class="btn btn-login">🚀 Login</button>
    </form>
    <a href="register.php" class="register-link">Belum punya akun? Daftar di sini 💡</a>
  </div>
</body>
</html>
