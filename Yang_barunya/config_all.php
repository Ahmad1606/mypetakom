<?php
// config_all.php
session_start();

if (!isset($_SESSION['UserID']) || !isset($_SESSION['Role'])) {
    header("Location: index.php");
    exit();
}
?>
