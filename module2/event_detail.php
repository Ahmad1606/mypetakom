<?php
session_start();
include '../layout/dashboard_layout.php';
include '../db/connect.php';

if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== 'ST') {
    header("Location: ../module1/index.php");
    exit();
}

$eventID = $_GET['event_id'] ?? '';

if (!$eventID) {
    echo "<p>Invalid event ID.</p>";
    exit();
}

$stmt = $conn->prepare("SELECT * FROM event WHERE EventID = ?");
$stmt->bind_param("s", $eventID);
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();

if (!$event) {
    echo "<p>Event not found.</p>";
    exit();
}
?>
<div class="p-4 bg-white shadow rounded-3">
    <div class="container mt-5">
        <h2><?= htmlspecialchars($event['Title']) ?></h2>
        <p><strong>Date:</strong> <?= $event['Date'] ?></p>
        <p><strong>Time:</strong> <?= date("g:i A", strtotime($event['Time'])) ?></p>
        <p><strong>Location:</strong> <?= htmlspecialchars($event['Location']) ?></p>
        <p><strong>Status:</strong> <?= $event['Status'] ?></p>
        <p><strong>Level:</strong> <?= $event['Level'] ?></p>
        <p><strong>Description:</strong></p>
        <p><?= nl2br(htmlspecialchars($event['Description'])) ?></p>

        <!-- <div class="mt-4">
            <img src="../uploads/<?= $event['QRCode'] ?>" alt="QR Code" width="150">
            <p><small>Scan to verify attendance (if available).</small></p>
        </div> -->
    </div>
</div>
