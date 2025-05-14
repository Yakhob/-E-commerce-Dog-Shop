<?php
session_start();
include('../config/database.php');

// Fetch product details based on product_id
if (isset($_GET['id'])) {
    $product_id = intval($_GET['id']);
    $sql = "SELECT name, description, price, product_image, stock_quantity, category FROM products WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        echo "<p>Product not found.</p>";
        exit();
    }
} else {
    echo "<p>No product selected.</p>";
    exit();
}

// Handle Add to Cart
if (isset($_GET['add_to_cart'])) {
    $user_id = $_SESSION['user_id']; // Assuming user is logged in and user_id is stored in session

    $check_sql = "SELECT * FROM carts WHERE product_id = ? AND user_id = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("ii", $product_id, $user_id);
    $stmt->execute();
    $cart_result = $stmt->get_result();

    if ($cart_result->num_rows == 0) {
        $insert_sql = "INSERT INTO carts (product_id, user_id) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("ii", $product_id, $user_id);
        $stmt->execute();
        echo "<script>alert('Product added to cart!'); window.location.href='product_detail.php?id=$product_id';</script>";
    } else {
        echo "<script>alert('Product is already in your cart.'); window.location.href='product_detail.php?id=$product_id';</script>";
    }
}

// Fetch similar products
$category = $product['category'];
$similar_products_sql = "SELECT product_id, name, price, product_image FROM products WHERE category = ? AND product_id != ? LIMIT 8";
$stmt = $conn->prepare($similar_products_sql);
$stmt->bind_param("si", $category, $product_id);
$stmt->execute();
$similar_products = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Details</title>
    <link rel="stylesheet" href="../assets/css/product_detail.css">
</head>
<body>
    

    <div class="container">
    <a href="products.php" class="back-button">Back to Products</a>
        <div class="product-box">
            <img src="../assets/images/<?php echo htmlspecialchars($product['product_image']); ?>" alt="Product Image" class="product-image">
            <div class="product-info">
                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                <p><strong>Description:</strong> <?php echo htmlspecialchars($product['description']); ?></p>
                <p><strong>Price:</strong> $<?php echo htmlspecialchars($product['price']); ?></p>
                <p><strong>Stock Quantity:</strong> <?php echo htmlspecialchars($product['stock_quantity']); ?> units</p>

                <div class="action-buttons">
                    <a href="product_detail.php?id=<?php echo $product_id; ?>&add_to_cart=1" class="button">Add to Cart</a>
                    <a href="buy_product.php?id=<?php echo $product_id; ?>" class="button">Buy Now</a>
                </div>
            </div>
        </div>

        <h3 class="similar-heading">View Similar Products</h3>
        <div class="similar-products">
            <?php while ($similar_product = $similar_products->fetch_assoc()): ?>
                <div class="product-card">
                    <a href="product_detail.php?id=<?php echo $similar_product['product_id']; ?>">
                        <img src="../assets/images/<?php echo htmlspecialchars($similar_product['product_image']); ?>" alt="<?php echo htmlspecialchars($similar_product['name']); ?>">
                    </a>
                    <div class="card-info">
                        <h4><?php echo htmlspecialchars($similar_product['name']); ?></h4>
                        <p>$<?php echo htmlspecialchars($similar_product['price']); ?></p>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>
