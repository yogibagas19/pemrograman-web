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
    <!-- Navbar -->
    <div class="navbar">
        <div class="user-info">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['fullname']); ?></span>
        </div>
        <a href="../logout.php" class="logout-btn">Logout</a>
    </div>

    <!-- Sidebar -->
    <div class="sidebar">
        <a href="dashboardAdmin.php" class="active">Dashboard</a>
        <a href="listUsers.php">List Users</a>
        <a href="listGames.php">List Games</a>
        <a href="listVoucher.php">List Vouchers</a>
    </div>

    <!-- Main Content -->
    <div class="content">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['fullname']); ?>!</h1>
        <div class="card">
            <h2>Quick Actions</h2>
            <ul>
                <li><a href="createGame.php">Tambah Game Baru</a></li>
                <li><a href="addVoucher.php">Tambah Voucher Baru</a></li>
            </ul>
        </div>
    </div>
</body>

</html>