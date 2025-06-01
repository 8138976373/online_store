
<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location:../login.php');
    exit();
}

// Regenerate session ID for security
if (!isset($_SESSION['regenerated'])) {
    session_regenerate_id(true);
    $_SESSION['regenerated'] = true;
}
?>