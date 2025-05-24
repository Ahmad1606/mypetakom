<?php
include '../db/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_event'])) {
    $eventID = $_POST['event_id'];

    // Delete QR code and approval letter files (optional cleanup)
    $stmt = $conn->prepare("SELECT QRCode, ApprovalLetter FROM event WHERE EventID = ?");
    $stmt->bind_param("s", $eventID);
    $stmt->execute();
    $stmt->bind_result($qr, $letter);
    $stmt->fetch();
    $stmt->close();

    if ($qr && file_exists("../uploads/qrcodes/$qr")) unlink("../uploads/qrcodes/$qr");
    if ($letter && file_exists("../uploads/approvalLetters/$letter")) unlink("../uploads/approvalLetters/$letter");

    // Delete the event from DB
    $stmt = $conn->prepare("DELETE FROM event WHERE EventID = ?");
    $stmt->bind_param("s", $eventID);

    if ($stmt->execute()) {
        header("Location: manage_event.php?deleted=1");
    } else {
        header("Location: manage_event.php?deleted=0");
    }
    exit;
}
?>
