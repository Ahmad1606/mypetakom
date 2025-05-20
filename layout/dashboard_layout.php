<?php
// dashboard_layout.php
include '../db/config_all.php';
include '../db/connect.php';

$UserID = $_SESSION['UserID'];
$Role = $_SESSION['Role'];
$Name = "";

// Get user name
$table = ($Role === "ST") ? "user" : (($Role === "EA") ? "user" : (($Role === "PA") ? "user" : "user"));
$stmt = $conn->prepare("SELECT Name FROM $table WHERE UserID = ?");
$stmt->bind_param("s", $UserID);
$stmt->execute();
$stmt->bind_result($Name);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= $Role ?> Dashboard - MyPetakom</title>
    <link rel="stylesheet" href="../styles/style.css">
</head>
<body>

<div class="topbar">
    <!-- <div style="display:flex"><strong>MyPetakom</strong>
        <span> <img src="images/logoPetakom.png" alt="logo" with="50" height="50"></span>
    </div> -->
    <div class="container">
        <span class="text">MyPetakom</span>
        <img src="../images/logoPetakom.png" alt="Logo" with="65" height="65">
    </div>
    <div class="topbar-right">
        <span><?= htmlspecialchars($Role) ?>: <?= htmlspecialchars($Name) ?></span>
        <div class="avatar"><?= strtoupper(substr($Name, 0, 1)) ?></div>
    </div>
</div>

<div class="navmenu">
    <a href="#" class="active">Dashboard</a>
    <a href="">Events</a>
    <a href="">Committees</a>
    <a href="">Merit Application</a>
    <a href="">QR Codes</a>
    <a href="logout.php" class="logout">Logout</a>
</div>

<div class="sidebar-panel">
    <h4><?= ucfirst($Role) ?> Menu</h4>

    <?php if ($Role === "ST"): ?>
        <a href="#">👤 User Profile</a>
        <a href="student_membership.php">📋 Register Membership</a>
        <a href="#">📁 My Events</a>
        <a href="#">📊 Attendance</a>
        <a href="#">Merit</a>
    <?php elseif ($Role === "PA"): ?>
        <a href="manage_membership.php">⚙️ Manage Membership</a>
        <a href="#">👤 User Profiles</a>
    <?php elseif ($Role === "EA"): ?>
        <a href="#">👤 User Profile</a>
        <a href="">Advisor Dashboard</a>
        <a href="#">📅 Event Management</a>
        <a href="">Commitee Management</a>
        <a href="">Merit application</a>
        <a href="#">🧾 QR Codes</a>
    <?php endif; ?>
</div>

</body>
</html>
<?php
