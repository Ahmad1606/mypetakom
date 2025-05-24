<?php
session_start();
include '../db/config_all.php';
include '../db/connect.php';

// Make sure an ID is passed
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message'] = "Invalid user ID.";
    $_SESSION['msg_type'] = "danger";
    header("Location: admin_profile.php");
    exit();
}

$UserID = $_GET['id'];

// Delete user
$stmt = $conn->prepare("DELETE FROM User WHERE UserID = ?");
$stmt->bind_param("s", $UserID);

if ($stmt->execute()) {
    $_SESSION['message'] = "User deleted successfully.";
    $_SESSION['msg_type'] = "success";
} else {
    $_SESSION['message'] = "Failed to delete user.";
    $_SESSION['msg_type'] = "danger";
}

$stmt->close();
header("Location: admin_profile.php");
exit();
?>
