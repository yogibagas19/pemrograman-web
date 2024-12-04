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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create'])) {
    // Ambil data dari form
    $nama = $_POST['nama'];
    $harga = $_POST['harga'];
    $desc = $_POST['desc'];
    $foto = $_FILES['photo']['name'];
    $target = '../images/' . basename($foto);
    
    // Validasi tipe file foto (hanya gambar yang diizinkan)
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $file_type = $_FILES['photo']['type'];
    if (!in_array($file_type, $allowed_types)) {
        echo "<script>alert('File yang diupload harus berupa gambar (JPEG, PNG, GIF).');</script>";
        exit;
    }

    // Memastikan foto berhasil di-upload sebelum menambahkan data ke DB
    if (move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
        // Query menggunakan prepared statement untuk mencegah SQL injection
        $query = "INSERT INTO games (name, description, price, photo) 
                  VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssis", $nama, $desc, $harga, $foto);

        if ($stmt->execute()) {
            echo "<script>alert('Barang berhasil ditambahkan'); window.location.href = 'listGames.php';</script>";
        } else {
            echo "Error: " . $stmt->error;
        }
    } else {
        echo "<script>alert('Gagal mengupload foto.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Barang</title>
    <link rel="stylesheet" href="../css/admin/createGame.css">
</head>
<body>
<div class="navbar">
    <div class="user-info">
        <span>Welcome, <?php echo htmlspecialchars(ucfirst($_SESSION['username'])); ?></span>
    </div>
    <a href="../logout.php" class="logout-btn">Logout</a>
</div>

<div class="sidebar">
    <a href="dashboardAdmin.php">Dashboard</a>
    <a href="listGames.php">List Games</a>
</div>

<div class="content">
    <form action="createGame.php" method="POST" enctype="multipart/form-data">
        <h1>Tambah Game Baru</h1>
        
        <label for="nama">Nama:</label>
        <input type="text" id="nama" name="nama" required>
        
        <label for="harga">Harga:</label>
        <input type="number" id="harga" name="harga" required>

        <label for="desc">Deskripsi:</label>
        <textarea name="desc" id="desc" rows="3" maxlength="500" required></textarea>
        
        <label for="photo">Foto Barang:</label>
        <input type="file" id="photo" name="photo" required>
        
        <button type="submit" name="create">Tambah Barang</button>
    </form>
</div>
</body>
</html>

<?php
$conn->close();
?>
