<?php
session_start();
include '../layout/dashboard_layout.php';
include '../db/connect.php';
include '../lib/phpqrcode/qrlib.php'; // Ensure this path matches your structure

if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== 'PA') {
    header(header: "Location: ../module1/index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_slot'])) {
    $AttendanceID = $_POST['AttendanceID'];
    $EventID = $_POST['EventID'];
    $Location = $_POST['Location'];
    $StartTime = $_POST['StartTime'];
    $EndTime = $_POST['EndTime'];
    $Date = $_POST['AttendanceDate'];

    // Generate QR Code
    $qrText = $AttendanceID;
    $qrFilename = "qr_" . $AttendanceID . ".png";
    $qrPath = "../uploads/qr/" . $qrFilename;
    if (!is_dir('../uploads/qr')) {
        mkdir('../uploads/qr', 0777, true);
    }
    QRcode::png($qrText, $qrPath, QR_ECLEVEL_L, 4);

    // Insert into DB
    $stmt = $conn->prepare("INSERT INTO attendance_slot (AttendanceID, EventID, Location, AttendanceStartTime, AttendanceEndTime, QRCodeAttendance, AttendanceDate) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $AttendanceID, $EventID, $Location, $StartTime, $EndTime, $qrFilename, $Date);
    $stmt->execute();
    $stmt->close();

    echo "<script>alert('Attendance slot created and QR code generated successfully.');</script>";
}

$events = $conn->query("SELECT EventID, Title FROM event");
?>

<div class="main">
  <h2>Create Attendance Slot</h2>
  <form method="POST">
    <input type="hidden" name="create_slot" value="1">
    <div class="mb-3">
      <label>Attendance ID</label>
      <input type="text" name="AttendanceID" required>
    </div>
    <div class="mb-3">
      <label>Event</label>
      <select name="EventID" required>
        <option value="">-- Select Event --</option>
        <?php while ($e = $events->fetch_assoc()): ?>
          <option value="<?= $e['EventID'] ?>">[<?= $e['EventID'] ?>] <?= $e['Title'] ?></option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="mb-3">
      <label>Location</label>
      <input type="text" name="Location" required>
    </div>
    <div class="mb-3">
      <label>Start Time</label>
      <input type="time" name="StartTime" required>
    </div>
    <div class="mb-3">
      <label>End Time</label>
      <input type="time" name="EndTime" required>
    </div>
    <div class="mb-3">
      <label>Attendance Date</label>
      <input type="date" name="AttendanceDate" required>
    </div>
    <button type="submit">Create Slot</button>
  </form>
</div>

<?php $conn->close(); ?>