<?php
session_start();

// Pastikan pengguna sudah login dan memiliki role
if (!isset($_SESSION['role'])) {
    // Jika tidak ada role dalam session, alihkan ke halaman login
    header("Location: login.php");
    exit;
}

$role = $_SESSION['role']; // Mendapatkan role dari session

// Jika parameter `action=logout` diterima, logout pengguna
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    // Proses logout
    session_destroy();
    header("Location: login.php");
    exit;
}
?>

<script>
    // Menampilkan konfirmasi logout
    var logoutConfirmed = confirm("Apakah Anda yakin ingin keluar?");
    if (logoutConfirmed) {
        // Jika pengguna klik "OK", lanjutkan ke halaman logout
        window.location.href = window.location.href + "?action=logout"; // Menambahkan query parameter untuk logout
    } else {
        // Jika pengguna klik "Cancel", arahkan ke halaman sesuai role
        <?php if ($role == 'admin'): ?>
            window.location.href = 'admin/dashboardAdmin.php'; // Arahkan ke dashboard admin
        <?php elseif ($role == 'user' || $role == 'member'): ?>
            window.location.href = 'user/gameList.php'; // Arahkan ke halaman game list untuk user/member
        <?php else: ?>
            window.location.href = 'login.php'; // Default, jika role tidak valid
        <?php endif; ?>
    }
</script>
