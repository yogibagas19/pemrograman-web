<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/admin/dashboardAdmin.css">
</head>

<body>
    <div class="navbar">
        <div class="user-info">
            <span>Welcome, <?php echo htmlspecialchars(ucfirst($_SESSION['fullname'])); ?></span>
        </div>
        <a href="../logout.php" class="logout-btn">Logout</a>
    </div>
    <div class="sidebar">
        <a href="dashboardAdmin.php" class="active">Dashboard</a>
        <a href="listUsers.php">List Users</a>
        <a href="listGames.php">List Games</a>
        <a href="listVoucher.php">List Vouchers</a>
        <a href="userHistory.php">User History</a>
    </div>
    <div class="content">
        <h1>Welcome, <?php echo htmlspecialchars(ucfirst($_SESSION['fullname'])); ?>!</h1>
        <div class="card">
            <h2>Quick Actions</h2>
            <ul>
                <li><p><a href="createGame.php">- Tambah Game Baru</a></p></li>
                <li><p></p><a href="addVoucher.php">- Tambah Voucher Baru</a></p></li>
            </ul>
        </div>
    </div>
</body>

</html>