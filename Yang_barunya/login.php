<?php
session_start();
include 'connect.php';
// include 'config_all.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $UserID = $_POST['UserID'];
    $Password = $_POST['Password'];
    $Role = $_POST['Role'];

    $stmt = $conn->prepare("SELECT Password FROM user WHERE UserID = ? AND Role = ?");
    $stmt->bind_param("ss", $UserID, $Role);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($storedPassword);
        $stmt->fetch();

        if ($Password === $storedPassword) {
            $_SESSION['UserID'] = $UserID;
            $_SESSION['Role'] = $Role;

            // Redirect by role
            switch ($Role) {
                case 'ST':
                    header("Location: student_dashboard.php");
                    break;
                case 'EA':
                    header("Location: advisor_dashboard.php");
                    break;
                case 'PA':
                    header("Location: admin_dashboard.php");
                    break;
            }
            exit();
        } else {
            $_SESSION['LoginError'] = "❌ Incorrect password.";
        }
    } else {
        $_SESSION['LoginError'] = "❌ User ID or role not found.";
    }

    $stmt->close();
    header("Location: index.php");
    exit();
}
?>
