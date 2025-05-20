<?php
// dashboard_layout.php
include '../db/config_all.php';
include '../db/connect.php';

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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
</head>
<body>
    <!-- <header>
       <div class="container-fluid text-white" style="background-color: #0f214d;">
        <div class="row">
            <div class="d-flex align-items-center gap-3 text-white p-3">
                <img src="../images/logoPetakom.png" alt="Petakom Logo" width="70" height="70">
                <h4 class="mb-0">MyPetakom</h4>
            </div>
            </div>
            <div class="col text-end">
                <span><?= htmlspecialchars($Role) ?>: <?= htmlspecialchars($Name) ?></span>
                <div class="avatar"><?= strtoupper(substr($Name, 0, 1)) ?></div>
            </div>
        </div>
        </div>
    </header>
    <nav class="navbar" style="background-color: #2ba3ec;" data-bs-theme="light">
        <ul class="nav nav-pills">
        <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="#">Active</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#">Link</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#">Link</a>
        </li>
        <li class="nav-item">
            <a class="nav-link disabled" aria-disabled="true">Disabled</a>
        </li>
        </ul>
    </nav> -->
    <div class="text-white py-3 px-4" style="background-color: #0f214d">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <!-- Left: Logo and Title -->
        <div class="d-flex align-items-center">
        <img src="../images/logoPetakom.png" alt="Logo" height="50" class="me-2">
        <h5 class="mb-0">MyPetakom</h5>
        </div>

        <!-- Right: Event advisor info -->
        <div class="d-flex align-items-center">
        <span class="me-3"><?= htmlspecialchars($Role) ?>: <?= htmlspecialchars($Name) ?></span>
        <span class="bg-primary rounded-circle text-white text-center" style="width:35px; height:35px; line-height:35px;"><?= strtoupper(substr($Name, 0, 1)) ?></span>
        </div>
    </div>
    </div>

    <!-- Navigation Bar -->
    <div class="border border-primary rounded" style="background-color: #ebfcff;">
    <div class="container-fluid">
        <ul class="nav nav-pills py-2 px-3">
        <li class="nav-item">
            <a class="nav-link active text-white" href="#"><b>Dashboard</b></a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-primary" href="#"><b>Events</b></a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-primary" href="#"><b>Committees</b></a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-primary" href="#"><b>Merit Application</b></a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-primary" href="#"><b>QR Codes</b></a>
        </li>
        </ul>
    </div>
    </div>
    

    
</body>
</html>
<?php 
