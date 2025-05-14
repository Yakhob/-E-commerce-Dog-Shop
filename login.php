<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include('config/database.php');
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Fetch user details from the database
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role']; // Save user role in session

            // Redirect based on role
            if ($user['role'] == 'admin') {
                header("Location: ../dog_shop/admin/manage_dogs.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "No account found with that email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            background-image: url("C:\Users\jacob\Downloads\add.jpg");
            background-size: cover;
            background-repeat: no-repeat;
            font-family: Arial, sans-serif;
            height: 100vh;
            margin: 0;
        }

        .login-container {
            width: 400px;
            background-color: rgba(255, 255, 255, 0.8);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin: 150px auto;
            text-align: center;
        }

        .login-container h2 {
            margin-bottom: 20px;
            color: #333;
        }

        .login-form {
            margin: 20px 0;
        }

        .login-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
            text-align: left;
        }

        .login-form input[type="email"],
        .login-form input[type="password"] {
            width: 95%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        .login-form input[type="submit"] {
            background-color: #0de12d;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }

        .login-form input[type="submit"]:hover {
            background-color: green;
        }

        .error {
            color: red;
            font-size: 14px;
        }

        .login-container p {
            margin-top: 15px;
        }

        .login-container a {
            color: darkcyan;
            text-decoration: none;
        }

        .login-container a:hover {
            text-decoration: none;
            color: blue;
        }

    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
        <form method="POST" action="" class="login-form">
            <label for="email">Email:</label>
            <input type="email" name="email" required><br><br>
            <label for="password">Password:</label>
            <input type="password" name="password" required><br><br>
            <input type="submit" value="Login" class=" login-button">
        </form>
        <p>Don't have an account? <a href="register.php">Sign up</a></p>
    </div>
</body>
</html>
