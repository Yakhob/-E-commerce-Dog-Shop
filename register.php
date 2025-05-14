<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include('config/database.php');
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $mobile = $_POST['mobile'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $address = $_POST['address'];  // New field for address

    // Validate password confirmation
    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // SQL query to check if email already exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            // SQL query to insert new user with address field
            $stmt = $conn->prepare("INSERT INTO users (username, email, mobile, password, address) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $username, $email, $mobile, $hashed_password, $address);
            
            if ($stmt->execute()) {
                $_SESSION['user_id'] = $conn->insert_id;
                $_SESSION['username'] = $username;
                header("Location: ../dog_shop/shop/profile.php");
                exit();
            } else {
                $error = "Registration failed. Please try again.";
            }
        } else {
            $error = "Email already exists. Please use another email.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/style.css">
    <title>User Registration</title>
    <style>
        body {
            background-color: #ffffff; /* Set background color to white */
            font-family: Arial, sans-serif;
            height: 100vh;
            margin: 0;
        }

        .register-container {
            width: 500px;
            background-color: rgba(255, 255, 255, 0.9); /* Slight transparency */
            padding: 10px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin: 30px auto;
            text-align: center;
        }

        .register-container h2 {
            margin-bottom: 20px;
            color: #333;
        }

        .register-form {
            margin: 20px 0;
        }

        .register-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
            text-align: left;
            margin-left: 20px;
        }

        .register-form input[type="text"],
        .register-form input[type="email"],
        .register-form input[type="password"] {
            width: 89%;
            margin-left: 20px;
            padding: 10px;
            margin-bottom: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        .register-form button {
            background-color: #0de12d;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 92%;
            margin-left: 20px;
        }

        .register-form button:hover {
            background-color: green;
        }

        .error {
            color: red;
            font-size: 14px;
        }

        .register-container a {
            color: darkcyan;
            text-decoration: none;
        }

        .register-container a:hover {
            text-decoration: none;
            color: blue;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>User Registration</h2>
        <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
        <form method="POST" action="" class="register-form">
            <label for="username">Username:</label>
            <input type="text" name="username" required><br><br>
            <label for="email">Email:</label>
            <input type="email" name="email" required><br><br>
            <label for="mobile">Mobile Number:</label>
            <input type="text" name="mobile" required><br><br>
            <label for="password">Password:</label>
            <input type="password" name="password" required><br><br>
            <label for="confirm_password">Confirm Password:</label>
            <input type="password" name="confirm_password" required><br><br>
            <label for="address">Address:</label> <!-- New address field -->
            <input type="text" name="address" required><br><br>
            <button type="submit">Register</button>
        </form>
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
</body>
</html>
