<?php
session_start();
include '../db/connect.php';

if (!isset($_SESSION['UserID']) || ($_SESSION['Role'] !== 'PA' && $_SESSION['Role'] !== 'EA')) {
    header("Location: ../module1/index.php");
    exit();
}

if (isset($_GET['aid']) && isset($_GET['uid'])) {
    $stmt = $conn->prepare("DELETE FROM attendance WHERE AttendanceID = ? AND UserID = ?");
    $stmt->bind_param("ss", $_GET['aid'], $_GET['uid']);
    $stmt->execute();
    $stmt->close();
}

header("Location: manage_attendance.php");
exit();
?>