<?php
session_start();
include '../layout/dashboard_layout.php';
include '../db/connect.php';

if (!isset($_SESSION['UserID']) || ($_SESSION['Role'] !== 'PA' && $_SESSION['Role'] !== 'EA')) {
    header("Location: ../module1/index.php");
    exit();
}

if (!isset($_GET['AttendanceID'])) {
    echo "<div class='main'><h2>No Attendance ID provided.</h2></div>";
    exit();
}

$AttendanceID = $_GET['AttendanceID'];
$stmt = $conn->prepare("SELECT e.Title, s.Location, s.AttendanceDate, s.QRCodeAttendance FROM attendance_slot s JOIN event e ON s.EventID = e.EventID WHERE s.AttendanceID = ?");
$stmt->bind_param("s", $AttendanceID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<div class='main'><h2>Attendance Slot not found.</h2></div>";
    exit();
}

$data = $result->fetch_assoc();
$stmt->close();
?>

<div class="main">
  <h2>QR Code for <?= htmlspecialchars($data['Title']) ?></h2>
  <p><strong>Date:</strong> <?= $data['AttendanceDate'] ?></p>
  <p><strong>Location:</strong> <?= htmlspecialchars($data['Location']) ?></p>
  <p><strong>QR Code:</strong></p>
  <img src="../uploads/qr/<?= htmlspecialchars($data['QRCodeAttendance']) ?>" alt="QR Code" width="250">
</div>

<?php $conn->close(); ?>