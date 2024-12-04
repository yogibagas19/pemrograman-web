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

// Proses penghapusan barang jika ada parameter id yang diterima
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_query = "DELETE FROM games WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        echo "<script>
            alert('Game berhasil dihapus.');
            window.location.href = 'listGames.php';
        </script>";
        exit;
    } else {
        echo "<script>alert('Gagal menghapus game.');</script>";
    }
}

// Query untuk mengambil semua data dari tabel barang
$query = "SELECT * FROM games";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Content</title>
    <link rel="stylesheet" href="../css/admin/games.css">
</head>

<body>
    <!-- Navbar -->
    <div class="navbar">
        <div class="user-info">
        <span>Welcome, <?php echo htmlspecialchars(ucfirst($_SESSION['fullname'])); ?></span>
        </div>
        <a href="../logout.php" class="logout-btn">Logout</a>
    </div>

    <!-- Sidebar -->
    <div class="sidebar">
        <a href="dashboardAdmin.php">Dashboard</a>
        <a href="listUsers.php">List Users</a>
        <a href="#" class="active">List Games</a>
        <a href="listVoucher.php">List Vouchers </a>
        <a href="userHistory.php">User History</a>
    </div>

    <div class="content">
        <h1>List Game</h1>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Game</th>
                    <th>Harga Game</th>
                    <th>Foto Game</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id'] ?></td>
                            <td><?php echo $row['name'] ?></td>
                            <td>Rp <?php echo number_format($row['price'], 2, ',', '.') ?></td>
                            <td><img src="../images/<?php echo $row['photo'] ?>" alt="<?php echo $row['name'] ?>" class="product-img" width="250"></td>
                            <td>
                                <a href="editGame.php?id=<?php echo $row['id'] ?>" class="btn edit-btn">Edit</a>
                                <a href="?delete_id=<?php echo $row['id'] ?>" class="btn delete-btn" onclick='return confirm(\"Anda yakin ingin menghapus game ini?\")'>Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">Tidak ada data game.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <a href="createGame.php" class="btn add-btn">Tambah Game</a>
    </div>
</body>

</html>


<?php
// Tutup koneksi database
$conn->close();
?>