<?php
session_start();
require_once '../db_connection.php';

// Cek apakah user sudah login
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit();
}

$conn = connect_db();
$user_id = $_SESSION['id_user'];
$error_message = '';
$success_message = '';

// Ambil data user saat ini
$query = "SELECT username, fullname FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$stmt->close();

// Proses update profil
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_username = trim($_POST['username']);
    $new_fullname = trim($_POST['fullname']);
    $new_password = $_POST['new_password'];

    // Validasi input
    if (empty($new_username) || empty($new_fullname)) {
        $error_message = "Username dan Nama Lengkap tidak boleh kosong!";
    } else {
        // Cek apakah username sudah digunakan
        $check_username_query = "SELECT id FROM users WHERE username = ? AND id != ?";
        $check_username_stmt = $conn->prepare($check_username_query);
        $check_username_stmt->bind_param("si", $new_username, $user_id);
        $check_username_stmt->execute();
        $check_username_result = $check_username_stmt->get_result();
        
        if ($check_username_result->num_rows > 0) {
            $error_message = "Username sudah digunakan!";
            $check_username_stmt->close();
        } else {
            $check_username_stmt->close();

            // Update data profil
            $update_query = "UPDATE users SET username = ?, fullname = ?";
            $bind_types = "ss";
            $bind_params = [&$new_username, &$new_fullname];

            // Jika password baru diisi
            if (!empty($new_password)) {
                // Hash password baru
                $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                $update_query .= ", password = ?";
                $bind_types .= "s";
                $bind_params[] = &$hashed_password;
            }

            $update_query .= " WHERE id = ?";
            $bind_types .= "i";
            $bind_params[] = &$user_id;

            $update_stmt = $conn->prepare($update_query);
            
            // Binding dinamis
            $bind_param_array = array_merge([$bind_types], $bind_params);
            call_user_func_array([$update_stmt, 'bind_param'], $bind_param_array);
            
            if ($update_stmt->execute()) {
                // Update session jika username berubah
                $_SESSION['username'] = $new_username;
                $_SESSION['fullname'] = $new_fullname;
                
                $success_message = "Profil berhasil diperbarui!";
            } else {
                $error_message = "Gagal memperbarui profil: " . $conn->error;
            }
            $update_stmt->close();
        }
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil</title>
    <link rel="stylesheet" href="../css/user/editProfileUser.css">
</head>
<body>
<div class="navbar">
        <h1>GameStore</h1>
        <ul>
            <li><a href="gameList.php">Game List</a></li>
            <li><a href="userHistory.php">Riwayat Pembelian</a></li>
            <li><a href="editProfileUser.php" class="posisi">Edit Profile</a></li>
            <li><a href="../logout.php" >Logout</a></li>
        </ul>
    </div>
    <div class="container">
        <!-- Tautan untuk kembali -->
        <!-- <div class="back-link">
            <a href="gameList.php">← back</a>
        </div> -->
        
        <form method="post" action="editProfileUser.php">
            <h2>Edit Profil</h2>
            
            <?php if (!empty($error_message)): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success_message)): ?>
                <div class="success-message"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <div class="input-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" 
                       value="<?php echo htmlspecialchars($user_data['username']); ?>" required>
            </div>
            
            <div class="input-group">
                <label for="fullname">Nama Lengkap</label>
                <input type="text" id="fullname" name="fullname" 
                       value="<?php echo htmlspecialchars($user_data['fullname']); ?>" required>
            </div>
            
            <div class="input-group">
                <label for="new_password">Password Baru (Kosongkan jika tidak ingin mengubah)</label>
                <input type="password" id="new_password" name="new_password">
            </div>
            
            <button type="submit">Perbarui Profil</button>
        </form>
    </div>
    <script>
  // Mendapatkan semua link dalam navbar
  const navLinks = document.querySelectorAll('.navbar a');

  // Menambahkan event listener pada setiap link
  navLinks.forEach(link => {
    // Cek jika URL saat ini sama dengan href dari link
    if (window.location.href.includes(link.href)) {
      link.classList.add('active'); // Tambahkan kelas 'active' jika cocok
    }
  });
</script>
</body>
</html>
