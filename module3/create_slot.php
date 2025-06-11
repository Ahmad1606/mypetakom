<?php
session_start();

if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== 'EA') {
    header("Location: ../module1/index.php");
    exit();
}

include '../layout/dashboard_layout.php';
include '../db/connect.php';
include '../lib/phpqrcode/phpqrcode.php';

$success = false;

// Get the latest Attendance ID and auto-increment it
$query = "SELECT MAX(AttendanceID) AS max_id FROM attendance_slot";
$result = $conn->query($query);
$row = $result->fetch_assoc();
$max_id = $row['max_id'];

if ($max_id) {
    // Increment the numeric part of the Attendance ID (assuming format A001, A002, ...)
    $num = (int)substr($max_id, 1) + 1; // Extract numeric part, increment, and reformat
    $AttendanceID = 'A' . str_pad($num, 3, '0', STR_PAD_LEFT);
} else {
    // If no attendance slots exist, start with A001
    $AttendanceID = 'A001';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_slot'])) {
    $EventID = $_POST['EventID'];
    $StartTime = $_POST['StartTime'];
    $EndTime = $_POST['EndTime'];
    $Date = $_POST['AttendanceDate'];

    // Fetch event location automatically from the event table
    $event_query = "SELECT Location FROM event WHERE EventID = ?";
    $stmt = $conn->prepare($event_query);
    $stmt->bind_param("s", $EventID);
    $stmt->execute();
    $event_result = $stmt->get_result();
    $event_data = $event_result->fetch_assoc();
    $Location = $event_data['Location']; // Automatically set the event location

    // Check if the Attendance ID already exists
    $check = $conn->prepare("SELECT 1 FROM attendance_slot WHERE AttendanceID = ?");
    $check->bind_param("s", $AttendanceID);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $error = "Attendance ID already exists.";
    } else {
        $qrFilename = "qr_" . $AttendanceID . ".png";
        $qrPath = "../uploads/qr/" . $qrFilename;

        if (!is_dir('../uploads/qr')) {
            mkdir('../uploads/qr', 0777, true);
        }

        $qrLink = "http://localhost/mypetakom-1/module3/attendance_form.php?aid=" . urlencode($AttendanceID);
        QRcode::png($qrLink, $qrPath, QR_ECLEVEL_L, 4);

        $stmt = $conn->prepare("INSERT INTO attendance_slot (AttendanceID, EventID, Location, AttendanceStartTime, AttendanceEndTime, QRCodeAttendance, AttendanceDate) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $AttendanceID, $EventID, $Location, $StartTime, $EndTime, $qrFilename, $Date);

        if ($stmt->execute()) {
            $success = true;
            // Redirect EA to the manage attendance slots page after successful creation
            header("Location: manage_attendance.php?status=created");
            exit();
        } else {
            $error = "Database error: {$stmt->error}";
        }

        $stmt->close();
    }

    $check->close();
}

$events = $conn->query("SELECT EventID, Title FROM event");
?>

<div class="main">
    <div class="container">
        <h2>Create Attendance Slot</h2>

        <?php if (isset($success) && $success): ?>
            <div style="background-color:#d4edda;color:#155724;padding:10px 15px;margin:15px 0;border-left:5px solid #28a745;">
                ✅ Attendance slot created successfully.
            </div>
        <?php elseif (isset($error)): ?>
            <div style="background-color:#f8d7da;color:#721c24;padding:10px 15px;margin:15px 0;border-left:5px solid #dc3545;">
                ❌ <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="create_slot" value="1">

            <div class="form-group">
                <label>Attendance ID</label>
                <input type="text" name="AttendanceID" class="form-control" value="<?= $AttendanceID ?>" readonly>
            </div>

            <div class="form-group">
                <label>Event</label>
                <select name="EventID" class="form-control" required>
                    <option value="">-- Select Event --</option>
                    <?php while ($e = $events->fetch_assoc()): ?>
                        <option value="<?= $e['EventID'] ?>">[<?= $e['EventID'] ?>] <?= $e['Title'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Start Time</label>
                <input type="time" name="StartTime" class="form-control" required>
            </div>

            <div class="form-group">
                <label>End Time</label>
                <input type="time" name="EndTime" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Attendance Date</label>
                <input type="date" name="AttendanceDate" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary">Create Slot</button>
        </form>
    </div>
</div>

<?php $conn->close(); ?>
