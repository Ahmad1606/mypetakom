<?php
session_start();
include '../db/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_id'])) {
    $eventID = $_POST['event_id'];

    $stmt = $conn->prepare("DELETE FROM committee WHERE EventID = ?");
    $stmt->bind_param("s", $eventID);
    $stmt->execute();
    $stmt->close();

    header("Location: manage_committeeV2.php?msg=delete_success");
    exit();
}
header("Location: manage_committeeV2.php?msg=delete_fail");
exit();
