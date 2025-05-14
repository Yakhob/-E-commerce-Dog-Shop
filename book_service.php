<?php
include('../config/database.php');
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<p>Please log in to book a grooming service.</p>";
    exit();
}

$user_id = $_SESSION['user_id']; // Retrieve user ID from the session

// Check if service_id is provided
if (!isset($_GET['service_id'])) {
    echo "<p>Invalid request. No service selected.</p>";
    exit();
}

$service_id = intval($_GET['service_id']);

// Fetch the service details
$sql = "SELECT service_name, price FROM grooming_services WHERE service_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $service_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p>Service not found.</p>";
    exit();
}

$service = $result->fetch_assoc();

// Handle booking form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_date = $_POST['booking_date'];
    $booking_time = $_POST['booking_time'];

    // Insert booking details into the database
    $insert_sql = "INSERT INTO grooming_bookings (user_id, service_id, booking_date, booking_time, book_status) VALUES (?, ?, ?, ?, 'pending')";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("iiss", $user_id, $service_id, $booking_date, $booking_time);

    if ($stmt->execute()) {
        // Redirect to grooming.php after successful booking
        header("Location: grooming.php");
        exit();
    } else {
        echo "<p>Error booking the service. Please try again.</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Grooming Service</title>
    <link rel="stylesheet" href="../assets/css/book_service.css">
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
    <script>
        function confirmBooking() {
            const userConfirmed = confirm("Do you want to confirm the booking?");
            if (userConfirmed) {
                alert("Your request will be responded to shortly.");
                return true; // Proceed with form submission
            }
            return false; // Cancel form submission
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>Book Grooming Service</h2>
        <div class="service-details">
            <h3><?php echo htmlspecialchars($service['service_name']); ?></h3>
            <p><strong>Price:</strong> $<?php echo htmlspecialchars($service['price']); ?></p>
        </div>

        <form action="" method="POST" onsubmit="return confirmBooking();">
            <label for="booking_date">Booking Date:</label>
            <input type="date" id="booking_date" name="booking_date" required>

            <label for="booking_time">Booking Time:</label>
            <input type="time" id="booking_time" name="booking_time" required>

            <button type="submit" class="button">Confirm Booking</button>
        </form>
    </div>
</body>
</html>

<?php
$conn->close();
?>
