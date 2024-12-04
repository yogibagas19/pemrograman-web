<?php
session_start();
require_once '../db_connection.php';

// Ensure user is logged in and has admin rights
if (!isset($_SESSION['id_user']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized access");
}

// Establish database connection
$conn = connect_db();

// Initialize variables
$error_message = '';
$success_message = '';
$userId = null;
$user_data = null;

// Validate and sanitize user ID from URL
if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $userId = intval($_GET['id']);
} else {
    $error_message = "Invalid User ID.";
}

// Fetch user data if ID is valid
if ($userId) {
    $query = "SELECT username, fullname FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_data = $result->fetch_assoc();
        $stmt->close();

        if (!$user_data) {
            $error_message = "User not found.";
        }
    } else {
        $error_message = "Database query preparation failed.";
    }
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && $userId) {
    $new_username = trim($_POST['username']);
    $new_fullname = trim($_POST['fullname']);
    $new_password = $_POST['new_password'];

    // Input validation
    if (empty($new_username) || empty($new_fullname)) {
        $error_message = "Username and Full Name cannot be empty!";
    } else {
        // Check if username is already taken by another user
        $check_username_query = "SELECT id FROM users WHERE username = ? AND id != ?";
        $check_username_stmt = $conn->prepare($check_username_query);
        $check_username_stmt->bind_param("si", $new_username, $userId);
        $check_username_stmt->execute();
        $check_username_result = $check_username_stmt->get_result();
        
        if ($check_username_result->num_rows > 0) {
            $error_message = "Username is already in use!";
            $check_username_stmt->close();
        } else {
            $check_username_stmt->close();

            // Prepare update query
            $update_query = "UPDATE users SET username = ?, fullname = ?";
            $bind_types = "ss";
            $bind_params = [&$new_username, &$new_fullname];

            // Handle password update if provided
            if (!empty($new_password)) {
                $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                $update_query .= ", password = ?";
                $bind_types .= "s";
                $bind_params[] = &$hashed_password;
            }

            $update_query .= " WHERE id = ?";
            $bind_types .= "i";
            $bind_params[] = &$userId;

            $update_stmt = $conn->prepare($update_query);
            
            // Dynamic parameter binding
            $bind_param_array = array_merge([$bind_types], $bind_params);
            call_user_func_array([$update_stmt, 'bind_param'], $bind_param_array);
            
            if ($update_stmt->execute()) {
                // Update session if current user is editing their own profile
                if ($userId == $_SESSION['id_user']) {
                    $_SESSION['username'] = $new_username;
                    $_SESSION['fullname'] = $new_fullname;
                }
                
                $success_message = "Profile successfully updated!";

                echo "<script>
                alert('User berhasil diperbarui!');
                window.location.href = 'listUsers.php';
                </script>";
            } else {
                $error_message = "Failed to update profile: " . $conn->error;
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
    <link rel="stylesheet" href="../css/admin/editProfileUser.css">
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
        <a href="listUsers.php">List Users</a>
    </div>
    <div class="content">
        
        <?php if ($user_data): ?>
            <form method="post" action="editUser.php?id=<?php echo $userId; ?>">
                <h2>Edit User</h2>
                <label for="username">Username</label>
                <input type="text" id="username" name="username" 
                    value="<?php echo htmlspecialchars($user_data['username']); ?>" required>
                
                <label for="fullname">Full Name</label>
                <input type="text" id="fullname" name="fullname" 
                value="<?php echo htmlspecialchars($user_data['fullname']); ?>" required>
                
                <label for="new_password">New Password (Leave blank if no change)</label>
                <input type="password" id="new_password" name="new_password">
                
                <?php if ($error_message): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>
                
                <?php if ($success_message): ?>
                    <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
                <?php endif; ?>
                <button type="submit">Update Profile</button>
            </form>
        <?php else: ?>
            <div class="error-message">Unable to load user data. Please check the user ID.</div>
        <?php endif; ?>
    </div>
</body>
</html>