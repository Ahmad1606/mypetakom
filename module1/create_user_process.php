<?php
session_start();
include '../db/config_all.php';
include '../db/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $UserID = trim($_POST['UserID']);
    $Name = trim($_POST['Name']);
    $Password = $_POST['Password'];
    $Email = trim($_POST['Email']);
    $PhoneNumber = trim($_POST['PhoneNumber']);
    $Role = $_POST['Role'];

    
    if (!$UserID || !$Name || !$Password || !$Email || !$PhoneNumber || !$Role) {
        $_SESSION['message'] = "Please fill in all required fields.";
        $_SESSION['msg_type'] = "danger";
        header("Location: create_user.php");
        exit();
    }

    // Check if there is UserID
    $check = $conn->prepare("SELECT 1 FROM User WHERE UserID = ?");
    $check->bind_param("s", $UserID);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $_SESSION['message'] = "User ID already exists.";
        $_SESSION['msg_type'] = "danger";
        $check->close();
        header("Location: create_user.php");
        exit();
    }
    $check->close();

    // Create new user
    $hashed = password_hash($Password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO User (UserID, Name, Role, Password, Email, PhoneNumber) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $UserID, $Name, $Role, $hashed, $Email, $PhoneNumber);

    if ($stmt->execute()) {
        $_SESSION['message'] = "User created successfully.";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['message'] = "Failed to create user.";
        $_SESSION['msg_type'] = "danger";
    }

    $stmt->close();
    header("Location: admin_profile.php");
    exit();
}
?>
