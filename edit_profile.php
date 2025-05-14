<?php
session_start();
include('config/database.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user data from the database
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, email, mobile, address FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $mobile = $_POST['mobile'];
    $address = $_POST['address'];
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

    // Update user information
    if ($password) {
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, mobile = ?, address = ?, password = ? WHERE user_id = ?");
        $stmt->bind_param("sssssi", $username, $email, $mobile, $address, $password, $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, mobile = ?, address = ? WHERE user_id = ?");
        $stmt->bind_param("ssssi", $username, $email, $mobile, $address, $user_id);
    }

    if ($stmt->execute()) {
        $_SESSION['username'] = $username; // Update session
        $success = "Profile updated successfully!";
    } else {
        $error = "Failed to update profile. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/edit_profile.css">
    <title>Edit Profile</title>
</head>
<body>
    <h2 class="edit-profile-heading">Edit Profile</h2>
    <div class="edit-profile-container">
        <?php if (isset($success)) { echo "<p class='success'>$success</p>"; } ?>
        <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
        <form method="POST" action="">
            <label for="username">Username:</label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            <label for="email">Email:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            <label for="mobile">Mobile:</label>
            <input type="text" name="mobile" value="<?php echo htmlspecialchars($user['mobile']); ?>" required>
            <label for="address">Address:</label>
            <textarea name="address" required><?php echo htmlspecialchars($user['address']); ?></textarea>
            <label for="password">New Password (leave blank to keep current password):</label>
            <input type="password" name="password">
            <button type="submit">Update Profile</button>
        </form>
        <a href="shop/profile.php">Back to Profile</a>
    </div>
</body>
</html>
