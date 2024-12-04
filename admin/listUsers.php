<?php
session_start();
include '../db_connection.php';
$conn = connect_db();

// Cek apakah pengguna sudah login
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// Proses penghapusan data jika permintaan datang melalui metode POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);

    // Proteksi tambahan: cek ID untuk mencegah penghapusan ID 1
    if ($id === 1) {
        echo "Cannot delete user with ID 1.";
    } else {
        // Jika bukan ID 1, hapus pengguna
        $deleteSql = "DELETE FROM users WHERE id = ?";
        $deleteStmt = $conn->prepare($deleteSql);
        $deleteStmt->bind_param("i", $id);
        if ($deleteStmt->execute()) {
            echo "User deleted successfully.";
        } else {
            echo "Failed to delete user: " . $conn->error;
        }
        $deleteStmt->close();
    }

    $conn->close();
    exit; // Menghentikan eksekusi agar tidak menampilkan HTML di bawah
}

// Ambil data dari tabel users
$sql = "select id, username, fullname, role from users";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List of Users</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function deleteUser(userId) {
            if (confirm("Are you sure you want to delete this user?")) {
                $.ajax({
                    url: '', // Endpoint PHP
                    type: 'POST',
                    data: {
                        id: userId
                    },
                    success: function(response) {
                        alert(response);
                        location.reload();
                    },
                    error: function() {
                        alert("Error while deleting the user.");
                    }
                });
            }
        }
    </script>
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

        .edit-btn {
            background-color: #3498db;
            color: #fff;
        }

        .edit-btn:hover {
            background-color: #2980b9;
        }

        .delete-btn {
            background-color: #e74c3c;
            color: #fff;
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
        <a href="#" class="active">List Users</a>
        <a href="listGames.php">List Games</a>
        <a href="listVoucher.php">List Vouchers</a>
        <a href="userHistory.php">User History</a>
    </div>

    <!-- Content -->
    <div class="content">
        <h1>List of Users</h1>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Fullname</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['username'] ?></td>
                        <td><?= $row['fullname'] ?></td>
                        <td><?= $row['role'] ?></td>
                        <td class="action-buttons">
                            <a href="editUser.php?id=<?= $row['id'] ?>" class="edit-btn">Edit</a>
                            <?php if ($row['role'] != 'admin'): ?>
                                <a href="#" class="delete-btn" onclick="deleteUser(<?= $row['id'] ?>)">Delete</a>
                            <?php else: ?>
                                <span class="disabled">Cannot delete</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>

</html>