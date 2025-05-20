<?php
session_start();
include '../db/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $UserID = $_POST['UserID'];
    $Password = $_POST['Password'];
    $Role = $_POST['Role'];

    $stmt = $conn->prepare("SELECT Password FROM User WHERE UserID = ? AND Role = ?");
    $stmt->bind_param("ss", $UserID, $Role);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($storedPassword);
        $stmt->fetch();

        if ($Password === $storedPassword) {
            $_SESSION['UserID'] = $UserID;
            $_SESSION['Role'] = $Role;

            // Redirect to role-specific dashboard
            switch ($Role) {
                case 'PA':
                    header("Location: admin_dashboard.php");
                    break;
                case 'EA':
                    header("Location: ../module2/advisor_dashboard.php");
                    break;
                case 'ST':
                    header("Location: student_dashboard.php");
                    break;
            }
            exit();
        } else {
            $_SESSION['LoginError'] = "Your password is Incorrect.";
        }
    } else {
        $_SESSION['LoginError'] = "User ID or Role not found.";
    }

    $stmt->close();
    header("Location: index.php");
    exit();
}
?>
