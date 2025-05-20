<?php
// connect.php - connects to the new database

$conn = new mysqli("localhost", "root", "", "mypetakomv2");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
