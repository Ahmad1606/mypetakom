<?php
session_start();
include '../db/connect.php';

if (!isset($_SESSION['UserID']) || ($_SESSION['Role'] !== 'PA' && $_SESSION['Role'] !== 'EA')) {
    header("Location: ../module1/index.php");
    exit();
}

$deleted = false;
$error = "unknown";

if (isset($_GET['AttendanceID'])) {
    $AttendanceID = $_GET['AttendanceID'];

    if (trim($AttendanceID) === '') {
        $error = "empty_id";
    } else {
        // Get QR filename
        $qrQuery = $conn->prepare("SELECT QRCodeAttendance FROM attendance_slot WHERE AttendanceID = ?");
        $qrQuery->bind_param("s", $AttendanceID);
        $qrQuery->execute();
        $result = $qrQuery->get_result();

        if ($result->num_rows === 0) {
            $error = "notfound";
        } else {
            $row = $result->fetch_assoc();
            $qrPath = "../uploads/qr/" . $row['QRCodeAttendance'];

            // Delete QR code image
            if (!empty($row['QRCodeAttendance']) && file_exists($qrPath)) {
                unlink($qrPath);
            }

            // Delete slot
            $stmt = $conn->prepare("DELETE FROM attendance_slot WHERE AttendanceID = ?");
            $stmt->bind_param("s", $AttendanceID);
            $stmt->execute();
            $deleted = $stmt->affected_rows > 0;
            if (!$deleted) $error = "not_affected";
            $stmt->close();
        }
        $qrQuery->close();
    }
} else {
    $error = "id_missing";
}

// Redirect with status
header("Location: manage_attendance.php?status=" . ($deleted ? "deleted" : "error") . "&reason=$error");
exit();
