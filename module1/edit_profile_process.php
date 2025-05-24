<?php
session_start();
include '../db/connect.php';

$targetID = $_SESSION['UserID'] ?? null;

// If admin is editing another user
if (isset($_GET['id']) && $_SESSION['Role'] === 'PA') {
    $targetID = $_GET['id'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $targetID) {
    $Email = trim($_POST['Email']);
    $PhoneNumber = trim($_POST['PhoneNumber']);
    $Password = trim($_POST['Password']);

    if (!$Email || !$PhoneNumber) {
        $_SESSION['message'] = "Email and phone number are required.";
        $_SESSION['msg_type'] = "danger";
        $redirect = isset($_GET['id']) ? "edit_user.php?id=" . urlencode($_GET['id']) : "edit_profile.php";
        header("Location: $redirect");
        exit();
    }

    if (!empty($Password)) {
        $hashed = password_hash($Password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE User SET Email = ?, Password = ?, PhoneNumber = ? WHERE UserID = ?");
        $stmt->bind_param("ssss", $Email, $hashed, $PhoneNumber, $targetID);
    } else {
        $stmt = $conn->prepare("UPDATE User SET Email = ?, PhoneNumber = ? WHERE UserID = ?");
        $stmt->bind_param("sss", $Email, $PhoneNumber, $targetID);
    }

    if ($stmt->execute()) {
        $_SESSION['message'] = "Profile updated successfully.";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['message'] = "Update failed.";
        $_SESSION['msg_type'] = "danger";
    }

    $stmt->close();
    $redirect = isset($_GET['id']) ? "edit_user.php?id=" . urlencode($_GET['id']) : "edit_profile.php";
    header("Location: $redirect");
    exit();
}
?>
