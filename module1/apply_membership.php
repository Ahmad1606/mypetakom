<?php
include 'config_all.php';
include 'connect.php';

$UserID = $_SESSION['UserID'];

// Prevent duplicate application
$q = $conn->prepare("SELECT MembershipID FROM Membership WHERE UserID = ?");
$q->bind_param("s", $UserID);
$q->execute();
$q->store_result();

if ($q->num_rows > 0) {
    $_SESSION['message'] = "You already applied.";
    $_SESSION['msg_type'] = "error";
    header("Location: student_membership.php");
    exit();
}
$q->close();

// File handling
$file = $_FILES['student_card'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$path = "uploads/student_card_$UserID.$ext";

if (!is_dir("uploads")) mkdir("uploads", 0775, true);

if (move_uploaded_file($file['tmp_name'], $path)) {
    $id = uniqid("M");
    $stmt = $conn->prepare("INSERT INTO Membership (MembershipID, UserID, StudentCard) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $id, $UserID, $path);
    $stmt->execute();
    $stmt->close();

    $_SESSION['message'] = "Application submitted.";
    $_SESSION['msg_type'] = "success";
} else {
    $_SESSION['message'] = "Upload failed.";
    $_SESSION['msg_type'] = "error";
}

header("Location: student_membership.php");
exit();
