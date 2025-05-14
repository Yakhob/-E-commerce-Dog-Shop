<?php
session_start();

include('../config/database.php');
include('../shop/user_header.php');

// Handle add to cart
if (isset($_GET['add_to_cart'])) {
    $product_id = intval($_GET['add_to_cart']);
    $user_id = $_SESSION['user_id']; // Assuming user is logged in and user_id is stored in session

    // Check if the product is already in the cart
    $check_sql = "SELECT * FROM carts WHERE product_id = ? AND user_id = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("ii", $product_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        // Product not in cart, so add it
        $insert_sql = "INSERT INTO carts (product_id, user_id) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("ii", $product_id, $user_id);
        $stmt->execute();
        echo "<script>alert('Product added to cart!'); window.location.href='products.php';</script>";
    } else {
        echo "<script>alert('Product is already in your cart.'); window.location.href='products.php';</script>";
    }
}

// Fetch products with category 'food' and limit 12 for pagination
$limit = 12;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Check if a search term is provided
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Modify SQL query based on search term
if (!empty($search)) {
    $search = "%" . $conn->real_escape_string($search) . "%"; // Escape search term to prevent SQL injection
    $sql = "SELECT product_id, name, price, product_image FROM products WHERE category = 'food' AND name LIKE ? AND stock_quantity > 0 ORDER BY RAND() LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $search, $limit, $offset);
} else {
    $sql = "SELECT product_id, name, price, product_image FROM products WHERE category = 'food' AND stock_quantity > 0 ORDER BY RAND() LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $limit, $offset);
}

$stmt->execute();
$result = $stmt->get_result();

// Get total count for pagination
$count_sql = "SELECT COUNT(*) as total FROM products WHERE category = 'food' AND stock_quantity > 0" . (!empty($search) ? " AND name LIKE '$search'" : "");
$count_result = $conn->query($count_sql);
$total_products = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_products / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Products</title>
    <link rel="stylesheet" href="../assets/css/products.css">
</head>
<body>
   
    <div class="header">
        <h2>Food Products</h2>
        <a href="products_accessory.php" class="button">Go to Accessories Products</a>
    </div>
    <div class="container">
        <!-- Search bar -->
        <form class="search-form" method="GET" action="products.php">
            <input type="text" name="search" placeholder="Search for a product..." 
                   value="<?php echo htmlspecialchars(str_replace('%', '', $search)); ?>">
            <button type="submit">Search</button>
        </form>

        <!-- Display food products -->
        <div class="products">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<div class='product'>
                            <a href='product_detail.php?id={$row['product_id']}'>
                                <img src='../assets/images/{$row['product_image']}' alt='{$row['name']}'>
                            </a>
                            <div class='product-info'>
                                <h3>{$row['name']}</h3>
                                <p>Price: \${$row['price']}</p>
                                <a href='products.php?add_to_cart={$row['product_id']}' class='button'>Add to Cart</a>
                            </div>
                          </div>";
                }
            } else {
                echo "<p>No food products found.</p>";
            }
            ?>
        </div>

        <!-- Pagination -->
        <div class="pagination">
            <?php
            if ($page > 1) {
                echo "<a href='?page=" . ($page - 1) . "&search=" . htmlspecialchars($search) . "' class='prev'>Previous</a>";
            }
            for ($i = 1; $i <= $total_pages; $i++) {
                echo "<a href='?page=$i&search=" . htmlspecialchars($search) . "'" . ($i == $page ? " class='active'" : "") . ">$i</a>";
            }
            if ($page < $total_pages) {
                echo "<a href='?page=" . ($page + 1) . "&search=" . htmlspecialchars($search) . "' class='next'>Next</a>";
            }
            ?>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>
