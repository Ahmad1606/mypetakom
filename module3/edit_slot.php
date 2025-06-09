<?php
session_start();
include '../layout/dashboard_layout.php';
include '../db/connect.php';

if (!isset($_SESSION['UserID']) || ($_SESSION['Role'] !== 'PA' && $_SESSION['Role'] !== 'EA')){
    header("Location: ../module1/index.php");
    exit();
}

$AttendanceID = $_GET['id'] ?? null;
$slot = null;
$events = $conn->query("SELECT EventID, Title FROM event");

if ($AttendanceID) {
    $stmt = $conn->prepare("SELECT * FROM attendance_slot WHERE AttendanceID = ?");
    $stmt->bind_param("s", $AttendanceID);
    $stmt->execute();
    $slot = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $AttendanceID = $_POST['AttendanceID'];
    $EventID = $_POST['EventID'];
    $Location = $_POST['Location'];
    $StartTime = $_POST['StartTime'];
    $EndTime = $_POST['EndTime'];
    $Date = $_POST['AttendanceDate'];

    $stmt = $conn->prepare("UPDATE attendance_slot SET EventID = ?, Location = ?, AttendanceStartTime = ?, AttendanceEndTime = ?, AttendanceDate = ? WHERE AttendanceID = ?");
    $stmt->bind_param("ssssss", $EventID, $Location, $StartTime, $EndTime, $Date, $AttendanceID);
    $stmt->execute();
    $stmt->close();

    echo "<script>alert('Slot updated successfully.'); window.location.href='manage_attendance.php';</script>";
    exit();
}
?>

<div class="main">
  <h2>Edit Attendance Slot</h2>
  <?php if ($slot): ?>
  <form method="POST">
    <input type="hidden" name="AttendanceID" value="<?= $slot['AttendanceID'] ?>">

    <div class="mb-3">
      <label>Event</label>
      <select name="EventID" required>
        <?php while ($e = $events->fetch_assoc()): ?>
          <option value="<?= $e['EventID'] ?>" <?= $e['EventID'] === $slot['EventID'] ? 'selected' : '' ?>>
            [<?= $e['EventID'] ?>] <?= $e['Title'] ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>

    <div class="mb-3">
      <label>Location</label>
      <input type="text" name="Location" value="<?= $slot['Location'] ?>" required>
    </div>
    <div class="mb-3">
      <label>Start Time</label>
      <input type="time" name="StartTime" value="<?= $slot['AttendanceStartTime'] ?>" required>
    </div>
    <div class="mb-3">
      <label>End Time</label>
      <input type="time" name="EndTime" value="<?= $slot['AttendanceEndTime'] ?>" required>
    </div>
    <div class="mb-3">
      <label>Date</label>
      <input type="date" name="AttendanceDate" value="<?= $slot['AttendanceDate'] ?>" required>
    </div>
    <button type="submit">Update Slot</button>
  </form>

  <div style="margin-top: 20px;">
    <strong>QR Code:</strong><br>
    <img src="../uploads/qr/<?= $slot['QRCodeAttendance'] ?>" width="180" style="border: 1px solid #ccc; padding: 10px; border-radius: 10px;">
  </div>

  <?php else: ?>
    <p>No slot selected. Append <code>?id=A001</code> to the URL.</p>
  <?php endif; ?>
</div>

<?php $conn->close(); ?>
