<?php
session_start();
include '../db/connect.php';

$ApprovedBy = $_SESSION['UserID'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $MembershipID = $_POST['MembershipID'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $status = 'Approved';
        $date = date("Y-m-d");

        $stmt = $conn->prepare("UPDATE Membership SET Status = ?, ApprovalDate = ?, ApprovedBy = ? WHERE MembershipID = ?");
        $stmt->bind_param("ssss", $status, $date, $ApprovedBy, $MembershipID);
    } else {
        $status = 'Rejected';
        $stmt = $conn->prepare("UPDATE Membership SET Status = ?, ApprovalDate = NULL, ApprovedBy = NULL WHERE MembershipID = ?");
        $stmt->bind_param("ss", $status, $MembershipID);
    }

    if ($stmt->execute()) {
        $_SESSION['message'] = "Membership $status successfully.";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['message'] = "Failed to update membership.";
        $_SESSION['msg_type'] = "danger";
    }

    $stmt->close();
}

header("Location: manage_membership.php");
exit();
