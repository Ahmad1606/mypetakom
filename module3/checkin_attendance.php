<?php
session_start();
include '../layout/dashboard_layout.php';
include '../db/connect.php';

if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== 'ST') {
    header("Location: ../module1/index.php");
    exit();
}

$AttendanceID = $_GET['aid'] ?? null;

if (!$AttendanceID) {
    echo "<script>alert('Invalid QR access.'); window.location.href='list_slots_student.php';</script>";
    exit();
}

// Get QR filename from DB
$stmt = $conn->prepare("SELECT QRCodeAttendance FROM attendance_slot WHERE AttendanceID = ?");
$stmt->bind_param("s", $AttendanceID);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$stmt->close();
?>

<div class="main">
    <div class="container" style="text-align: center;">
        <h2>Attendance QR Code</h2>
        <?php if ($data && $data['QRCodeAttendance']): ?>
            <p>Scan this QR code to check in for <strong><?= htmlspecialchars($AttendanceID) ?></strong></p>
            <img src="../uploads/qr/<?= htmlspecialchars($data['QRCodeAttendance']) ?>" alt="QR Code" style="width:250px;height:250px;border-radius:10px;">

            <br><br>
            <a href="list_slots_st.php" class="btn btn-secondary" style="padding:8px 18px; text-decoration:none; background:#6c757d; color:white; border-radius:5px;">← Back to Slot List</a>
        <?php else: ?>
            <p style="color:red;">QR Code not found for this attendance slot.</p>
            <a href="list_slots_st.php" class="btn btn-secondary" style="padding:8px 18px; text-decoration:none; background:#6c757d; color:white; border-radius:5px;">← Back to Slot List</a>
        <?php endif; ?>
    </div>
</div>

<?php $conn->close(); ?>
