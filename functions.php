<?php
function sanitizeInput($data) {
    return htmlspecialchars(trim($data));
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCartCount($user_id) {
    global $conn; // Use the global connection variable
    $stmt = $conn->prepare("SELECT COUNT(*) FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    return $count;
}

function fetchGroomingServices() {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM grooming_services");
    $stmt->execute();
    return $stmt->get_result();
}
?>
