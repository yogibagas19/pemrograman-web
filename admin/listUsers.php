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
    <link rel="stylesheet" href="../css/admin/user.css">
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