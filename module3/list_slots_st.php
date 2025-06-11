<?php
session_start();
include '../layout/dashboard_layout.php';
include '../db/connect.php';

if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== 'ST') {
    header("Location: ../module1/index.php");
    exit();
}

$dateToday = date('Y-m-d');
$searchQuery = "";  // Default to no search

// Check if search is performed
if (isset($_GET['search_event'])) {
    $searchQuery = "%" . $_GET['search_event'] . "%";
} else {
    $searchQuery = "%";
}

$query = "
    SELECT s.AttendanceID, e.Title AS EventTitle, s.Location, s.AttendanceDate, s.AttendanceStartTime, s.AttendanceEndTime
    FROM attendance_slot s
    JOIN event e ON s.EventID = e.EventID
    WHERE s.AttendanceDate >= ? AND e.Title LIKE ?
    ORDER BY s.AttendanceDate ASC, s.AttendanceStartTime ASC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $dateToday, $searchQuery);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="main">
    <div class="container">
        <h2>Available Attendance Slots</h2>

        <!-- Search Form -->
        <form method="GET" class="mb-3">
            <div class="input-group">
                <input type="text" class="form-control" name="search_event" placeholder="Search by event name" value="<?= isset($_GET['search_event']) ? htmlspecialchars($_GET['search_event']) : '' ?>">
                <button class="btn btn-outline-secondary" type="submit">Search</button>
            </div>
        </form>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Attendance ID</th>
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
                        <td><?= $row['AttendanceID'] ?></td>
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
