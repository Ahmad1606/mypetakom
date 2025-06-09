<?php
session_start();
include '../layout/dashboard_layout.php';
include '../db/connect.php';

if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== 'ST') {
    header("Location: ../module1/index.php");
    exit();
}

$UserID = $_SESSION['UserID'];

$stmt = $conn->prepare("
    SELECT a.AttendanceID, e.Title AS EventTitle, a.AttendanceTime, a.Location, a.AttendanceStatus
    FROM attendance a
    JOIN attendance_slot s ON a.AttendanceID = s.AttendanceID
    JOIN event e ON s.EventID = e.EventID
    WHERE a.UserID = ?
    ORDER BY a.AttendanceTime DESC
");
$stmt->bind_param("s", $UserID);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="main">
    <div class="container">
        <h2>My Attendance Records</h2>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Event Title</th>
                    <th>Attendance Time</th>
                    <th>Location</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['EventTitle']) ?></td>
                            <td><?= htmlspecialchars($row['AttendanceTime']) ?></td>
                            <td><?= htmlspecialchars($row['Location']) ?></td>
                            <td>
                                <?php
                                    $status = $row['AttendanceStatus'];
                                    if ($status === 'Approved') echo '<span style="color:green;font-weight:bold;">Approved</span>';
                                    elseif ($status === 'Rejected') echo '<span style="color:red;font-weight:bold;">Rejected</span>';
                                    else echo '<span style="color:orange;">Pending</span>';
                                ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4">No attendance records found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $stmt->close(); $conn->close(); ?>
