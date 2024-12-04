<?php
// Mulai sesi
session_start();

// Jika pengguna belum login, tampilkan JS alert dan arahkan ke halaman login
if (!isset($_SESSION['username'])) {
    echo "<script>
        alert('Anda harus login terlebih dahulu untuk mengakses halaman ini.');
        window.location.href = 'login.php';
    </script>";
    exit;
}

// Include file koneksi
require_once 'db_connection.php';

// Buat koneksi
$conn = connect_db();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil</title>
    <link rel="stylesheet" href="css/user.css">
</head>
<body>
    <div class="navbar">
        <h1>GameStore</h1>
        <ul>
            <li><a href="dashboardUser.php">Dashboard</a></li>
            <li><a href="gameList.php">Game List</a></li>
            <li><a href="purchase_history.php">Riwayat Pembelian</a></li>
            <li><a href="logout.php" class="logout-btn">Logout</a></li>
        </ul>
    </div>

    <div class="container">
        <h1>Profil Pengguna</h1>
        <span>Welcome, <?php echo $_SESSION['fullname']; ?></span>
        <p><strong>Username:</strong><?php echo $_SESSION['username']; ?></p>
        <p><strong>Password (Hash):</strong> <?php echo $_SESSION['password']; ?></p>
        <p><strong>Full Name:</strong> <?php echo $_SESSION['fullname']; ?></p>
    </div>
</body>
</html>
