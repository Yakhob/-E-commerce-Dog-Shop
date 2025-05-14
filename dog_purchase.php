<?php
session_start();
include('config/database.php'); // Adjusted path

if (!isset($_SESSION['user_id'])) {
    echo "<p>You need to log in to purchase a dog. <a href='login.php'>Login here</a></p>";
    exit();
}

if (isset($_GET['dog_id']) && is_numeric($_GET['dog_id'])) {
    $dog_id = intval($_GET['dog_id']);
    $user_id = $_SESSION['user_id'];

    // Fetch dog details
    $stmt = $conn->prepare("SELECT name, breed, price, is_available FROM dogs WHERE dog_id = ?");
    $stmt->bind_param("i", $dog_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $dog = $result->fetch_assoc();

        // Check if the dog is already unavailable
        if ($dog['is_available'] == 1) {
            echo "<p>Sorry, this dog is no longer available. <a href='index.php'>Go back to Home</a></p>";
            exit();
        }

        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $payment_method = $_POST['payment_method'];

            // Insert purchase record
            $insertStmt = $conn->prepare("INSERT INTO purchase_dog (dog_id, user_id, payment_method) VALUES (?, ?, ?)");
            $insertStmt->bind_param("iis", $dog_id, $user_id, $payment_method);

            if ($insertStmt->execute()) {
                // Mark dog as unavailable
                $updateStmt = $conn->prepare("UPDATE dogs SET is_available = 1 WHERE dog_id = ?");
                $updateStmt->bind_param("i", $dog_id);
                $updateStmt->execute();

                // Insert message into messages table
                $message = "You have successfully purchased " . htmlspecialchars($dog['name']) . "!";
                $type = 'dog_purchase';
                $msgStmt = $conn->prepare("INSERT INTO messages (user_id, message, type) VALUES (?, ?, ?)");
                $msgStmt->bind_param("iss", $user_id, $message, $type);
                $msgStmt->execute();

                // Redirect to payment bill page
                header("Location: dog_payment_bill.php?dog_id=$dog_id");
                exit();
            } else {
                echo "<p>Failed to process your purchase. Please try again later.</p>";
            }
        }
    } else {
        echo "<p>Dog not found. <a href='index.php'>Go back to Home</a></p>";
    }
} else {
    echo "<p>Invalid request. <a href='index.php'>Go back to Home</a></p>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/dog_purchase.css">
    <title>Purchase <?php echo htmlspecialchars($dog['name']); ?></title>
</head>
<body>
    <div class="purchase-container">
        <h2>Confirm Purchase for <?php echo htmlspecialchars($dog['name']); ?></h2>
        <p><strong>Breed:</strong> <?php echo htmlspecialchars($dog['breed']); ?></p>
        <p><strong>Price:</strong> $<?php echo htmlspecialchars($dog['price']); ?></p>
        <form method="post">
            <label for="payment_method">Enter Payment Method:</label>
            <input type="text" name="payment_method" id="payment_method" placeholder="e.g., GPay, Paytm, PhonePe" required>
            <button type="submit">Confirm Payment</button>
        </form>
        <a href="index.php">Cancel</a>
    </div>
</body>
</html>
