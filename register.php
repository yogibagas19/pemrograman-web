<?php
// Include file koneksi
require_once 'db_connection.php';

// Buat koneksi
$conn = connect_db();

// Tangkap data dari form register
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
    <link rel="stylesheet" href="../css/register.css">
</head>

<body>
    <div class="glowing-light"></div>
    <div class="register-box">
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
                <a href="login.php">Already have an account? Login</a>
            </div>
        </form>
    </div>
</body>

</html>