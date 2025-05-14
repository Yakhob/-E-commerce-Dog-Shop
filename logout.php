<?php
session_start();

// Destroy the session to log out the user
session_unset();
session_destroy();

// Redirect to the main index.php in the root dog_shop folder
header("Location: /dog_shop/index.php");
exit();
?>
