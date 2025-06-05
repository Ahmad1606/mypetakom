<?php
session_start();
include '../db/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['MembershipID'], $_POST['action'])) {
    $MembershipID = $_POST['MembershipID'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $sql = "UPDATE Membership SET Status = 'Approved' WHERE MembershipID = ?";
    } elseif ($action === 'reject') {
        $sql = "UPDATE Membership SET Status = 'Rejected' WHERE MembershipID = ?";
    }

    if (isset($sql)) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $MembershipID);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Membership status updated.";
            $_SESSION['msg_type'] = "success";
        } else {
            $_SESSION['message'] = "Failed to update.";
            $_SESSION['msg_type'] = "danger";
        }
        $stmt->close();
    }
}

header("Location: manage_membership.php");
exit();
