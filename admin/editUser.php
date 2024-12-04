<?php
session_start();
include '../db_connection.php';
$conn = connect_db();

// Cek apakah pengguna sudah login sebagai admin
if (!isset($_SESSION['username']) || $_SESSION['id_role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// Variabel untuk menyimpan pesan error atau sukses
$error = '';
$success = '';

// Ambil ID user yang akan diedit dari parameter URL
$edit_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Cek apakah ID valid
if ($edit_id <= 0) {
    $_SESSION['error_message'] = "Invalid user ID.";
    header("Location: listUsers.php");
    exit;
}

// Ambil data user yang akan diedit
$user = null;
if ($edit_id > 0) {
    $stmt = $conn->prepare("SELECT id, username, fullname, id_role FROM users WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user) {
        $_SESSION['error_message'] = "User not found.";
        header("Location: listUsers.php");
        exit;
    }
}

// Proses form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validasi input
    $fullname = trim($_POST['fullname']);
    $role = trim($_POST['role']);

    // Cek apakah yang sedang diedit adalah user admin yang sedang login
    $is_current_admin = ($_SESSION['id'] == $edit_id);

    // Validasi password hanya jika sedang edit admin sendiri
    if ($is_current_admin && !empty($_POST['new_password'])) {
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if ($new_password !== $confirm_password) {
            $_SESSION['error_message'] = "Password and Confirm Password do not match.";
            header("Location: editUser.php?id=" . $edit_id);
            exit;
        } else {
            // Hash password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update dengan password
            $stmt = $conn->prepare("UPDATE users SET fullname = ?, id_role = ?, password = ? WHERE id = ?");
            $stmt->bind_param("sssi", $fullname, $role, $hashed_password, $edit_id);
        }
    } else {
        // Update tanpa password
        $stmt = $conn->prepare("UPDATE users SET fullname = ?, id_role = ? WHERE id = ?");
        $stmt->bind_param("ssi", $fullname, $role, $edit_id);
    }

    // Eksekusi query
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "User updated successfully.";
        header("Location: listUsers.php");
        exit;
    } else {
        $_SESSION['error_message'] = "Failed to update user: " . $stmt->error;
        header("Location: listUsers.php");
        exit;
    }
}

// Jika tidak ada proses POST, tampilkan form
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
</head>
<body>
    <h1>Edit User</h1>
    <?php if ($user): ?>
    <form method="POST" action="">
        <table>
            <tr>
                <td>Username:</td>
                <td><?= htmlspecialchars($user['username']) ?> (Cannot be changed)</td>
            </tr>
            <tr>
                <td>Fullname:</td>
                <td><input type="text" name="fullname" value="<?= htmlspecialchars($user['fullname']) ?>" required></td>
            </tr>
            <tr>
                <td>Role:</td>
                <td>
                    <select name="role">
                        <option value="admin" <?= $user['id_role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                        <option value="user" <?= $user['id_role'] == 'user' ? 'selected' : '' ?>>User</option>
                    </select>
                </td>
            </tr>
            
            <?php 
            // Tampilkan field password hanya jika sedang edit admin sendiri
            if ($_SESSION['id'] == $edit_id): ?>
            <tr>
                <td>New Password:</td>
                <td><input type="password" name="new_password"></td>
            </tr>
            <tr>
                <td>Confirm Password:</td>
                <td><input type="password" name="confirm_password"></td>
            </tr>
            <?php endif; ?>
            
            <tr>
                <td colspan="2">
                    <input type="submit" value="Update User">
                    <a href="listUsers.php">Back to User List</a>
                </td>
            </tr>
        </table>
    </form>
    <?php endif; ?>
</body>
</html>

<?php $conn->close(); ?>