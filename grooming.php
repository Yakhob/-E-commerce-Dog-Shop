<?php
include('../config/database.php');
session_start();
include('../shop/user_header.php');

if (!isset($_SESSION['user_id'])) {
    echo "<p>Please log in to book grooming services.</p>";
    exit();
}

$user_id = $_SESSION['user_id'];

// Search functionality
$search_query = '';
if (isset($_GET['search'])) {
    $search_query = htmlspecialchars($_GET['search']);
}

// Fetch grooming services
$sql = "SELECT service_id, service_name, description, price, service_image 
        FROM grooming_services 
        WHERE availability > 0 AND service_name LIKE ?";
$stmt = $conn->prepare($sql);
$search_pattern = '%' . $search_query . '%';
$stmt->bind_param("s", $search_pattern);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grooming Services</title>
    <link rel="stylesheet" href="../assets/css/grooming.css">
</head>
<body>
    <div class="container">
        <h2>Grooming Services</h2>

        <!-- Search Bar -->
        <form class="search-bar" method="GET" action="">
            <input type="text" name="search" placeholder="Search for a service..." value="<?php echo $search_query; ?>">
            <button type="submit">Search</button>
        </form>

        <!-- Display grooming services -->
        <div class="services">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $service_image = $row['service_image'] ? "../assets/images/" . htmlspecialchars($row['service_image']) : "../assets/images/default.jpg";
                    echo "<div class='service'>
                            <img src='" . $service_image . "' alt='" . htmlspecialchars($row['service_name']) . "' class='service-image'>
                            <div class='service-info'>
                                <h3>" . htmlspecialchars($row['service_name']) . "</h3>
                                <p>" . htmlspecialchars($row['description']) . "</p>
                                <p><strong>Price:</strong> $" . htmlspecialchars($row['price']) . "</p>
                            </div>
                            <a href='book_service.php?service_id=" . htmlspecialchars($row['service_id']) . "&user_id=" . htmlspecialchars($user_id) . "' class='button'>Book Now</a>
                          </div>";
                }
            } else {
                echo "<p>No grooming services available at the moment.</p>";
            }
            ?>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>
