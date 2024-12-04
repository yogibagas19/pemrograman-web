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

// Periksa apakah parameter 'id' ada di URL
if (isset($_GET['id'])) {
    $id_game = $_GET['id'];

    // Ambil data game berdasarkan id
    $query = "SELECT id, name, price, photo, description FROM games WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_game);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $game = $result->fetch_assoc();
    } else {
        echo "<script>
            alert('Game tidak ditemukan.');
            window.location.href = 'listGames.php';
        </script>";
        exit;
    }
}

// Jika form di-submit, lakukan update data game
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $nama = $_POST['nama'];
    $harga = $_POST['harga'];
    $desc = $_POST['desc'];

    // Periksa apakah file foto baru di-upload
    if ($_FILES['photo']['name']) {
        $foto = $_FILES['photo']['name'];
        $target = '../images/' . basename($foto);
        move_uploaded_file($_FILES['photo']['tmp_name'], $target);
        $query = "UPDATE games SET name = ?, price = ?, photo = ?, description = ? WHERE id = ?";
    } else {
        // Jika tidak ada foto baru, gunakan foto lama
        $foto = $game['photo'];
        $query = "UPDATE games SET name = ?, price = ?, description = ? WHERE id = ?";
    }

    $stmt = $conn->prepare($query);

    // Jika ada foto baru
    if ($_FILES['photo']['name']) {
        $stmt->bind_param("sdsdi", $nama, $harga, $foto, $desc, $id_game);
    } else {
        $stmt->bind_param("sdsi", $nama, $harga, $desc, $id_game);
    }

    if ($stmt->execute()) {
        echo "<script>
            alert('Game berhasil diperbarui');
            window.location.href = 'listGames.php';
        </script>";
    } else {
        echo "Error: " . $stmt->error;
    }
}

// Tutup koneksi
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Game</title>
    <!-- Link ke file CSS -->
    <link rel="stylesheet" href="../../css/admin/createDataGame.css">
</head>

<body>
    <h1>Update Game</h1>

    <form action="updateGame.php?id=<?php echo $game['id']; ?>" method="POST" enctype="multipart/form-data">
        <label for="nama">Nama:</label>
        <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($game['name']); ?>" required>

        <label for="harga">Harga:</label>
        <input type="number" id="harga" name="harga" value="<?php echo htmlspecialchars($game['price']); ?>" required>

        <label for="desc">Deskripsi:</label>
        <textarea name="desc" id="desc" rows="3" maxlength="50" required><?php echo $game['description']; ?></textarea>

        <label for="photo">Foto Barang:</label>
        <input type="file" id="photo" name="photo">

        <button type="submit" name="update">Update Game</button>
    </form>

    <a href="listGames.php">Kembali ke Daftar Game</a>
</body>

</html>