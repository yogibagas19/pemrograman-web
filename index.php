<?php
// Include file koneksi
require_once 'db_connection.php';

// Buat koneksi
$conn = connect_db();

// Query untuk mengambil semua data dari tabel barang
$query = "SELECT * FROM games";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Daftar Barang</title>
    <link rel="stylesheet" href="css/index.css"> <!-- Pastikan link ke file CSS Anda -->
</head>

<body>

    <div class="container">
        <h1>Daftar Barang</h1>
        <div class="card-container">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="card">
                        <a href="#" class="card-link">
                            <img src="images/<?php echo $row['photo'] ?>" alt="<?php echo $row['name'] ?>" class="card-img">
                            <div class="card-title"><?php echo $row['name'] ?></div>
                        </a>
                        <div class="details">
                            <p>Harga: Rp <?php echo number_format($row['price'], 2, ',', '.') ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Tidak ada game yang tersedia</p>
            <?php endif; ?>
        </div>

        <div class="links">
            <a class="btn btn-login" href="login.php">Klik di sini untuk Login</a>
            <br>
            <a class="btn btn-register" href="register.php">Klik di sini untuk Daftar</a>
        </div>
    </div>

</body>

</html>

<?php
$conn->close();
?>