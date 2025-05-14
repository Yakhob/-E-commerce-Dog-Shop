<?php
session_start();
include('../config/database.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../user/login.php");
    exit();
}

// Get the grooming service ID from the URL
if (isset($_GET['id'])) {
    $service_id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT service_name, description, price FROM grooming_services WHERE service_id = ?");
    $stmt->bind_param("i", $service_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $service = $result->fetch_assoc();
    } else {
        echo "Service not found.";
        exit();
    }
} else {
    echo "Service not found.";
    exit();
}

// Handle booking form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $booking_date = htmlspecialchars($_POST['booking_date']);
    $booking_time = htmlspecialchars($_POST['booking_time']);

    $stmt = $conn->prepare("INSERT INTO grooming_bookings (user_id, service_id, booking_date, booking_time) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $user_id, $service_id, $booking_date, $booking_time);

    if ($stmt->execute()) {
        echo "<p class='success'>Booking confirmed for " . htmlspecialchars($service['service_name']) . "!</p>";
    } else {
        echo "<p class='error'>Booking failed. Please try again. Error: " . $stmt->error . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/style.css">
    <title>Book Grooming Service</title>
    <style>
         body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: url('../assets/images/owner.jpg') no-repeat center center fixed;
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
    <div class="booking-container">
        <h2>Book Grooming Service</h2>
        
        <?php if (isset($service)): ?>
            <h3><?php echo htmlspecialchars($service['service_name']); ?></h3>
            <p><strong>Description:</strong> <?php echo htmlspecialchars($service['description']); ?></p>
            <p><strong>Price:</strong> $<?php echo htmlspecialchars($service['price']); ?></p>
            
            <!-- Booking Form -->
            <form action="" method="POST">
                <label for="booking_date">Date:</label>
                <input type="date" name="booking_date" required>

                <label for="booking_time">Time:</label>
                <input type="time" name="booking_time" required>

                <input type="submit" value="Confirm Booking">
            </form>
        <?php else: ?>
            <p>Service not available.</p>
        <?php endif; ?>

        <a href="grooming.php">Back to Grooming Services</a>
    </div>
</body>
</html>
