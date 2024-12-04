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
    $desc = $_POST['desc']; // Ambil nilai deskripsi

    // Debugging: Periksa apakah nilai deskripsi diterima dengan benar
    // echo "<script>console.log('Deskripsi: $desc');</script>"; // Bisa gunakan ini untuk debug di browser

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
            position: sticky;
            top: 0;
            z-index: 1000;
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
            height: calc(100% - 60px);
            background-color: #34495e;
            color: #ecf0f1;
            padding: 20px 10px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            z-index: 500;
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
            padding-top: 50px;
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
        }

        .content h1 {
            font-size: 2em;
            margin-bottom: 20px;
            justify-self: center;
        }

        /* Form Styles */
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
        input[type="file"],
        textarea {
            width: 93%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #444;
            border-radius: 5px;
            background-color: #2c353f;
            color: #fff;
            font-size: 14px;
        }

        input:focus,
        textarea:focus {
            border-color: #00f7ff;
            outline: none;
        }

        textarea {
            resize: none;
            height: 80px;
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

        /* Responsive Design */
        @media screen and (max-width: 768px) {
            .navbar {
                flex-direction: column;
                align-items: flex-start;
            }

            .sidebar {
                width: 100%;
                height: auto;
                position: static;
                box-shadow: none;
            }

            .content {
                margin-left: 0;
                padding: 20px;
            }
        }

    </style>
</head>
<body>
<div class="navbar">
        <div class="user-info">
        <span>Welcome, <?php echo htmlspecialchars(ucfirst($_SESSION['username'])); ?></span>
        </div>
        <a href="../logout.php" class="logout-btn">Logout</a>
    </div>

    <!-- Sidebar -->
    <div class="sidebar">
        <a href="dashboardAdmin.php">Dashboard</a>
        <a href="listGames.php">List Games</a>
    </div>

    <div class="content">
        <form action="editGame.php?id=<?php echo $game['id']; ?>" method="POST" enctype="multipart/form-data">
            <h1>Edit Game</h1>
            <label for="nama">Nama:</label>
            <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($game['name']); ?>" required>

            <label for="harga">Harga:</label>
            <input type="number" id="harga" name="harga" value="<?php echo htmlspecialchars($game['price']); ?>" required>

            <label for="desc">Deskripsi:</label>
            <textarea name="desc" id="desc" rows="3" required><?php echo htmlspecialchars($game['description']); ?></textarea>

            <label for="photo">Foto Barang:</label>
            <input type="file" id="photo" name="photo">

            <button type="submit" name="update">Update Game</button>
        </form>
    </div>
</body>

</html>