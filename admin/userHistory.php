<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

include '../db_connection.php';
$conn = connect_db();

// Ambil data riwayat pembelian dari tabel purchase_history
$query = "
    SELECT ph.id, ph.purchase_date, u.fullname AS user_name, g.name AS game_name, ph.total_price, 
           IFNULL(v.discount_rate * g.price / 100, 0) AS discount_amount
    FROM purchase_history ph
    LEFT JOIN users u ON ph.user_id = u.id
    LEFT JOIN games g ON ph.game_id = g.id
    LEFT JOIN vouchers v ON ph.voucher_id = v.id
    ORDER BY ph.purchase_date ASC
";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pembelian</title>
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
        <a href="listGames.php">List Games</a>
        <a href="listVoucher.php">List Vouchers</a>
        <a href="#" class="active">User History</a>
    </div>

    <!-- Main Content -->
    <div class="content">
        <h1>Riwayat Pembelian Pengguna</h1>
        <table>
            <?php if ($result->num_rows > 0): ?>
                <thead>
                    <tr>
                        <th>ID Pembelian</th>
                        <th>Nama Pengguna</th>
                        <th>Nama Game</th>
                        <th>Total Harga</th>
                        <th>Diskon</th>
                        <th>Tanggal Pembelian</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['game_name']); ?></td>
                            <td>Rp. <?php echo number_format($row['total_price'], 0, ',', '.'); ?></td>
                            <td>
                                <?php if ($row['discount_amount'] > 0): ?>
                                    Rp. <?php echo number_format($row['discount_amount'], 0, ',', '.'); ?>
                                <?php else: ?>
                                    Tidak ada diskon
                                <?php endif; ?>
                            </td>
                            <td><?php echo date("d-m-Y H:i", strtotime($row['purchase_date'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            <?php else: ?>
                <h1>Tidak ada transaksi tersedia saat ini.</h1>
            <?php endif; ?>
        </table>
    </div>
</body>

</html>
