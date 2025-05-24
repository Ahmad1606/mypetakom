<?php
include '../db/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_event'])) {
    $eventID = $_POST['event_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $location = $_POST['location'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE event SET Title = ?, Description = ?, Date = ?, Time = ?, Location = ?, Status = ? WHERE EventID = ?");
    $stmt->bind_param("sssssss", $title, $description, $date, $time, $location, $status, $eventID);
    
    if ($stmt->execute()) {
        header("Location: manage_event.php?updated=1");
    } else {
        header("Location: manage_event.php?updated=0");
    }
    exit;
}
?>
