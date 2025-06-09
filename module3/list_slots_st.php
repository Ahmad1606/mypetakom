<?php
session_start();
include '../layout/dashboard_layout.php';
include '../db/connect.php';

if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== 'ST') {
    header("Location: ../module1/index.php");
    exit();
}

$dateToday = date('Y-m-d');
$query = "
    SELECT s.AttendanceID, e.Title AS EventTitle, s.Location, s.AttendanceDate, s.AttendanceStartTime, s.AttendanceEndTime
    FROM attendance_slot s
    JOIN event e ON s.EventID = e.EventID
    WHERE s.AttendanceDate >= ?
    ORDER BY s.AttendanceDate ASC, s.AttendanceStartTime ASC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $dateToday);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="main">
    <div class="container">
        <h2>Available Attendance Slots</h2>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Event</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Location</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['EventTitle'] ?></td>
                    <td><?= $row['AttendanceDate'] ?></td>
                    <td><?= $row['AttendanceStartTime'] ?> - <?= $row['AttendanceEndTime'] ?></td>
                    <td><?= $row['Location'] ?></td>
                    <td>
                        <a href="checkin_attendance.php?aid=<?= $row['AttendanceID'] ?>" class="btn btn-primary btn-sm">Check In</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $stmt->close(); $conn->close(); ?>
