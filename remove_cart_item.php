<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

include('../config/database.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $user_id = $_SESSION['user_id'];
    $product_id = intval($_POST['product_id']);

    $delete_query = $conn->prepare("DELETE FROM carts WHERE user_id = ? AND product_id = ?");
    $delete_query->bind_param("ii", $user_id, $product_id);

    if ($delete_query->execute()) {
        $_SESSION['message'] = "Product removed from cart successfully.";
    } else {
        $_SESSION['message'] = "Failed to remove product from cart.";
    }

    header("Location: profile.php");
    exit();
} else {
    header("Location: profile.php");
    exit();
}
