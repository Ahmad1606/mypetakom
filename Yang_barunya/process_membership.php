<?php
include 'config_all.php';
include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['MembershipID'], $_POST['action'])) {
    $id = $_POST['MembershipID'];
    $status = $_POST['action'] === 'approve' ? 'Approved' : 'Rejected';
    $admin = $_SESSION['UserID'];

    $stmt = $conn->prepare("UPDATE Membership SET Status = ?, ApprovalDate = CURDATE(), ApprovedBy = ? WHERE MembershipID = ?");
    $stmt->bind_param("sss", $status, $admin, $id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['message'] = "Membership has been $status.";
    $_SESSION['msg_type'] = $status === 'Approved' ? 'success' : 'error';
}

header("Location: manage_membership.php");
exit();
?>
