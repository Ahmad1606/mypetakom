<?php
include '../layout/dashboard_layout.php';

// Fetch membership status
$UserID = $_SESSION['UserID'];
$stmt = $conn->prepare("SELECT Status FROM Membership WHERE UserID = ?");
$stmt->bind_param("s", $UserID);
$stmt->execute();
$stmt->bind_result($Status);
$stmt->fetch();
$stmt->close();
?>

<h2>Welcome, <?= htmlspecialchars($Name) ?></h2>

