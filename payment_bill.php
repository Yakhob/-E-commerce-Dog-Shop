<?php
session_start();
include('../config/database.php');

// Fetch purchase details
if (isset($_GET['id']) && isset($_GET['quantity'])) {
    $product_id = intval($_GET['id']);
    $quantity = intval($_GET['quantity']);
    $user_id = $_SESSION['user_id']; // Assume user_id is stored in the session

    // Fetch product details
    $product_sql = "SELECT name, price FROM products WHERE product_id = ?";
    $stmt = $conn->prepare($product_sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product_result = $stmt->get_result();

    if ($product_result->num_rows > 0) {
        $product = $product_result->fetch_assoc();
    } else {
        echo "<p>Product not found.</p>";
        exit();
    }

    // Calculate total price
    $total_price = $product['price'] * $quantity;

    // Fetch purchase details from the database
    $purchase_sql = "SELECT purchase_date, purchase_method FROM purchases WHERE user_id = ? AND product_id = ? ORDER BY purchase_date DESC LIMIT 1";
    $stmt = $conn->prepare($purchase_sql);
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $purchase_result = $stmt->get_result();

    if ($purchase_result->num_rows > 0) {
        $purchase = $purchase_result->fetch_assoc();
    } else {
        echo "<p>Purchase details not found.</p>";
        exit();
    }
} else {
    echo "<p>Invalid request.</p>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Bill</title>
    <link rel="stylesheet" href="../assets/css/payment_bill.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: url('../assets/images/eg.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        input, button {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Payment Bill</h2>
        
        <!-- Bill Details -->
        <div class="bill">
            <h3>Thank you for your purchase!</h3>
            
            <div class="bill-section">
                <strong>Product Name:</strong> <?php echo htmlspecialchars($product['name']); ?>
            </div>
            <div class="bill-section">
                <strong>Price (per unit):</strong> $<?php echo htmlspecialchars($product['price']); ?>
            </div>
            <div class="bill-section">
                <strong>Quantity:</strong> <?php echo htmlspecialchars($quantity); ?>
            </div>
            <div class="bill-section">
                <strong>Total Price:</strong> $<?php echo htmlspecialchars($total_price); ?>
            </div>
            <div class="bill-section">
                <strong>Purchase Date:</strong> <?php echo htmlspecialchars($purchase['purchase_date']); ?>
            </div>
            <div class="bill-section">
                <strong>Payment Method:</strong> <?php echo htmlspecialchars($purchase['purchase_method']); ?>
            </div>
        </div>
        
        <!-- Back to Products -->
        <a href="products.php" class="button">Back to Products</a>
    </div>
</body>
</html>

<?php
$conn->close();
?>
