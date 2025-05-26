<?php
session_start();
include '../layout/dashboard_layout.php';
include '../db/connect.php';

if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== 'ST') {
    header("Location: ../module1/index.php");
    exit();
}

$UserID = $_SESSION['UserID'];
$selectedAid = $_GET['aid'] ?? '';

// Handle attendance submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['AttendanceID'])) {
    $AttendanceID = $_POST['AttendanceID'];
    $Location = $_POST['Location'];
    $EventID = $_POST['EventID'];
    $AttendanceTime = date("H:i:s");
    $status = 'Pending';

    $stmt = $conn->prepare("INSERT INTO attendance (AttendanceID, UserID, EventID, AttendanceTime, Location, AttendanceStatus) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $AttendanceID, $UserID, $EventID, $AttendanceTime, $Location, $status);
    $stmt->execute();
    $stmt->close();

    echo "<script>alert('Attendance submitted successfully!'); window.location.href='attendance_list.php';</script>";
    exit();
}

$slotQuery = "SELECT AttendanceID, EventID, Location FROM attendance_slot";
$slots = $conn->query($slotQuery);
?>

<div class="main">
  <h2>Submit Attendance</h2>
  <form method="POST">
    <div class="mb-3">
      <label for="AttendanceID">Select Attendance Slot</label>
      <select name="AttendanceID" id="AttendanceID" required>
        <option value="">-- Select Slot --</option>
        <?php while ($row = $slots->fetch_assoc()):
          $selected = ($row['AttendanceID'] === $selectedAid) ? 'selected' : '';
        ?>
          <option value="<?= $row['AttendanceID'] ?>" data-event="<?= $row['EventID'] ?>" data-location="<?= $row['Location'] ?>" <?= $selected ?>>
            <?= $row['AttendanceID'] ?> - <?= $row['Location'] ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>
    <input type="hidden" name="EventID" id="EventID">
    <input type="hidden" name="Location" id="Location">
    <button type="submit">Submit Attendance</button>
  </form>
</div>

<script>
  const dropdown = document.getElementById("AttendanceID");
  const eventInput = document.getElementById("EventID");
  const locationInput = document.getElementById("Location");

  function updateHiddenFields() {
    const selected = dropdown.options[dropdown.selectedIndex];
    eventInput.value = selected.getAttribute('data-event');
    locationInput.value = selected.getAttribute('data-location');
  }

  dropdown.addEventListener('change', updateHiddenFields);

  // Auto-trigger if pre-selected from ?aid=
  if (dropdown.value !== '') {
    updateHiddenFields();
  }
</script>

<?php $conn->close(); ?>
