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
    <style>
        /* Global Styles */
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
            color: #333;
        }

        /* Navbar */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background-color: #2c3e50;
            color: #ecf0f1;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            /* Pastikan navbar berada di atas sidebar */
            position: sticky;
            top: 0;
            z-index: 1000;
            /* Tambahkan ini agar z-index bekerja */
        }

        .navbar .user-info {
            font-size: 16px;
        }

        .navbar .logout-btn {
            text-decoration: none;
            padding: 8px 12px;
            background-color: #e74c3c;
            color: #ecf0f1;
            border-radius: 4px;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }

        .navbar .logout-btn:hover {
            background-color: #c0392b;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 60px;
            /* Jarak dari atas untuk menyesuaikan dengan tinggi navbar */
            left: 0;
            width: 220px;
            height: calc(100% - 60px);
            /* Kurangi tinggi navbar */
            background-color: #34495e;
            color: #ecf0f1;
            padding: 20px 10px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            z-index: 500;
            /* Z-index lebih rendah dari navbar */
        }

        .sidebar a {
            display: block;
            text-decoration: none;
            color: #bdc3c7;
            padding: 10px 15px;
            margin-bottom: 10px;
            border-radius: 4px;
            transition: background 0.3s, color 0.3s;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background-color: #1abc9c;
            color: #fff;
        }

        /* Content */
        .content {
            margin-left: 240px;
            padding: 20px;
            padding-top: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
        }

        table th,
        table td {
            padding: 15px;
            text-align: left;
            border: 1px solid #ddd;
        }

        table th {
            background-color: #2c3e50;
            color: #ecf0f1;
        }

        table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .action-buttons a {
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 4px;
            margin-right: 5px;
            font-size: 14px;
        }

        .btn {
            padding: 8px 12px;
            /* Ukuran padding */
            border-radius: 4px;
            font-size: 14px;
            /* Ukuran font */
            text-align: center;
            display: inline-block;
            /* Pastikan tombol tidak tumpuk */
            transition: background-color 0.3s ease;
        }

        /* Edit button */
        .edit-btn {
            background-color: #3498db;
            color: #fff;
            text-decoration: none;
        }

        .edit-btn:hover {
            background-color: #2980b9;
        }

        /* Delete button */
        .delete-btn {
            background-color: #e74c3c;
            color: #fff;
            text-decoration: none;
        }

        .delete-btn:hover {
            background-color: #c0392b;
        }

        .disabled {
            color: #888;
        }

        .add-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 15px;
            background-color: #1abc9c;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
        }

        .add-btn:hover {
            background-color: #16a085;
        }
    </style>
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