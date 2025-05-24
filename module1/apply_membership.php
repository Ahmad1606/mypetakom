<?php
session_start();
include '../db/config_all.php';
include '../db/connect.php';

$UserID = $_SESSION['UserID'] ?? null;

if (!$UserID || !isset($_FILES['student_card'])) {
    $_SESSION['message'] = "Unauthorized or no file uploaded.";
    $_SESSION['msg_type'] = "danger";
    header("Location: student_membership.php");
    exit();
}

// Check if student has a previous record
$stmt = $conn->prepare("SELECT MembershipID, Status FROM Membership WHERE UserID = ?");
$stmt->bind_param("s", $UserID);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($existingID, $existingStatus);
$hasRecord = $stmt->num_rows > 0;
$stmt->fetch();
$stmt->close();

// Handle file upload
$file = $_FILES['student_card'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$filename = "student_card_$UserID.$ext";
$path = "uploads/$filename";

if (!is_dir("uploads")) mkdir("uploads", 0775, true);

if (!move_uploaded_file($file['tmp_name'], $path)) {
    $_SESSION['message'] = "Failed to upload file.";
    $_SESSION['msg_type'] = "danger";
    header("Location: student_membership.php");
    exit();
}

if ($hasRecord && strtolower($existingStatus) === 'rejected') {
    // Reapply: update existing record
    $stmt = $conn->prepare("UPDATE Membership SET StudentCard = ?, Status = 'Pending' WHERE MembershipID = ?");
    $stmt->bind_param("ss", $path, $existingID);
    $stmt->execute();
    $stmt->close();
    $_SESSION['message'] = "Re-application submitted successfully.";
    $_SESSION['msg_type'] = "success";

} elseif (!$hasRecord) {
    // Generate next MembershipID like MS001
    $res = $conn->query("SELECT MembershipID FROM Membership ORDER BY MembershipID DESC LIMIT 1");
    $lastID = $res && $row = $res->fetch_assoc() ? intval(substr($row['MembershipID'], 2)) + 1 : 1;
    $newID = 'MS' . str_pad($lastID, 3, '0', STR_PAD_LEFT);

    // Insert new record
    $stmt = $conn->prepare("INSERT INTO Membership (MembershipID, UserID, StudentCard) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $newID, $UserID, $path);
    $stmt->execute();
    $stmt->close();

    $_SESSION['message'] = "Application submitted successfully.";
    $_SESSION['msg_type'] = "success";
}

header("Location: student_membership.php");
exit();
