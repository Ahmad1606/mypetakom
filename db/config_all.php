<?php
// config_all.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['UserID']) || !isset($_SESSION['Role'])) {
    header("Location: ../module1/index.php");
    exit();
}
?>
