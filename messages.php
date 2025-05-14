<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

include('../config/database.php');

// Fetch user details
$user_id = $_SESSION['user_id'];

// Handle delete all messages request
if (isset($_POST['delete_all'])) {
    $delete_all_query = $conn->prepare("DELETE FROM messages WHERE user_id = ?");
    $delete_all_query->bind_param("i", $user_id);
    $delete_all_query->execute();
}

// Handle delete single message request
if (isset($_POST['delete_message_id']) && is_numeric($_POST['delete_message_id'])) {
    $message_id = intval($_POST['delete_message_id']);
    $delete_query = $conn->prepare("DELETE FROM messages WHERE message_id = ? AND user_id = ?");
    $delete_query->bind_param("ii", $message_id, $user_id);
    $delete_query->execute();
}

// Fetch all messages ordered by message_id
$message_query = $conn->prepare("
    SELECT m.message_id, m.message, m.timestamp, m.type
    FROM messages m
    WHERE m.user_id = ?
    ORDER BY m.message_id DESC
");
$message_query->bind_param("i", $user_id);
$message_query->execute();
$messages = $message_query->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/messages.css">
    <title>Messages</title>
</head>
<body>
    <div class="messages-container">
        <h2>Your Messages</h2>
        
        <!-- Delete All Messages Button -->
        <form method="post" class="delete-all-form">
            <button type="submit" name="delete_all" onclick="return confirm('Are you sure you want to delete all messages?');">Delete All Messages</button>
        </form>

        <?php if ($messages->num_rows > 0): ?>
            <div class="messages-list">
                <?php while ($message = $messages->fetch_assoc()): ?>
                    <div class="message-item">
                        <div class="message-header">
                            <form method="post" class="delete-single-form">
                                <input type="hidden" name="delete_message_id" value="<?php echo $message['message_id']; ?>">
                                <button type="submit" onclick="return confirm('Are you sure you want to delete this message?');">
                                    <img src="../assets/images/delete.png" alt="Delete" class="delete-icon">
                                </button>
                            </form>
                        </div>
                        <div class="message-content">
                            <p><strong>Message:</strong> <?php echo htmlspecialchars($message['message']); ?></p>
                            <p><strong>Type:</strong> <?php echo htmlspecialchars($message['type']); ?></p>
                            <p><strong>Date:</strong> <?php echo htmlspecialchars($message['timestamp']); ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>No messages found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
