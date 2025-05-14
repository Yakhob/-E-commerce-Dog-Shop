<?php
session_start();
include('config/database.php'); // Adjust path

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<p>You need to log in to view your payment bill. <a href='login.php'>Login here</a></p>";
    exit();
}

// Check if a dog_id is provided
if (isset($_GET['dog_id']) && is_numeric($_GET['dog_id'])) {
    $dog_id = intval($_GET['dog_id']);
    $user_id = $_SESSION['user_id'];

    // Fetch purchase and dog details
    $stmt = $conn->prepare("
        SELECT d.name, d.breed, d.price, p.payment_method, p.purchase_date 
        FROM purchase_dog p 
        JOIN dogs d ON p.dog_id = d.dog_id 
        WHERE p.dog_id = ? AND p.user_id = ?
    ");
    $stmt->bind_param("ii", $dog_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $purchase = $result->fetch_assoc();
    } else {
        echo "<p>No purchase record found for this dog. <a href='index.php'>Go back to Home</a></p>";
        exit();
    }
} else {
    echo "<p>Invalid request. <a href='index.php'>Go back to Home</a></p>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/dog_payment_bill.css">
    <title>Payment Confirmation</title>
</head>
<body>
    <div class="bill-container">
        <h2>Payment Confirmation</h2>
        <p><strong>Dog Name:</strong> <?php echo htmlspecialchars($purchase['name']); ?></p>
        <p><strong>Breed:</strong> <?php echo htmlspecialchars($purchase['breed']); ?></p>
        <p><strong>Price:</strong> $<?php echo htmlspecialchars($purchase['price']); ?></p>
        <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($purchase['payment_method']); ?></p>
        <p><strong>Purchase Date:</strong> <?php echo htmlspecialchars($purchase['purchase_date']); ?></p>
        <p>Thank you for your purchase! If you have any issues, feel free to contact us.</p>
        <a href="index.php">Return to Home</a>
    </div>
</body>
</html>
