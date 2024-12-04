<?php
// Mulai sesi
session_start();

// Cek apakah pengguna adalah admin (misalnya dengan mengecek role dalam session)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<script>
        alert('Hanya admin yang dapat mengakses halaman ini.');
        window.location.href = '../login.php';
    </script>";
    exit;
}

// Include file koneksi
require_once '../db_connection.php';

// Buat koneksi
$conn = connect_db();

// Ambil semua transaksi dari tabel purchase_history
$query = "SELECT ph.id, ph.purchase_date, u.fullname AS user_name, g.name AS game_name, ph.total_price, 
                 IFNULL(v.discount_rate * g.price / 100, 0) AS discount_amount
          FROM purchase_history ph
          LEFT JOIN users u ON ph.user_id = u.id
          LEFT JOIN games g ON ph.game_id = g.id
          LEFT JOIN vouchers v ON ph.voucher_id = v.id
          ORDER BY ph.purchase_date DESC"; // Menampilkan berdasarkan tanggal pembelian terbaru

$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

// Tutup koneksi
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pembelian - Admin</title>
    <link rel="stylesheet" href="../css/admin/transactionHistory.css">
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
            position: relative;
            /* Tidak menggunakan fixed agar tidak melampaui konten */
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
            left: 0;
            width: 220px;
            height: 100%;
            background-color: #34495e;
            color: #ecf0f1;
            padding: 20px 10px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
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

        .card {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .card h2 {
            margin: 0 0 10px;
            font-size: 1.5em;
        }

        .card ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .card ul li {
            margin-bottom: 10px;
        }

        .card ul li a {
            text-decoration: none;
            color: #3498db;
            transition: color 0.3s;
        }

        .card ul li a:hover {
            color: #2980b9;
        }
    </style>
</head>

<body>
    <div class="navbar">
        <h1>GameStore Admin</h1>
        <ul>
            <li><a href="dashboardAdmin.php">Dashboard</a></li>
            <li><a href="listGames.php">List Game</a></li>
            <li><a href="transactionHistory.php">Riwayat Pembelian</a></li>
            <li><a href="logout.php" class="logout-btn">Logout</a></li>
        </ul>
    </div>

    <div class="container">
        <h1>Riwayat Pembelian Semua Pengguna</h1>

        <?php if ($result->num_rows > 0): ?>
            <table>
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
            </table>
        <?php else: ?>
            <p>Tidak ada transaksi yang ditemukan.</p>
        <?php endif; ?>
    </div>
</body>

</html>