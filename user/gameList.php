<?php
// Mulai sesi
session_start();

// Jika pengguna belum login, tampilkan JS alert dan arahkan ke halaman login
if (!isset($_SESSION['username'])) {
    echo "<script>
        alert('Anda harus login terlebih dahulu untuk mengakses halaman ini.');
        window.location.href = '../login.php';
    </script>";
    exit;
}

// Include file koneksi
require_once '../db_connection.php';

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
    <title>Dashboard Users</title>
    <link rel="stylesheet" href="../css/user/gameList.css" />
    <style>
        .posisi{
            color: #00f7ff;
        }
    </style>
</head>

<body>
    <div class="navbar">
        <h1>GameStore</h1>
        <ul>
            <li><a href="gameList.php" class="posisi">Game List</a></li>
            <li><a href="userHistory.php">Riwayat Pembelian</a></li>
            <li><a href="editProfileUser.php">Edit Profile</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </div>
    <div class="container">
        <h1>Selamat Datang, <?php echo htmlspecialchars($_SESSION['fullname']); ?>!</h1>

        <h1>Daftar Game</h1>

        <div class="card-container">
            <?php
            if ($result->num_rows > 0) {
                // Output data per item dalam bentuk card
                while ($row = $result->fetch_assoc()) {
                    echo "<div class='card'>";
                    echo "<a href='beliGame.php?id=" . $row['id'] . "' class='card-link'>";

                    // Gambar barang
                    if (!empty($row['photo'])) {
                        echo "<img src='../images/" . htmlspecialchars($row['photo']) . "' alt='" . htmlspecialchars($row['name']) . "' class='card-img'>";
                    } else {
                        echo "<img src='../images/default.png' alt='Tidak ada foto' class='card-img'>";
                    }

                    // Ganti tombol "BELI"
                    echo "<h3 class='card-title'>BELI</h3>";
                    echo "</a>";

                    // Hapus bagian harga dan jumlah, hanya tombol BELI yang tersisa
                    echo "<div class='details'>";
                    echo "<a href='beliGame.php?id=" . $row['id'] . "' class='details-btn'>Lihat Detail</a>";
                    echo "</div>";

                    echo "</div>";
                }
            } else {
                echo "<p>Tidak ada data barang.</p>";
            }
            ?>
        </div>
    </div>
    <script>
  // Mendapatkan semua link dalam navbar
  const navLinks = document.querySelectorAll('.navbar a');

  // Menambahkan event listener pada setiap link
  navLinks.forEach(link => {
    // Cek jika URL saat ini sama dengan href dari link
    if (window.location.href.includes(link.href)) {
      link.classList.add('active'); // Tambahkan kelas 'active' jika cocok
    }
  });
</script>

</body>

</html>

<?php
// Tutup koneksi database
$conn->close();
?>