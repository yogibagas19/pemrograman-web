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
    <link rel="stylesheet" href="../css/admin/voucher.css">
</head>

<body>
    <div class="navbar">
        <div class="user-info">
            <span>Welcome, <?php echo htmlspecialchars(ucfirst($_SESSION['fullname'])); ?></span>
        </div>
        <a href="../logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="sidebar">
        <a href="dashboardAdmin.php">Dashboard</a>
        <a href="listUsers.php">List Users</a>
        <a href="listGames.php">List Games</a>
        <a href="#" class="active">List Vouchers</a>
        <a href="userHistory.php">User History</a>
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