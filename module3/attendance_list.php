<?php
session_start();
include '../layout/dashboard_layout.php';
include '../db/connect.php';

if (!isset($_SESSION['UserID']) || ($_SESSION['Role'] !== 'EA')) {
    header("Location: ../module1/index.php");
    exit();
}

// Handle approval/rejection based on location
if (isset($_GET['action']) && isset($_GET['aid']) && isset($_GET['uid'])) {
    $status = ($_GET['action'] === 'approve') ? 'Approved' : 'Rejected';
    $AttendanceID = $_GET['aid'];
    $UserID = $_GET['uid'];

    // Get the event location and the student's entered location from attendance table
    $location_query = "
        SELECT s.Location AS SlotLocation, a.Location AS StudentLocation
        FROM attendance a
        JOIN attendance_slot s ON a.AttendanceID = s.AttendanceID
        WHERE a.AttendanceID = ? AND a.UserID = ?
    ";
    $stmt = $conn->prepare($location_query);
    $stmt->bind_param("ss", $AttendanceID, $UserID);
    $stmt->execute();
    $location_result = $stmt->get_result();
    $location_data = $location_result->fetch_assoc();

    // If the student's entered location matches the event location, auto-approve
    if (trim($location_data['SlotLocation']) === trim($location_data['StudentLocation'])) {
        $status = 'Approved';
    }

    // Update attendance status
    $update_query = "UPDATE attendance SET AttendanceStatus = ? WHERE AttendanceID = ? AND UserID = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("sss", $status, $AttendanceID, $UserID);
    $stmt->execute();
    $stmt->close();

    header("Refresh: 1; URL=attendance_list.php?status=" . strtolower($status));
    exit();
}

// Load attendance records
$query = "
    SELECT a.AttendanceID, a.UserID, u.Name, a.AttendanceTime AS CheckInTime, a.AttendanceStatus,
           s.Location, s.AttendanceDate, e.Title AS EventTitle
    FROM attendance a
    JOIN user u ON a.UserID = u.UserID
    JOIN attendance_slot s ON a.AttendanceID = s.AttendanceID
    JOIN event e ON s.EventID = e.EventID
    ORDER BY s.AttendanceDate DESC, a.AttendanceTime DESC
";
$result = $conn->query($query);
?>

<div class="main">
    <div class="container">
        <h2>Student Attendance Verification</h2>

        <!-- Success Banners -->
        <?php if (isset($_GET['status'])): ?>
            <?php if ($_GET['status'] === 'approved'): ?>
                <div style="background-color:#d4edda;color:#155724;padding:10px 15px;margin:15px 0;border-left:5px solid #28a745;display:flex;align-items:center;">
                    <span style="margin-right:10px;">✅</span> Attendance approved successfully.
                </div>
            <?php elseif ($_GET['status'] === 'rejected'): ?>
                <div style="background-color:#f8d7da;color:#721c24;padding:10px 15px;margin:15px 0;border-left:5px solid #dc3545;display:flex;align-items:center;">
                    <span style="margin-right:10px;">❌</span> Attendance rejected.
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Attendance ID</th>
                    <th>Student</th>
                    <th>Event</th>
                    <th>Date</th>
                    <th>Location</th>
                    <th>Check-In Time</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['AttendanceID'] ?></td>
                        <td><?= $row['UserID'] ?> - <?= $row['Name'] ?></td>
                        <td><?= $row['EventTitle'] ?></td>
                        <td><?= $row['AttendanceDate'] ?></td>
                        <td><?= $row['Location'] ?></td>
                        <td><?= $row['CheckInTime'] ?></td>
                        <td>
                            <?php
                                $status = $row['AttendanceStatus'];
                                if ($status === 'Approved') echo '<span style="color:green;font-weight:bold;">Approved</span>';
                                elseif ($status === 'Rejected') echo '<span style="color:red;font-weight:bold;">Rejected</span>';
                                else echo '<span style="color:orange;">Pending</span>';
                            ?>
                        </td>
                        <td>
                            <?php if ($status === 'Pending'): ?>
                                <a href="?action=approve&aid=<?= $row['AttendanceID'] ?>&uid=<?= $row['UserID'] ?>"
                                   class="btn btn-success btn-sm"
                                   onclick="return confirm('Are you sure you want to approve attendance for <?= $row['Name'] ?>?');">
                                   Approve
                                </a>
                                <a href="?action=reject&aid=<?= $row['AttendanceID'] ?>&uid=<?= $row['UserID'] ?>"
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('Are you sure you want to reject attendance for <?= $row['Name'] ?>?');">
                                   Reject
                                </a>
                            <?php else: ?>
                                <em>-</em>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $conn->close(); ?>
