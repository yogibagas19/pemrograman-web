<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}
include '../db_connection.php';
$conn = connect_db();

// Proses penghapusan data jika permintaan datang melalui metode POST
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_query = "DELETE FROM vouchers WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        echo "<script>
            alert('Game berhasil dihapus.');
            window.location.href = 'listVoucher.php';
        </script>";
        exit;
    } else {
        echo "<script>alert('Gagal menghapus voucher.');</script>";
    }
}

$voucher = "select * from vouchers";
$hasil = $conn->query($voucher);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List of Vouchers</title>
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
            position: relative;
            /* Tambahkan ini agar z-index bekerja */
        }

        .navbar .user-info {
            display: flex;
            align-items: center;
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
            padding-top: 80px;
        }

        .content h1 {
            font-size: 2em;
            margin-bottom: 20px;
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
    <div class="navbar">
        <div class="user-info">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['fullname']); ?></span>
        </div>
        <a href="../logout.php" class="logout-btn">Logout</a>
    </div>



    <!-- Sidebar -->
    <div class="sidebar">
        <a href="dashboardAdmin.php">Dashboard</a>
        <a href="listUsers.php">List Users</a>
        <a href="listGames.php">List Games</a>
        <a href="#" class="active">List Voucher</a>
    </div>

    <!-- Main Content -->
    <div class="content">
        <h1>List of Voucher</h1>
        <table>
            <?php if ($hasil->num_rows > 0): ?>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Kode</th>
                        <th>Rate</th>
                        <th>Jangka Waktu</th>
                        <th>Jumlah Pemakaian</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $hasil->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id'] ?></td>
                            <td><?php echo $row['code'] ?></td>
                            <td><?php echo $row['discount_rate'] ?></td>
                            <td><?php echo $row['start_date'] ?> - <?php echo $row['end_date'] ?></td>
                            <td><?php echo $row['usage_limit'] ?></td>
                            <td>
                                <a href="editVoucher.php?id=<?php echo $row['id'] ?>" class="btn edit-btn">Edit</a>
                                <a href="?delete_id=<?php echo $row['id'] ?>" class="btn delete-btn" onclick="return confirm('Anda yakin ingin menghapus voucher ini?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <h1>Tidak ada voucher tersedia saat ini.</h1>
                <?php endif; ?>
                </tbody>
        </table>
        <a href="addVoucher.php" class="btn add-btn">Tambah Voucher</a>
    </div>
</body>

</html>