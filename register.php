<?php

require_once 'db_connection.php';

$conn = connect_db();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash password dengan bcrypt
    $fullname = $_POST['fullname'];

    // Query untuk insert data
    $sql = "INSERT INTO users (username, password, fullname) 
            VALUES (?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $password, $fullname);

    if ($stmt->execute()) {
        echo "<script>
        alert('Registrasi berhasil, anda akan diarahkan ke login.');
        window.location.href ='login.php';
        </script>";
        exit();
    } else {
        echo "Error saat insert: " . $stmt->error;
    }

    $stmt->close();
}

// Tutup koneksi
$conn->close();
?>

<!-- Form HTML -->

<!DOCTYPE html>
<html>

<head>
    <title>Register</title>
    <link rel="stylesheet" href="css/register.css">
</head>

<body>
  <div class="glowing-light"></div>
  <div class="register-box">
    <form action="register.php" method="post">
      <input type="checkbox" class="input-check" id="input-check" />
      <label for="input-check" class="toggle">
        <span class="text off">off</span>
        <span class="text on">on</span>
      </label>
      <div class="light"></div>

      <form method="POST" action="">
            <h2>Register</h2>

            <div class="input-box">
                <input type="text" id="username" name="username" required>
                <label for="username">Username</label>
                <div class="input-line"></div>
            </div>

            <div class="input-box">
                <input type="password" id="password" name="password" required>
                <label for="password">Password</label>
                <div class="input-line"></div>
            </div>

            <div class="input-box">
                <input type="text" id="fullname" name="fullname" required>
                <label for="fullname">Fullname</label>
                <div class="input-line"></div>
            </div>

            <button type="submit">Register</button>
            <div class="login-link">
                <p><a href="./login.php">Already have an account? Login</a></p>
            </div>
        </form>
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