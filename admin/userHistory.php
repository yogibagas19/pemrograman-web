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
           IFNULL(v.discount_rate * g.price / 100, 0) AS discount_amount, ph.payment_method
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
    <link rel="stylesheet" href="../css/admin/History.css">
</head>

<body>
    <div class="navbar">
        <div class="user-info">
            <span>Welcome, <?php echo htmlspecialchars(ucfirst($_SESSION['username'])); ?></span>
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
                        <th>ID</th>
                        <th>Nama Pengguna</th>
                        <th>Nama Game</th>
                        <th>Harga</th>
                        <th>Diskon</th>
                        <th>Pembayaran</th>
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
                            </td>
                            <td>
                                <?php
                                switch ($row['payment_method']) {
                                    case 'credit_card':
                                        echo "Kartu Kredit";
                                        break;
                                    case 'bank_transfer':
                                        echo "Transfer Bank";
                                        break;
                                    case 'e_wallet':
                                        echo "E-Wallet (OVO, GoPay, Dana)";
                                        break;
                                    case 'Minimarket':
                                        echo "Minimarket";
                                        break;
                                    default:
                                        echo "Metode tidak diketahui";
                                }
                                ?>
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
