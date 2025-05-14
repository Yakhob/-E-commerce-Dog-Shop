<?php
session_start();
include('../config/database.php');
include('../shop/user_header.php');

// Pagination setup
$products_per_page = 12;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $products_per_page;

// Search functionality
$search_query = "";
if (isset($_GET['search'])) {
    $search_query = $_GET['search'];
}

// Fetch accessory products with search and pagination, excluding out of stock products
$sql = "SELECT product_id, name, price, product_image FROM products WHERE category = 'accessory' AND name LIKE ? AND stock_quantity > 0 ORDER BY RAND() LIMIT ?, ?";
$stmt = $conn->prepare($sql);
$search_param = "%$search_query%";
$stmt->bind_param("sii", $search_param, $offset, $products_per_page);
$stmt->execute();
$result = $stmt->get_result();

// Fetch total products for pagination
$count_sql = "SELECT COUNT(*) as total FROM products WHERE category = 'accessory' AND name LIKE ? AND stock_quantity > 0";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param("s", $search_param);
$count_stmt->execute();
$count_result = $count_stmt->get_result()->fetch_assoc();
$total_products = $count_result['total'];
$total_pages = ceil($total_products / $products_per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accessories Products</title>
    <link rel="stylesheet" href="../assets/css/products_accessory.css">
</head>
<body>
        <div class="header">
            <h2>Accessories Products</h2>
            <a href="products.php" class="button">Go to Food Products</a>
        </div>
        <div class="container">
        <!-- Search bar -->
        <form method="GET" action="products_accessory.php" class="search-bar">
            <input type="text" name="search" placeholder="Search by product name" value="<?php echo htmlspecialchars($search_query); ?>">
            <button type="submit">Search</button>
        </form>

        <!-- Display accessory products -->
        <div class="products">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<div class='product-card'>
                            <a href='product_detail.php?id={$row['product_id']}'><img src='../assets/images/{$row['product_image']}' alt='{$row['name']}'></a>
                            <div class='product-info'>
                                <h3>{$row['name']}</h3>
                                <p>\${$row['price']}</p>
                                <a href='products_accessory.php?add_to_cart={$row['product_id']}' class='button'>Add to Cart</a>
                            </div>
                          </div>";
                }
            } else {
                echo "<p>No accessory products found.</p>";
            }
            ?>
        </div>

        <!-- Pagination -->
        <div class="pagination">
            <?php
            for ($i = 1; $i <= $total_pages; $i++) {
                echo "<a href='products_accessory.php?page=$i&search=$search_query' class='pagination-link" . ($i === $page ? " active" : "") . "'>$i</a>";
            }
            ?>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>
