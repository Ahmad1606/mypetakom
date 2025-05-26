<?php
session_start();
include '../layout/dashboard_layout.php';
include '../db/connect.php';

if (!isset($_SESSION['UserID']) || ($_SESSION['Role'] !== 'PA' && $_SESSION['Role'] !== 'EA')) {
    header(header: "Location: ../module1/index.php");
    exit();
}

// Handle Approve / Reject
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['AttendanceID'], $_POST['UserID'])) {
    $action = $_POST['action'];
    $AttendanceID = $_POST['AttendanceID'];
    $UserID = $_POST['UserID'];

    if (in_array($action, ['Approved', 'Rejected'])) {
        $stmt = $conn->prepare("UPDATE attendance SET AttendanceStatus = ? WHERE AttendanceID = ? AND UserID = ?");
        $stmt->bind_param("sss", $action, $AttendanceID, $UserID);
        $stmt->execute();
        $stmt->close();
    }
}

// Handle Delete
if (isset($_GET['delete']) && isset($_GET['aid']) && isset($_GET['uid'])) {
    $aid = $_GET['aid'];
    $uid = $_GET['uid'];
    $stmt = $conn->prepare("DELETE FROM attendance WHERE AttendanceID = ? AND UserID = ?");
    $stmt->bind_param("ss", $aid, $uid);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_attendance.php");
    exit();
}

// Retrieve attendance list
$sql = "SELECT a.AttendanceID, a.UserID, u.Name, e.Title AS EventTitle, a.AttendanceTime, a.Location, a.AttendanceStatus
        FROM attendance a
        JOIN user u ON a.UserID = u.UserID
        JOIN event e ON a.EventID = e.EventID
        ORDER BY a.AttendanceTime DESC";
$result = $conn->query($sql);
?>

<div class="main">
  <h2>Manage Attendance</h2>

  <table class="data-table">
    <thead>
      <tr>
        <th>Student</th>
        <th>Event</th>
        <th>Time</th>
        <th>Location</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['Name']) ?> (<?= $row['UserID'] ?>)</td>
          <td><?= htmlspecialchars($row['EventTitle']) ?></td>
          <td><?= htmlspecialchars($row['AttendanceTime']) ?></td>
          <td><?= htmlspecialchars($row['Location']) ?></td>
          <td><?= htmlspecialchars($row['AttendanceStatus']) ?></td>
          <td>
            <form method="POST" style="display:inline;">
              <input type="hidden" name="AttendanceID" value="<?= $row['AttendanceID'] ?>">
              <input type="hidden" name="UserID" value="<?= $row['UserID'] ?>">
              <button type="submit" name="action" value="Approved">Approve</button>
              <button type="submit" name="action" value="Rejected">Reject</button>
            </form>
            <a href="?delete=1&aid=<?= $row['AttendanceID'] ?>&uid=<?= $row['UserID'] ?>" onclick="return confirm('Delete this attendance?')">Delete</a>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?php $conn->close(); ?>