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
    $nama = $_POST['nama'];
    $harga = $_POST['harga'];
    $desc = $_POST['desc'];
    $foto = $_FILES['photo']['name'];
    $target = '../images/' . basename($foto);

    $query = "INSERT INTO games (name, price, photo) 
              VALUES ('$nama', '$harga','$foto')";
    if ($conn->query($query) === TRUE) {
        move_uploaded_file($_FILES['photo']['tmp_name'], $target);
        echo "<script>alert('Barang berhasil ditambahkan'); window.location.href = 'listGames.php';</script>";
    } else {
        echo "Error: " . $query . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Barang</title>
    <!-- Link ke file CSS -->
    <link rel="stylesheet" href="../../css/admin/createDataGame.css">
</head>
<body>
    <h1>Tambah Barang Baru</h1>

    <form action="createGame.php" method="POST" enctype="multipart/form-data">
        <label for="nama">Nama:</label>
        <input type="text" id="nama" name="nama" required>
        
        <label for="harga">Harga:</label>
        <input type="number" id="harga" name="harga" required>

        <label for="deskripsi">Deksripsi:</label>
        <textarea name="deskripsi" id="desc" rows="3" maxlength="50"></textarea>
        
        <label for="photo">Foto Barang:</label>
        <input type="file" id="photo" name="photo" required>

        <button type="submit" name="create">Tambah Barang</button>
    </form>

    <a href="listGames.php">List Game ?</a>
</body>
</html>


<?php
// Tutup koneksi database
$conn->close();
?>
