<?php
session_start();
include '../module2/layout/dashboard_layout.php';
include '../db/connect.php';

if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== 'ST') {
    header(header: "Location: ../module1/index.php");
    exit();
}

$UserID = $_SESSION['UserID'];

$stmt = $conn->prepare(query: "SELECT a.AttendanceID, e.Title AS EventTitle, a.AttendanceTime, a.Location, a.AttendanceStatus FROM attendance a JOIN event e ON a.EventID = e.EventID WHERE a.UserID = ?");
$stmt->bind_param(types: "s", var: $UserID);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="main">
  <h2>My Attendance Records</h2>

  <table class="data-table">
    <thead>
      <tr>
        <th>Event Title</th>
        <th>Attendance Time</th>
        <th>Location</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($row['EventTitle']) ?></td>
        <td><?= htmlspecialchars($row['AttendanceTime']) ?></td>
        <td><?= htmlspecialchars($row['Location']) ?></td>
        <td><?= htmlspecialchars($row['AttendanceStatus']) ?></td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?php $stmt->close(); $conn->close(); ?>