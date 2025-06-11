<?php
session_start();
include '../layout/dashboard_layout.php';
include '../db/connect.php';

// Get attendance records without filters
$query = "
    SELECT u.Name AS StudentName, u.UserID, e.Title AS EventTitle, es.AttendanceDate,
           a.AttendanceTime, a.Location, a.AttendanceStatus
    FROM attendance a
    JOIN user u ON a.UserID = u.UserID
    JOIN event e ON a.EventID = e.EventID
    JOIN attendance_slot es ON a.AttendanceID = es.AttendanceID
    ORDER BY es.AttendanceDate DESC, a.AttendanceTime DESC
";
$result = mysqli_query($conn, $query);

// Total attendance count
$total_attendance_query = "
    SELECT COUNT(*) AS total_attendance
    FROM attendance a
    JOIN event e ON a.EventID = e.EventID
    JOIN attendance_slot es ON a.AttendanceID = es.AttendanceID
";
$total_attendance_result = mysqli_query($conn, $total_attendance_query);
$total_attendance_row = mysqli_fetch_assoc($total_attendance_result);
$total_attendance = $total_attendance_row['total_attendance'];

// Chart data preparation
$chart_sql = "
    SELECT e.Title, a.AttendanceStatus, COUNT(*) as total
    FROM attendance a
    JOIN event e ON a.EventID = e.EventID
    GROUP BY e.Title, a.AttendanceStatus
";
$chart_result = mysqli_query($conn, $chart_sql);
$pie_data = [];

while ($row = mysqli_fetch_assoc($chart_result)) {
    $pie_data[$row['AttendanceStatus']][] = $row['total'];
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

    <!-- Total Attendance Count -->
    <div class="mb-3">
        <h5>Total Attendance: <?= $total_attendance ?></h5>
    </div>

    <!-- Pie Chart -->
    <div class="card mb-4">
        <div class="card-header">Attendance by Event & Status</div>
        <div class="card-body">
            <div id="pieChart" style="height: 300px;"></div>
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
                    <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
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
    const pieChart = new CanvasJS.Chart("pieChart", {
        animationEnabled: true,
        theme: "light2",
        title: {
            text: "Attendance Status Distribution"
        },
        data: [{
            type: "pie",
            dataPoints: [
                { label: "Approved", y: <?= isset($pie_data['Approved']) ? array_sum($pie_data['Approved']) : 0 ?> },
                { label: "Pending", y: <?= isset($pie_data['Pending']) ? array_sum($pie_data['Pending']) : 0 ?> },
                { label: "Rejected", y: <?= isset($pie_data['Rejected']) ? array_sum($pie_data['Rejected']) : 0 ?> }
            ]
        }]
    });

    pieChart.render();
};
</script>
