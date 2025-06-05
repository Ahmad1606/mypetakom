<?php
session_start();
include '../db/config_all.php';
include '../db/connect.php';

$UserID = isset($_SESSION['UserID']) ? $_SESSION['UserID'] : null;

if (!$UserID || !isset($_FILES['student_card'])) {
    $_SESSION['message'] = "Unauthorized or no file uploaded.";
    $_SESSION['msg_type'] = "danger";
    header("Location: student_membership.php");
    exit();
}

// Check for existing membership
$sql = "SELECT MembershipID, Status FROM Membership WHERE UserID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $UserID);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($existingID, $existingStatus);
$hasRecord = $stmt->num_rows > 0;
$stmt->fetch();
$stmt->close();

// Handle upload
$file = $_FILES['student_card'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$filename = "student_card_$UserID.$ext";
$path = "uploads/$filename";

if (!is_dir("uploads")) {
    mkdir("uploads", 0775, true);
}

if (!move_uploaded_file($file['tmp_name'], $path)) {
    $_SESSION['message'] = "Failed to upload file.";
    $_SESSION['msg_type'] = "danger";
    header("Location: student_membership.php");
    exit();
}

// Insert or update
if ($hasRecord && strtolower($existingStatus) === 'rejected') {
    $sql = "UPDATE Membership SET StudentCard = ?, Status = 'Pending' WHERE MembershipID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $path, $existingID);
    $stmt->execute();
    $stmt->close();
    $_SESSION['message'] = "Re-application submitted successfully.";
    $_SESSION['msg_type'] = "success";
} elseif (!$hasRecord) {
    $sql = "SELECT MembershipID FROM Membership ORDER BY MembershipID DESC LIMIT 1";
    $result = $conn->query($sql);
    $newID = "MS001";
    if ($row = $result->fetch_assoc()) {
        $lastNum = (int)substr($row['MembershipID'], 2);
        $newID = "MS" . str_pad($lastNum + 1, 3, "0", STR_PAD_LEFT);
    }

    $sql = "INSERT INTO Membership (MembershipID, UserID, StudentCard) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $newID, $UserID, $path);
    $stmt->execute();
    $stmt->close();
    $_SESSION['message'] = "Application submitted successfully.";
    $_SESSION['msg_type'] = "success";
}

header("Location: student_membership.php");
exit();
