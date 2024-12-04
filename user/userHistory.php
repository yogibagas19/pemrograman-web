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

// Ambil id_user dari session
$id_user = $_SESSION['id_user'];

// Ambil riwayat pembelian berdasarkan user_id
$query = "SELECT ph.id, ph.purchase_date, g.name AS game_name, ph.total_price, 
                 IFNULL(v.discount_rate * g.price / 100, 0) AS discount_amount
          FROM purchase_history ph
          LEFT JOIN games g ON ph.game_id = g.id
          LEFT JOIN vouchers v ON ph.voucher_id = v.id
          WHERE ph.user_id = ?
          ORDER BY ph.purchase_date DESC"; // Menampilkan berdasarkan tanggal pembelian terbaru

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_user);
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
    <title>Riwayat Pembelian</title>
    <link rel="stylesheet" href="../css/user/gameList.css" />
</head>

<body>
    <div class="navbar">
        <h1>GameStore</h1>
        <ul>
            <li><a href="gameList.php">Game List</a></li>
            <li><a href="userHistory.php">Riwayat Pembelian</a></li>
            <li><a href="logout.php" class="logout-btn">Logout</a></li>
        </ul>
    </div>

    <div class="container">
        <h1>Riwayat Pembelian</h1>

        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID Pembelian</th>
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
            <p>Anda belum melakukan pembelian.</p>
        <?php endif; ?>
    </div>
</body>

</html>