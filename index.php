<?php
session_start();
include('config/database.php');
include('includes/functions.php');

// Check if the user is logged in
$isLoggedIn = isLoggedIn();

// Pagination and search setup
$limit = 8; // Number of dogs to display per page
$page = isset($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1; // Current page number
$offset = ($page - 1) * $limit; // Offset for SQL query

$search_breed = isset($_GET['search_breed']) ? htmlspecialchars($_GET['search_breed']) : '';

// Prepare query to fetch available dogs randomly
if ($search_breed) {
    $like_breed = '%' . $search_breed . '%';
    $total_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM dogs WHERE breed LIKE ? AND is_available = 0");
    $total_stmt->bind_param("s", $like_breed);

    $stmt = $conn->prepare("SELECT dog_id, name, breed, age, price, image 
                            FROM dogs 
                            WHERE breed LIKE ? AND is_available = 0 
                            ORDER BY RAND() LIMIT ? OFFSET ?");
    $stmt->bind_param("sii", $like_breed, $limit, $offset);
} else {
    $total_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM dogs WHERE is_available = 0");
    $stmt = $conn->prepare("SELECT dog_id, name, breed, age, price, image 
                            FROM dogs 
                            WHERE is_available = 0 
                            ORDER BY RAND() LIMIT ? OFFSET ?");
    $stmt->bind_param("ii", $limit, $offset);
}

// Fetch total dogs count
$total_stmt->execute();
$total_result = $total_stmt->get_result()->fetch_assoc();
$total_dogs = $total_result['total'];
$total_pages = ceil($total_dogs / $limit);

// Fetch dogs for the current page
$stmt->execute();
$dog_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/index.css">
    <title>Wagging Wonders - Home</title>
</head>
<body>
    <?php include('includes/header.php'); ?>

    <div class="home-container">
        <h1>Welcome to Wagging Wonders!</h1>
        <?php if ($isLoggedIn): ?>
            <p>Hello, <?php echo htmlspecialchars($_SESSION['username']); ?></p>
        <?php endif; ?>

        <!-- Search by Breed -->
        <form method="GET" action="" class="search-form">
            <input type="text" id="search_breed" name="search_breed" placeholder="Enter breed" value="<?php echo $search_breed; ?>">
            <button type="submit">Search</button>
        </form>

        <div class="new-dogs">
            <?php if ($dog_result->num_rows > 0): ?>
                <?php while ($dog = $dog_result->fetch_assoc()): ?>
                    <div class="dog-item">
                        <a href="browse.php?dog_id=<?php echo $dog['dog_id']; ?>">
                            <img src="assets/images/<?php echo htmlspecialchars($dog['image']); ?>" alt="<?php echo htmlspecialchars($dog['name']); ?>">
                        </a>
                        <p><?php echo htmlspecialchars($dog['name']); ?></p>
                        <p>Breed: <?php echo htmlspecialchars($dog['breed']); ?></p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No dogs found for the breed "<?php echo $search_breed; ?>".</p>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>&search_breed=<?php echo urlencode($search_breed); ?>" class="prev">Previous</a>
            <?php endif; ?>
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page + 1; ?>&search_breed=<?php echo urlencode($search_breed); ?>" class="next">Next</a>
            <?php endif; ?>
        </div>
    </div>

    <?php include('includes/footer.php'); ?>
</body>
</html>
