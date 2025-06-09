<?php
session_start();
include '../layout/dashboard_layout.php';
include '../db/connect.php';

if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== 'PA') {
    header("Location: ../module1/index.php");
    exit();
}

// Filters
$eventFilter = $_GET['event'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

// Get event options
$event_options = mysqli_query($conn, "SELECT DISTINCT e.EventID, e.Title FROM event e JOIN attendance a ON e.EventID = a.EventID");

// Build WHERE conditions
$where = [];
if ($eventFilter) $where[] = "e.EventID = '" . mysqli_real_escape_string($conn, $eventFilter) . "'";
if ($statusFilter) $where[] = "a.AttendanceStatus = '" . mysqli_real_escape_string($conn, $statusFilter) . "'";
if ($dateFrom && $dateTo) $where[] = "es.AttendanceDate BETWEEN '$dateFrom' AND '$dateTo'";
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Get attendance records
$detail_sql = "
    SELECT u.Name AS StudentName, u.UserID, e.Title AS EventTitle, es.AttendanceDate,
           a.AttendanceTime, a.Location, a.AttendanceStatus
    FROM attendance a
    JOIN user u ON a.UserID = u.UserID
    JOIN event e ON a.EventID = e.EventID
    JOIN attendance_slot es ON a.AttendanceID = es.AttendanceID
    $where_sql
    ORDER BY es.AttendanceDate DESC, a.AttendanceTime DESC
";
$detail_result = mysqli_query($conn, $detail_sql);

// Chart data
$chart_sql = "
    SELECT e.Title, a.AttendanceStatus, COUNT(*) as total
    FROM attendance a
    JOIN event e ON a.EventID = e.EventID
    GROUP BY e.Title, a.AttendanceStatus
";
$chart_result = mysqli_query($conn, $chart_sql);
$bar_data = [];
while ($row = mysqli_fetch_assoc($chart_result)) {
    $bar_data[$row['Title']][$row['AttendanceStatus']] = (int)$row['total'];
}
?>

<style>
@media print {
    .no-print, .no-print * { display: none !important; }
}
</style>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Attendance Insight Report</h3>
        <button onclick="window.print()" class="btn btn-secondary no-print">🖨️ Print Report</button>
    </div>

    <!-- Filter Form -->
    <form method="GET" class="row g-3 mb-4 no-print">
        <div class="col-md-4">
            <label class="form-label">Filter by Event</label>
            <select name="event" class="form-select">
                <option value="">All Events</option>
                <?php while ($e = mysqli_fetch_assoc($event_options)): ?>
                    <option value="<?= $e['EventID'] ?>" <?= $eventFilter == $e['EventID'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($e['Title']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">All</option>
                <option value="Approved" <?= $statusFilter == 'Approved' ? 'selected' : '' ?>>Approved</option>
                <option value="Pending" <?= $statusFilter == 'Pending' ? 'selected' : '' ?>>Pending</option>
                <option value="Rejected" <?= $statusFilter == 'Rejected' ? 'selected' : '' ?>>Rejected</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">From</label>
            <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($dateFrom) ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label">To</label>
            <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($dateTo) ?>">
        </div>
        <div class="col-md-1 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
    </form>

    <!-- Chart -->
    <div class="card mb-4">
        <div class="card-header">Attendance by Event & Status</div>
        <div class="card-body">
            <div id="barChart" style="height: 300px;"></div>
        </div>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="card-header">Attendance Records</div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Student Name</th>
                        <th>Student ID</th>
                        <th>Event</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Location</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; while ($row = mysqli_fetch_assoc($detail_result)): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['StudentName']) ?></td>
                            <td><?= htmlspecialchars($row['UserID']) ?></td>
                            <td><?= htmlspecialchars($row['EventTitle']) ?></td>
                            <td><?= htmlspecialchars($row['AttendanceDate']) ?></td>
                            <td><?= htmlspecialchars($row['AttendanceTime']) ?></td>
                            <td><?= htmlspecialchars($row['Location']) ?></td>
                            <td><?= htmlspecialchars($row['AttendanceStatus']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Chart Script -->
<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
<script>
window.onload = function () {
    const chart = new CanvasJS.Chart("barChart", {
        animationEnabled: true,
        theme: "light2",
        axisY: { title: "Attendance Count" },
        toolTip: { shared: true },
        legend: { cursor: "pointer" },
        data: []
    });

    const chartData = <?= json_encode($bar_data) ?>;
    const statuses = ["Approved", "Pending", "Rejected"];

    statuses.forEach(status => {
        const dataPoints = [];
        for (const event in chartData) {
            const count = chartData[event][status] || 0;
            dataPoints.push({ label: event, y: count });
        }
        chart.options.data.push({
            type: "column",
            name: status,
            showInLegend: true,
            dataPoints: dataPoints
        });
    });

    chart.render();
};
</script>
