<?php
session_start();
include '../layout/dashboard_layout.php';
include '../db/connect.php';

if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== 'EA') {
    header("Location: ../module1/index.php");
    exit();
}

$advisorID = $_SESSION['UserID'];

// Stats
$totalEvents = $conn->query("SELECT COUNT(*) AS total FROM event WHERE UserID = '$advisorID'")->fetch_assoc()['total'];
$activeEvents = $conn->query("SELECT COUNT(*) AS active FROM event WHERE UserID = '$advisorID' AND Status = 'Upcoming'")->fetch_assoc()['active'];
$committeeCount = $conn->query("SELECT COUNT(*) AS total FROM committee WHERE EventID IN (SELECT EventID FROM event WHERE UserID = '$advisorID')")->fetch_assoc()['total'];
$meritStats = $conn->query("SELECT COUNT(*) AS total, SUM(Status = 'Approved') AS approved FROM merit_application WHERE SubmittedBy = '$advisorID'")->fetch_assoc();

$eventParticipation = $conn->query("
    SELECT e.Title, COUNT(a.UserID) AS participants 
    FROM event e 
    LEFT JOIN attendance a ON e.EventID = a.EventID 
    WHERE e.UserID = '$advisorID' 
    GROUP BY e.Title
");

$meritDistribution = $conn->query("
    SELECT Status, COUNT(*) AS count 
    FROM merit_application 
    WHERE SubmittedBy = '$advisorID' 
    GROUP BY Status
");
?>

<!-- Styles and Scripts -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container mt-4">
    <h2 class="mb-2">Event Advisor Dashboard</h2>
    <p class="text-muted">Welcome back, <?= $_SESSION['UserID'] ?>!</p>

    <!-- Stat Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <div class="mb-2">
                        <span class="rounded-circle bg-light p-3">
                            <i class="fa-solid fa-calendar-days fa-xl text-primary"></i>
                        </span>
                    </div>
                    <h6 class="fw-bold text-dark">Total Events</h6>
                    <h4 class="fw-semibold"><?= $totalEvents ?></h4>
                    <small class="text-success"><i class="fa-solid fa-arrow-up"></i> 2 from last month</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <div class="mb-2">
                        <span class="rounded-circle bg-light p-3">
                            <i class="fa-solid fa-sun fa-xl text-warning"></i>
                        </span>
                    </div>
                    <h6 class="fw-bold text-dark">Active Events</h6>
                    <h4 class="fw-semibold"><?= $activeEvents ?></h4>
                    <small class="text-success"><i class="fa-solid fa-arrow-up"></i> 1 from last month</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <div class="mb-2">
                        <span class="rounded-circle bg-light p-3">
                            <i class="fa-solid fa-users fa-xl text-info"></i>
                        </span>
                    </div>
                    <h6 class="fw-bold text-dark">Committee Members</h6>
                    <h4 class="fw-semibold"><?= $committeeCount ?></h4>
                    <small class="text-success"><i class="fa-solid fa-arrow-up"></i> 5 from last month</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <div class="mb-2">
                        <span class="rounded-circle bg-light p-3">
                            <i class="fa-solid fa-trophy fa-xl text-warning"></i>
                        </span>
                    </div>
                    <h6 class="fw-bold text-dark">Merit Applications</h6>
                    <h4 class="fw-semibold"><?= $meritStats['total'] ?></h4>
                    <small class="text-muted"><?= $meritStats['approved'] ?> Approved</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-bold">Event Participation</div>
                <div class="card-body">
                    <canvas id="eventChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-bold">Merit Applications</div>
                <div class="card-body">
                    <canvas id="meritChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const eventChart = document.getElementById('eventChart').getContext('2d');
new Chart(eventChart, {
    type: 'bar',
    data: {
        labels: [<?php while ($row = $eventParticipation->fetch_assoc()) echo "'{$row['Title']}',"; ?>],
        datasets: [{
            label: 'Participants',
            data: [<?php mysqli_data_seek($eventParticipation, 0); while ($row = $eventParticipation->fetch_assoc()) echo "{$row['participants']},"; ?>],
            backgroundColor: 'rgba(54, 162, 235, 0.7)'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        }
    }
});

const meritChart = document.getElementById('meritChart').getContext('2d');
new Chart(meritChart, {
    type: 'pie',
    data: {
        labels: [<?php while ($row = $meritDistribution->fetch_assoc()) echo "'{$row['Status']}',"; ?>],
        datasets: [{
            label: 'Applications',
            data: [<?php mysqli_data_seek($meritDistribution, 0); while ($row = $meritDistribution->fetch_assoc()) echo "{$row['count']},"; ?>],
            backgroundColor: ['#28a745', '#ffc107', '#dc3545']
        }]
    },
    options: {
        responsive: true
    }
});
</script>
