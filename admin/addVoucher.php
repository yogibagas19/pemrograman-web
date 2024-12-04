<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}
include '../db_connection.php';
$conn = connect_db();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $code = $_POST['code'];
    $discount_rate = $_POST['discount_rate'];
    $max_discount = $_POST['max_discount'] ?? null; // Opsional
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $usage_limit = $_POST['usage_limit'];

    // Validasi input
    if (empty($code) || empty($discount_rate) || empty($start_date) || empty($end_date) || empty($usage_limit)) {
        die("Semua field wajib diisi.");
    }

    // Query untuk menyimpan voucher baru
    $sql = "INSERT INTO vouchers (code, discount_rate, max_discount, start_date, end_date, usage_limit) 
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sddssi", $code, $discount_rate, $max_discount, $start_date, $end_date, $usage_limit);

    if ($stmt->execute()) {
        echo "<script>
        alert('Voucher berhasil dibuat!');
        window.location.href = 'addVoucher.php';
        </script>";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Tutup koneksi
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Voucher</title>
</head>

<body>
    <div class="navbar">
        <div class="user-info">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['fullname']); ?></span>
        </div>
        <a href="../logout.php" class="logout-btn">Logout</a>
    </div>
    <div class="sidebar">
        <a href="dashboardAdmin.php">Dashboard</a>
        <a href="listVoucher.php">List Voucher</a>
    </div>

    <div class="content">
        <form method="POST" action="">
            <label for="code">Kode Voucher:</label>
            <input type="text" id="code" name="code" required>

            <label for="discount_rate">Persentase Diskon (%):</label>
            <input type="number" id="discount_rate" name="discount_rate" min="1" max="100" required>

            <label for="max_discount">Diskon Maksimal Rp (opsional):</label>
            <input type="number" id="max_discount" name="max_discount">

            <label for="start_date">Tanggal Mulai:</label>
            <input type="date" id="start_date" name="start_date" required>

            <label for="end_date">Tanggal Kadaluarsa:</label>
            <input type="date" id="end_date" name="end_date" required>

            <label for="usage_limit">Batas Penggunaan:</label>
            <input type="number" id="usage_limit" name="usage_limit" min="1" required>

            <button type="submit">Buat Voucher</button>
        </form>

    </div>
</body>

</html>

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
        padding-top: 80px;
        color: #fff;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100%;
    }

    .content h1 {
        font-size: 2em;
        margin-bottom: 20px;
    }

    form {
        background-color: #232e38;
        padding: 20px 30px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.4);
        width: 100%;
        max-width: 400px;
    }

    form h2 {
        text-align: center;
        color: #ff962d;
        margin-bottom: 20px;
    }

    label {
        display: block;
        font-weight: bold;
        margin-bottom: 8px;
        color: #ff962d;
    }

    input[type="text"],
    input[type="number"],
    input[type="date"] {
        width: 100%;
        padding: 10px;
        margin-bottom: 20px;
        border: 1px solid #444;
        border-radius: 5px;
        background-color: #2c353f;
        color: #fff;
        font-size: 14px;
    }

    input:focus {
        border-color: #00f7ff;
        outline: none;
    }

    button {
        width: 100%;
        padding: 12px;
        background-color: #00f7ff;
        color: #151f28;
        font-weight: bold;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s ease;
        font-size: 16px;
    }

    button:hover {
        background-color: #00a7d0;
    }
</style>