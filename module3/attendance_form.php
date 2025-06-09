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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $Location = $_POST['Location'];
    $Time = date("H:i:s"); // This will now use Malaysia time

    // Check if student already checked in
    $check_query = "SELECT * FROM attendance WHERE AttendanceID = ? AND UserID = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ss", $AttendanceID, $UserID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $error = "You have already checked in.";
    } else {
        $insert_query = "INSERT INTO attendance (AttendanceID, UserID, EventID, AttendanceTime, Location, AttendanceStatus)
                         SELECT ?, ?, EventID, ?, ?, 'Pending' FROM attendance_slot WHERE AttendanceID = ?";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("sssss", $AttendanceID, $UserID, $Time, $Location, $AttendanceID);
        if ($stmt->execute()) {
            $success = true;
        } else {
            $error = "Error occurred during check-in.";
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
        <form method="POST">
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

