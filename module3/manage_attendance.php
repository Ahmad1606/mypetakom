<?php
session_start();
include '../layout/dashboard_layout.php';
include '../db/connect.php';

if (!isset($_SESSION['UserID']) || ($_SESSION['Role'] !== 'PA' && $_SESSION['Role'] !== 'EA')) {
    header("Location: ../module1/index.php");
    exit();
}

// Check for search query
$searchQuery = isset($_GET['search']) ? "%" . $_GET['search'] . "%" : "%";

$query = "
    SELECT a.AttendanceID, a.EventID, e.Title AS EventTitle, a.Location,
           a.AttendanceStartTime, a.AttendanceEndTime, a.AttendanceDate, a.QRCodeAttendance
    FROM attendance_slot a
    JOIN event e ON a.EventID = e.EventID
    WHERE e.Title LIKE ?
    ORDER BY a.AttendanceDate DESC, a.AttendanceStartTime ASC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $searchQuery);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="main">
    <div class="container">
        <h2>Manage Attendance Slots</h2>

        <!-- Search Form -->
        <form method="GET" class="mb-3">
            <div class="input-group">
                <input type="text" class="form-control" name="search" placeholder="Search by event name"
                       value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                <button class="btn btn-outline-secondary" type="submit">Search</button>
            </div>
        </form>

        <!-- Alert messages -->
        <?php if (isset($_GET['status'])): ?>
            <?php if ($_GET['status'] === 'deleted'): ?>
                <div style="background-color:#d4edda;color:#155724;padding:10px;margin-bottom:15px;border-left:5px solid #28a745;">
                    ✅ Slot deleted successfully.
                </div>
            <?php elseif ($_GET['status'] === 'error'): ?>
                <div style="background-color:#f8d7da;color:#721c24;padding:10px;margin-bottom:15px;border-left:5px solid #dc3545;">
                    ❌ Failed to delete slot. Reason: <?= htmlspecialchars($_GET['reason'] ?? 'unknown') ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <a href="create_slot.php" class="btn btn-primary mb-3">+ Create New Slot</a>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Attendance ID</th>
                    <th>Event</th>
                    <th>Location</th>
                    <th>Date</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>QR Code</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['AttendanceID'] ?></td>
                        <td><?= $row['EventTitle'] ?></td>
                        <td><?= $row['Location'] ?></td>
                        <td><?= $row['AttendanceDate'] ?></td>
                        <td><?= $row['AttendanceStartTime'] ?></td>
                        <td><?= $row['AttendanceEndTime'] ?></td>
                        <td>
                            <?php if ($row['QRCodeAttendance']): ?>
                                <img src="../uploads/qr/<?= $row['QRCodeAttendance'] ?>" alt="QR Code" width="50">
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="edit_slot.php?id=<?= $row['AttendanceID'] ?>" class="btn btn-sm btn-warning">Edit</a>
                            <a href="delete_slot.php?AttendanceID=<?= $row['AttendanceID'] ?>"
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Are you sure you want to delete this slot?');">
                               Delete
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $stmt->close(); $conn->close(); ?>

