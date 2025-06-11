<?php
session_start();
include '../layout/dashboard_layout.php';
include '../db/connect.php';

// Fetch counts from 3 users
$totalStudents = $conn->query("SELECT COUNT(*) FROM User WHERE Role = 'ST'")->fetch_row()[0];
$totalAdvisors = $conn->query("SELECT COUNT(*) FROM User WHERE Role = 'EA'")->fetch_row()[0];
$totalAdmins = $conn->query("SELECT COUNT(*) FROM User WHERE Role = 'PA'")->fetch_row()[0];

// Fetch membership status for students
$statusCounts = [
    'approved' => $conn->query("SELECT COUNT(*) FROM Membership WHERE Status = 'Approved'")->fetch_row()[0],
    'pending' => $conn->query("SELECT COUNT(*) FROM Membership WHERE Status = 'Pending'")->fetch_row()[0],
    'rejected' => $conn->query("SELECT COUNT(*) FROM Membership WHERE Status = 'Rejected'")->fetch_row()[0],
];
?>
<h2>Welcome, <?= htmlspecialchars($Name) ?></h2>
<p>Welcome to the Petakom Administrator Dashboard. Here you can view totals for students, advisors, and administrators. The dashboard also displays student membership status.</p>

<div class="container mt-4">
  <div class="row">
    <!-- Stats cards -->
    <div class="col-lg-4 col-md-6 mb-4">
      <div class="card p-3">
        <h5>Total Students</h5>
        <h3><?= $totalStudents ?></h3>
      </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-4">
      <div class="card p-3">
        <h5>Total Event Advisors</h5>
        <h3><?= $totalAdvisors ?></h3>
      </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-4">
      <div class="card p-3">
        <h5>Total Administrator</h5>
        <h3><?= $totalAdmins ?></h3>
      </div>
    </div>
  </div>

  <!-- Pie Chart for Membership Status -->
  <div class="row">
    <div class="col-4">
      <h3>Student Membership Status</h3>
      <canvas id="membershipStatusChart" width="100" height="100"></canvas> <!-- Smaller canvas -->
    </div>
  </div>
</div>

<!-- Pie Chart javascript -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const ctx = document.getElementById('membershipStatusChart').getContext('2d');
  const membershipStatusChart = new Chart(ctx, {
    type: 'pie',
    data: {
      labels: ['Approved', 'Pending', 'Rejected'],
      datasets: [{
        data: [<?= $statusCounts['approved'] ?>, <?= $statusCounts['pending'] ?>, <?= $statusCounts['rejected'] ?>],
        backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
        borderColor: ['#fff', '#fff', '#fff'],
        borderWidth: 1
      }]
    }
  });
</script>
