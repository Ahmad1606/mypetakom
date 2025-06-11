<?php
session_start();
include '../db/connect.php';

// ✅ Set timezone to Malaysia
date_default_timezone_set('Asia/Kuala_Lumpur');

$success = false;
$error = '';

if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== 'ST') {
    header("Location: ../module1/index.php");
    exit();
}

if (!isset($_GET['aid'])) {
    die("Invalid access.");
}

$AttendanceID = $_GET['aid'];
$UserID = $_SESSION['UserID'];

// Fetch event details to display on the form
$event_query = "SELECT e.Title, e.Date, e.Location FROM event e JOIN attendance_slot s ON e.EventID = s.EventID WHERE s.AttendanceID = ?";
$stmt = $conn->prepare($event_query);
$stmt->bind_param("s", $AttendanceID);
$stmt->execute();
$event_result = $stmt->get_result();
$event_data = $event_result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_student_id = $_POST['student_id'];  // Student ID entered by the student
    $entered_password = $_POST['password'];  // Password entered by the student
    $Location = $_POST['Location'];
    $Time = date("H:i:s"); // This will now use Malaysia time

    // Check if entered Student ID matches the session UserID
    if ($entered_student_id != $_SESSION['UserID']) {
        $error = "Student ID does not match the logged-in account.";
    } else {
        // Verify password
        $check_password_query = "SELECT Password FROM user WHERE UserID = ?";
        $stmt = $conn->prepare($check_password_query);
        $stmt->bind_param("s", $UserID);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_data = $result->fetch_assoc();
        $stored_password = $user_data['Password'];

        // Verify entered password matches stored password
        if (password_verify($entered_password, $stored_password)) {
            // Check if student already checked in
            $check_query = "SELECT * FROM attendance WHERE AttendanceID = ? AND UserID = ?";
            $stmt = $conn->prepare($check_query);
            $stmt->bind_param("ss", $AttendanceID, $UserID);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $error = "You have already checked in.";
            } else {
                // Compare entered location with event location
                if (trim($Location) === trim($event_data['Location'])) {
                    // If the location matches, automatically approve
                    $status = 'Approved';
                } else {
                    // If location doesn't match, reject the attendance
                    $status = 'Rejected';
                }

                // Insert the attendance data with auto-approved/rejected status
                $insert_query = "INSERT INTO attendance (AttendanceID, UserID, EventID, AttendanceTime, Location, AttendanceStatus)
                                 SELECT ?, ?, EventID, ?, ?, ? FROM attendance_slot WHERE AttendanceID = ?";
                $stmt = $conn->prepare($insert_query);
                $stmt->bind_param("ssssss", $AttendanceID, $UserID, $Time, $Location, $status, $AttendanceID);
                if ($stmt->execute()) {
                    $success = true;
                } else {
                    $error = "Error occurred during check-in.";
                }
            }
        } else {
            $error = "Incorrect password.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Attendance Check-In</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h3>Attendance Check-In Form</h3>

    <?php if ($success): ?>
        <div class="alert alert-success">✅ You have successfully checked in.</div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger">❌ <?= $error ?></div>
    <?php endif; ?>

    <?php if (!$success && !$error): ?>
        <h5>Event Details: <?= htmlspecialchars($event_data['Title']) ?> (<?= htmlspecialchars($event_data['Date']) ?>)</h5>

        <form method="POST">
            <div class="mb-3">
                <label for="student_id" class="form-label">Student ID:</label>
                <input type="text" class="form-control" name="student_id" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password:</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="Location" class="form-label">Location:</label>
                <input type="text" class="form-control" name="Location" required>
            </div>

            <div class="mb-3">
                <label for="Time" class="form-label">Check-In Time:</label>
                <input type="text" class="form-control" value="<?= date('H:i:s') ?>" readonly>
            </div>

            <button type="submit" class="btn btn-primary">Check In</button>
        </form>
    <?php endif; ?>

    <a href="list_slots_st.php" class="btn btn-secondary mt-3">← Back to Attendance Slots</a>
</div>
</body>
</html>



