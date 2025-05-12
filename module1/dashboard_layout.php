<?php
// dashboard_layout.php
include 'config_all.php';
include 'connect.php';

$UserID = $_SESSION['UserID'];
$Role = $_SESSION['Role'];
$Name = "";

// Get user name
$stmt = $conn->prepare("SELECT Name FROM " . str_replace(" ", "_", ucfirst($Role)) . " WHERE UserID = ?");
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
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="topbar">
    <div><strong>MyPetakom</strong></div>
    <div class="topbar-right">
        <span><?= htmlspecialchars($Role) ?>: <?= htmlspecialchars($Name) ?></span>
        <div class="avatar"><?= strtoupper(substr($Name, 0, 1)) ?></div>
    </div>
</div>

<div class="navmenu">
    <a href="#" class="active">Dashboard</a>
    <a href="logout.php" class="logout">Logout</a>
</div>

<div class="sidebar-panel">
    <h4><?= ucfirst($Role) ?> Menu</h4>

    <?php if ($Role === "Student"): ?>
        <a href="#">👤 User Profile</a>
        <a href="student_membership.php">📋 Register Membership</a>
        <a href="#">📁 My Events (soon)</a>
        <a href="#">📊 Attendance (soon)</a>
    <?php elseif ($Role === "Petakom administrator"): ?>
        <a href="manage_membership.php">⚙️ Manage Membership</a>
        <a href="#">👤 User Profiles</a>
    <?php elseif ($Role === "Event advisor"): ?>
        <a href="#">👤 User Profile</a>
        <a href="#">📅 Event Management</a>
        <a href="#">🧾 QR Codes</a>
    <?php endif; ?>
</div>

</body>
</html>
<?php
