<?php
session_start();
include('config/database.php'); // Adjusted path

// Check if a specific dog_id is provided in the URL
if (isset($_GET['dog_id']) && is_numeric($_GET['dog_id'])) {
    $dog_id = intval($_GET['dog_id']);

    // Fetch details of the specific dog
    $stmt = $conn->prepare("SELECT dog_id, name, breed, age, price, description, image FROM dogs WHERE dog_id = ?");
    $stmt->bind_param("i", $dog_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the dog exists
    if ($result->num_rows === 0) {
        echo "<p>Dog not found. <a href='index.php'>Go back to Home</a></p>";
        exit();
    }
} else {
    echo "<p>No dog selected. <a href='index.php'>Go back to Home</a></p>";
    exit();
}

// Fetch the dog's details
$dog = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/browse.css">
    <title><?php echo htmlspecialchars($dog['name']); ?> - Details</title>
</head>
<body>
    <!-- Outer box wrapping the entire content -->
    <div class="browse-container">
        <!-- Dog name -->
        <h2><?php echo htmlspecialchars($dog['name']); ?></h2>

        <!-- Inner box with image and details -->
        <div class="dog-detail">
            <img src="<?php echo 'assets/images/' . htmlspecialchars($dog['image']); ?>" alt="<?php echo htmlspecialchars($dog['name']); ?>">
            <div class="info">
                <p><strong>Breed:</strong> <?php echo htmlspecialchars($dog['breed']); ?></p>
                <p><strong>Age:</strong> <?php echo htmlspecialchars($dog['age']); ?> years</p>
                <p><strong>Price:</strong> $<?php echo htmlspecialchars($dog['price']); ?></p>
                <p><strong>Description:</strong> <?php echo htmlspecialchars($dog['description']); ?></p>
                <!-- Buy Now button inside the inner box -->
                <a href="dog_purchase.php?dog_id=<?php echo $dog['dog_id']; ?>" class="buy-now">Buy Now</a>
            </div>
        </div>

        <!-- Go back to home link -->
        <a href="index.php" class="home" >Go Back to Home</a>
    </div>
</body>
</html>
