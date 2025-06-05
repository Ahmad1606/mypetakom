<?php

include '../db/config_all.php';
include '../db/connect.php';

$UserID = $_SESSION['UserID'];
$Role = $_SESSION['Role']; // ST, EA, PA
$Name = "";

// Get user name (simplified since all roles read from `user`)
$stmt = $conn->prepare("SELECT Name FROM user WHERE UserID = ?");
$stmt->bind_param("s", $UserID);
$stmt->execute();
$stmt->bind_result($Name);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>MyPetakom</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

  <!-- Top Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark px-4" style="background-color: #0f214d;">
    <a class="navbar-brand d-flex align-items-center" href="#">
      <img src="../images/logoPetakom.png" alt="Logo" width="75" height="75" class="me-2">
      <span><b>MyPetakom</b></span>
    </a>
    <div class="ms-auto d-flex align-items-center">
      <span class="text-white me-3">
        <?= htmlspecialchars($Name) ?>
      </span>
      <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
        <?= ($Role === "ST" ? "ST" : ($Role === "PA" ? "PA" : "EA")) ?>
      </div>
    </div>
  </nav>

  <!-- Nav Pills + Logout -->
  <div class="bg-white border-bottom px-4 py-2">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
      <ul class="nav nav-pills mb-0">
        <li class="nav-item">
          <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active text-white' : 'text-primary' ?>" href="dashboard.php"><b>Dashboard</b></a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'events.php' ? 'active text-white' : 'text-primary' ?>" href="events.php"><b>Events</b></a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-primary" href="#"><b>Attendance</b></a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-primary" href="#"><b>Committees</b></a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-primary" href="#"><b>Merit Application</b></a>
        </li>
      </ul>
      <a href="../module1/logout.php" class="btn btn-danger rounded-pill px-4">Logout</a>
    </div>
  </div>

  <!-- Main Layout -->
  <div class="container-fluid mt-3">
    <div class="row">
      
      <!-- Sidebar -->
      <div class="col-md-3 col-lg-2 mb-4">
        <div class="card shadow-sm rounded-4">
          <div class="card-header bg-primary text-white fw-bold text-center">
            <?= ($Role === "ST" ? "Student" : ($Role === "PA" ? "Administrator" : "Event advisor")) ?> Menu
          </div>
          <div class="list-group list-group-flush">
            <?php if ($Role === "ST"): ?>
              <a href="edit_profile.php" class="list-group-item list-group-item-action border-0">
                <i class="bi bi-person-fill me-2"></i>User Profile
              </a>
              <a href="student_membership.php" class="list-group-item list-group-item-action border-0">
                <i class="bi bi-card-heading me-2"></i>Register Membership
              </a>
              <a href="../module2/student_event.php" class="list-group-item list-group-item-action border-0">
                <i class="bi bi-folder-fill me-2"></i>Events 
              </a>
              <a href="../module2/student_committee.php" class="list-group-item list-group-item-action border-0">
                <i class="bi bi-people-fill me-2"></i> My Committee
              </a>
              <a href="#" class="list-group-item list-group-item-action border-0">
                <i class="bi bi-calendar2-week me-2"></i>Attendance
              </a>

            <?php elseif ($Role === "PA"): ?>
              <a href="manage_membership.php" class="list-group-item list-group-item-action border-0">
                <i class="bi bi-gear-fill me-2"></i>Manage Membership
              </a>
              <a href="admin_profile.php" class="list-group-item list-group-item-action border-0">
                <i class="bi bi-people-fill me-2"></i>User Profiles
              </a>
              <a href="../module2/admin_event.php" class="list-group-item list-group-item-action border-0 ">
                <i class="bi bi-calendar-event-fill me-2"></i>Event Management
              </a>
              <a href="../module2/manage_merit.php" class="list-group-item list-group-item-action border-0">
                <i class="bi bi-journal me-2"></i>Merit Management
              </a>

            <?php elseif ($Role === "EA"): ?>
              <a href="../module1/edit_profile.php" class="list-group-item list-group-item-action border-0">
                <i class="bi bi-person-fill me-2"></i>User Profile
              </a>
              <a href="../module2/advisor_dashboard.php" class="list-group-item list-group-item-action border-0">
                <i class="bi bi-reception-4 me-2"></i>Advisor Dashboard
              </a>
              <a href="../module2/manage_event.php" class="list-group-item list-group-item-action border-0 ">
                <i class="bi bi-calendar-event-fill me-2"></i>Event Management
              </a>
              <a href="../module2/manage_committeeV2.php" class="list-group-item list-group-item-action border-0">
                <i class="bi bi-people-fill me-2"></i>Committee Management
              </a>
              <a href="../module2/merit_application.php" class="list-group-item list-group-item-action border-0">
                <i class="bi bi-journal me-2"></i>Merit Application
              </a>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Dynamic Content Section -->
      <div class="col-md-9 col-lg-10">
        
