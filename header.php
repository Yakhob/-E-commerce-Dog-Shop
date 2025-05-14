<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/header.css">
    <title>Wagging Wonders</title>
</head>
<body>
    <header class="main-header">
        <div class="logo">
            <a href="index.php">
                <img src="assets/images/logo1.png" alt="Wagging Wonders Logo" class="logo-img">
                <span class="logo-text">Wagging Wonders</span>
            </a>
        </div>
        <nav class="navbar">
            <ul>
                <?php if ($isLoggedIn): ?>
                    <li><a href="shop/profile.php">Profile</a></li>
                    <li><a href="shop/products.php">View Products</a></li>
                    <li><a href="shop/grooming.php">Grooming Services</a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
</body>
</html>
