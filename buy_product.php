<?php
session_start();
include('../config/database.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<p>You need to log in to purchase a product. <a href='login.php'>Login here</a></p>";
    exit();
}

// Fetch product details
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $product_id = intval($_GET['id']);
    $sql = "SELECT name, price, stock_quantity FROM products WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        echo "<p>Product not found. <a href='products.php'>Back to Products</a></p>";
        exit();
    }
} else {
    echo "<p>No product selected. <a href='products.php'>Back to Products</a></p>";
    exit();
}

// Handle purchase
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id']; // Assume user_id is stored in session
    $quantity = intval($_POST['quantity']);
    $purchase_method = $_POST['purchase_method'];
    $total_price = $quantity * $product['price'];
    $purchase_date = date('Y-m-d H:i:s');

    // Check stock availability
    if ($quantity > $product['stock_quantity']) {
        echo "<script>alert('Insufficient stock!'); window.location.href='buy_product.php?id=$product_id';</script>";
        exit();
    }

    // Insert purchase into purchases table
    $insert_sql = "INSERT INTO purchases (user_id, product_id, quantity, total_price, purchase_date, purchase_method) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("iiidss", $user_id, $product_id, $quantity, $total_price, $purchase_date, $purchase_method);
    $stmt->execute();

    // Update stock_quantity in products table
    $update_stock_sql = "UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?";
    $stmt = $conn->prepare($update_stock_sql);
    $stmt->bind_param("ii", $quantity, $product_id);
    $stmt->execute();

    // Insert purchase message into messages table
    $message = "You have successfully purchased the product!";
    $message_type = 'product_purchase';
    $timestamp = date('Y-m-d H:i:s');

    $insert_message_sql = "INSERT INTO messages (user_id, message, type, timestamp, related_id) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_message_sql);
    $stmt->bind_param("isssi", $user_id, $message, $message_type, $timestamp, $product_id);
    $stmt->execute();

    // Redirect to payment bill page
    echo "<script>alert('Payment successful!'); window.location.href='payment_bill.php?id=$product_id&quantity=$quantity';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buy Product</title>
    <link rel="stylesheet" href="../assets/css/buy_product.css">
    <script>
        // JavaScript to calculate total price dynamically
        function updateTotalPrice() {
            const pricePerUnit = parseFloat(document.getElementById('pricePerUnit').value);
            const quantity = parseInt(document.getElementById('quantity').value);
            const totalPrice = pricePerUnit * quantity;
            document.getElementById('totalPrice').innerText = totalPrice.toFixed(2);
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>Buy Product</h2>
        
        <!-- Product Details -->
        <div class="product-detail">
            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
            <p><strong>Price (per unit):</strong> $<?php echo htmlspecialchars($product['price']); ?></p>
            <p><strong>Stock Available:</strong> <?php echo htmlspecialchars($product['stock_quantity']); ?> units</p>
        </div>

        <!-- Purchase Form -->
        <form method="POST">
            <input type="hidden" id="pricePerUnit" value="<?php echo $product['price']; ?>">

            <label for="quantity">Select Quantity:</label>
            <select name="quantity" id="quantity" onchange="updateTotalPrice()" required>
                <?php
                for ($i = 1; $i <= $product['stock_quantity']; $i++) {
                    echo "<option value='$i'>$i</option>";
                }
                ?>
            </select>

            <p><strong>Total Price:</strong> $<span id="totalPrice"><?php echo $product['price']; ?></span></p>

            <label for="purchase_method">Payment Method:</label>
            <select name="purchase_method" id="purchase_method" required>
                <option value="Credit Card">Credit Card</option>
                <option value="Debit Card">Debit Card</option>
                <option value="PayPal">PayPal</option>
                <option value="Cash on Delivery">Cash on Delivery</option>
            </select>

            <button type="submit" class="button">Confirm Payment</button>
        </form>

        <a href="products.php" class="button">Back to Products</a>
    </div>
</body>
</html>

<?php
$conn->close();
?>
