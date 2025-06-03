<?php
session_start();
include '../db/connect.php';

if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== 'EA') {
    header("Location: ../module1/index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_id'], $_POST['committee'])) {
    $eventID = $_POST['event_id'];
    $committees = $_POST['committee'];
    $assignedUsers = [];

    // Get event date
    $dateStmt = $conn->prepare("SELECT Date FROM event WHERE EventID = ?");
    $dateStmt->bind_param("s", $eventID);
    $dateStmt->execute();
    $dateStmt->bind_result($eventDate);
    $dateStmt->fetch();
    $dateStmt->close();

    // Step 1: DELETE old committee data
    $delStmt = $conn->prepare("DELETE FROM committee WHERE EventID = ?");
    $delStmt->bind_param("s", $eventID);
    $delStmt->execute();
    $delStmt->close();

    // Step 2: INSERT updated committee data
    foreach ($committees as $roleID => $value) {
        $userIDs = is_array($value) ? $value : [$value];

        foreach ($userIDs as $userID) {
            if (empty($userID)) continue;

            // Rule 1: avoid same student in multiple roles
            if (in_array($userID, $assignedUsers)) {
                header("Location: manage_committeeV2.php?msg=role_duplicate");
                exit();
            }
            $assignedUsers[] = $userID;

            // Rule 2: avoid same date conflict with other events
            $conflictStmt = $conn->prepare("
                SELECT c.CommitteeID FROM committee c
                JOIN event e ON c.EventID = e.EventID
                WHERE c.UserID = ? AND e.Date = ? AND c.EventID != ?
            ");
            $conflictStmt->bind_param("sss", $userID, $eventDate, $eventID);
            $conflictStmt->execute();
            $conflictResult = $conflictStmt->get_result();

            if ($conflictResult->num_rows > 0) {
                header("Location: manage_committeeV2.php?msg=date_conflict");
                exit();
            }

            // Insert new row
            $committeeID = 'C' . bin2hex(random_bytes(5));
            $stmt = $conn->prepare("INSERT INTO committee (CommitteeID, EventID, C_RoleID, UserID) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $committeeID, $eventID, $roleID, $userID);
            $stmt->execute();
            $stmt->close();
        }
    }

    header("Location: manage_committeeV2.php?msg=edit_success");
    exit();
}

header("Location: manage_committeeV2.php?msg=edit_fail");
exit();
