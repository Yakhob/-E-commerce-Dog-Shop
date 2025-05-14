<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

include('../config/database.php');
include('../shop/user_header.php');


// Fetch user details
$user_id = $_SESSION['user_id'];
$user_query = $conn->prepare("SELECT username, email, mobile, address FROM users WHERE user_id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_details = $user_query->get_result()->fetch_assoc();

// Handle pagination for purchases
$purchase_limit = 3;
$purchase_page = isset($_GET['purchase_page']) ? max(1, intval($_GET['purchase_page'])) : 1;
$purchase_offset = ($purchase_page - 1) * $purchase_limit;

// Fetch purchased products
$purchases_query = $conn->prepare("
    SELECT p.name, p.product_image, p.category, p.price, bp.purchase_date
    FROM purchases bp
    JOIN products p ON bp.product_id = p.product_id
    WHERE bp.user_id = ?
    ORDER BY bp.purchase_date DESC
    LIMIT ?, ?
");
$purchases_query->bind_param("iii", $user_id, $purchase_offset, $purchase_limit);
$purchases_query->execute();
$purchased_products = $purchases_query->get_result();

// Fetch purchased dogs
$dogs_query = $conn->prepare("
    SELECT d.name, d.breed, d.age, d.price, d.image, pd.purchase_date, pd.payment_method
    FROM purchase_dog pd
    JOIN dogs d ON pd.dog_id = d.dog_id
    WHERE pd.user_id = ?
    ORDER BY pd.purchase_date DESC
");
$dogs_query->bind_param("i", $user_id);
$dogs_query->execute();
$purchased_dogs = $dogs_query->get_result();

// Fetch bookings with pagination
$booking_limit = 2;
$booking_page = isset($_GET['booking_page']) ? max(1, intval($_GET['booking_page'])) : 1;
$booking_offset = ($booking_page - 1) * $booking_limit;

$bookings_query = $conn->prepare("
    SELECT gb.booking_date, gb.booking_time, gb.book_status, gs.service_name
    FROM grooming_bookings gb
    JOIN grooming_services gs ON gb.service_id = gs.service_id
    WHERE gb.user_id = ?
    ORDER BY gb.booking_date DESC, gb.booking_time DESC
    LIMIT ?, ?
");
$bookings_query->bind_param("iii", $user_id, $booking_offset, $booking_limit);
$bookings_query->execute();
$bookings = $bookings_query->get_result();

// Fetch cart products (4 most recent)
$cart_query = $conn->prepare("
    SELECT p.name, p.product_image, p.category, p.price, p.product_id
    FROM carts c
    JOIN products p ON c.product_id = p.product_id
    WHERE c.user_id = ?
    ORDER BY c.cart_id DESC
    LIMIT 4
");
$cart_query->bind_param("i", $user_id);
$cart_query->execute();
$cart_items = $cart_query->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="../assets/css/profile.css">
</head>
<body>
    <div class="profile-container">
        <!-- User Details Section -->
        <div class="profile-details">
            <p><strong>Username:</strong> <?php echo htmlspecialchars($user_details['username']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user_details['email']); ?></p>
            <p><strong>Mobile:</strong> <?php echo htmlspecialchars($user_details['mobile']); ?></p>
            <p><strong>Address:</strong> <?php echo htmlspecialchars($user_details['address']); ?></p>
            <a href="messages.php">
    <img src="../assets/images/message.png" alt="Messages" class="message-image">
            </a>
            <a href="../edit_profile.php" class="button">Edit Profile</a>
        </div>

        <!-- Section Navigation -->
        <div class="section-navigation">
            <a href="#cart" class="button">My Cart</a>
            <a href="#purchased-dogs" class="button">Purchased Dogs</a>
            <a href="#bookings" class="button">My Bookings</a>
            <a href="#purchases" class="button">My Purchases</a>
        </div>

        <h3 id="cart">My Cart</h3>
<?php if ($cart_items->num_rows > 0): ?>
    <div class="cart-list">
        <?php while ($cart = $cart_items->fetch_assoc()): ?>
            <div class="cart-item">
                <a href="product_detail.php?id=<?php echo htmlspecialchars($cart['product_id']); ?>">
                    <img src="../assets/images/<?php echo htmlspecialchars($cart['product_image']); ?>" alt="<?php echo htmlspecialchars($cart['name']); ?>">
                </a>
                <div class="cart-item-details">
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($cart['name']); ?></p>
                    <p><strong>Category:</strong> <?php echo htmlspecialchars($cart['category']); ?></p>
                    <p><strong>Price:</strong> $<?php echo htmlspecialchars($cart['price']); ?></p>
                
                <form action="remove_cart_item.php" method="POST">
                    <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($cart['product_id']); ?>">
                    <button type="submit" class="button">Remove</button>
                </form>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <p>No cart items found.</p>
<?php endif; ?>

        <!-- Purchased Dogs Section -->
        <h3 id="purchased-dogs">My Purchased Dogs</h3>
        <?php if ($purchased_dogs->num_rows > 0): ?>
            <div class="dog-list">
                <?php while ($dog = $purchased_dogs->fetch_assoc()): ?>
                    <div class="dog-item">
                        <img src="../assets/images/<?php echo htmlspecialchars($dog['image']); ?>" alt="<?php echo htmlspecialchars($dog['name']); ?>">
                        <div>
                            <p><strong>Name:</strong> <?php echo htmlspecialchars($dog['name']); ?></p>
                            <p><strong>Breed:</strong> <?php echo htmlspecialchars($dog['breed']); ?></p>
                            <p><strong>Age:</strong> <?php echo htmlspecialchars($dog['age']); ?> years</p>
                            <p><strong>Price:</strong> $<?php echo htmlspecialchars($dog['price']); ?></p>
                            <p><strong>Purchase Date:</strong> <?php echo htmlspecialchars($dog['purchase_date']); ?></p>
                            <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($dog['payment_method']); ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>No purchased dogs found.</p>
        <?php endif; ?>

        <!-- Bookings Section -->
        <h3 id="bookings">My Bookings</h3>
        <?php if ($bookings->num_rows > 0): ?>
            <div class="bookings-section">
                <table>
                    <thead>
                        <tr>
                            <th>Service Name</th>
                            <th>Booking Date</th>
                            <th>Booking Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($booking = $bookings->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($booking['service_name']); ?></td>
                                <td><?php echo htmlspecialchars($booking['booking_date']); ?></td>
                                <td><?php echo htmlspecialchars($booking['booking_time']); ?></td>
                                <td><?php echo htmlspecialchars($booking['book_status']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>No bookings found.</p>
        <?php endif; ?>

        <!-- Purchased Products Section -->
        <h3 id="purchases">My Purchases</h3>
        <?php if ($purchased_products->num_rows > 0): ?>
            <div class="product-list">
                <?php while ($product = $purchased_products->fetch_assoc()): ?>
                    <div class="product-item">
                        <img src="../assets/images/<?php echo htmlspecialchars($product['product_image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <div>
                            <p><strong>Name:</strong> <?php echo htmlspecialchars($product['name']); ?></p>
                            <p><strong>Category:</strong> <?php echo htmlspecialchars($product['category']); ?></p>
                            <p><strong>Price:</strong> $<?php echo htmlspecialchars($product['price']); ?></p>
                            <p><strong>Purchase Date:</strong> <?php echo htmlspecialchars($product['purchase_date']); ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>No purchases found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
