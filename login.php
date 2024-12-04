<?php

session_start();

// Include file koneksi
require_once 'db_connection.php';

// Buat koneksi
$conn = connect_db();

// Proses login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $user = $_POST['username'];
  $pass = $_POST['password'];

  // Cek data user
  $query = "SELECT * FROM users WHERE username = ?";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("s", $user);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();

    // Verifikasi password dengan bcrypt
    if (password_verify($pass, $row['password'])) {
      $_SESSION['id_user'] = $row['id'];
      $_SESSION['username'] = $row['username'];
      $_SESSION['fullname'] = $row['fullname'];
      $_SESSION['role'] = $row['role'];

      // Arahkan berdasarkan id_role
      if ($_SESSION['role'] == 'user' || $_SESSION['role'] == 'member') {
        header("Location: user/gameList.php");
      } elseif ($_SESSION['role'] == 'admin'){
        header("Location: admin/dashboardAdmin.php");
      }else{
        echo 'role tidak dikenali';
      }
      exit;
    } else {
      $error_message = "Password salah!";
    }
  } else {
    $error_message = "Username tidak ditemukan!";
  }

  $stmt->close();
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login</title>
  <link rel="stylesheet" href="../css/login.css" />
</head>

<body>
  <div class="error-message" id="error-message"></div>

  <div class="glowing-light"></div>
  <div class="login-box">
    <form action="login.php" method="post">
      <input type="checkbox" class="input-check" id="input-check" />
      <label for="input-check" class="toggle">
        <span class="text off">off</span>
        <span class="text on">on</span>
      </label>
      <div class="light"></div>

      <h2>Login</h2>
      <div class="input-box">
        <span class="icon">
          <ion-icon name="mail"></ion-icon>
        </span>
        <input type="text" name="username" required />
        <label>Username</label>
        <div class="input-line"></div>
      </div>
      <div class="input-box">
        <span class="icon">
          <ion-icon name="lock-closed"></ion-icon>
        </span>
        <input type="password" name="password" required />
        <label>Password</label>
        <div class="input-line"></div>
      </div>
      <button type="submit">Login</button>
      <div class="register-link">
        <p><a href="./register.php">Register</a></p>
      </div>
    </form>
  </div>

  <script
    type="module"
    src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
  <script
    nomodule
    src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
  <script>
    // Fungsi untuk menampilkan error
    function showError(message) {
      const errorElement = document.getElementById('error-message');
      errorElement.textContent = message;
      errorElement.classList.add('show');

      // Hilangkan error setelah 5 detik
      setTimeout(() => {
        errorElement.classList.remove('show');
      }, 5000);
    }

    // Cek apakah ada pesan error dari PHP
    <?php if (isset($error_message)): ?>
      showError('<?php echo $error_message; ?>');
    <?php endif; ?>
  </script>
</body>

</html>