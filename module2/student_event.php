<?php
session_start();
include '../layout/dashboard_layout.php';
include '../db/connect.php';

if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== 'ST') {
    header("Location: ../module1/index.php");
    exit();
}

// Filter handling
$filterStatus = $_GET['status'] ?? '';
$filterMonth = $_GET['month'] ?? '';

$query = "SELECT * FROM event WHERE 1";
if ($filterStatus !== '') {
    $query .= " AND Status = '$filterStatus'";
}
if ($filterMonth !== '') {
    $query .= " AND MONTH(Date) = '$filterMonth'";
}
$query .= " ORDER BY Date ASC, Time ASC";
$result = $conn->query($query);
?>

<!-- Custom Styles -->
<style>
    .event-card {
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 0 12px rgba(0, 0, 0, 0.1);
        background: white;
        transition: transform 0.3s ease;
    }
    .event-card:hover {
        transform: scale(1.02);
    }
    .event-date {
        color: white;
        padding: 10px;
        text-align: center;
    }
    .event-date h6 {
        margin: 0;
        font-size: 14px;
        letter-spacing: 1px;
    }
    .event-date h4 {
        margin: 0;
        font-size: 24px;
        font-weight: bold;
    }
</style>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<div class="container mt-5">
    <h2 class="mb-4">Available Events</h2>

    <!-- Filter Form -->
    <form method="get" class="row g-3 mb-4">
        <div class="col-md-4">
            <select class="form-select" name="status">
                <option value="">All Status</option>
                <option value="Upcoming" <?= $filterStatus == 'Upcoming' ? 'selected' : '' ?>>Upcoming</option>
                <option value="Completed" <?= $filterStatus == 'Completed' ? 'selected' : '' ?>>Completed</option>
                <option value="Cancelled" <?= $filterStatus == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>
        </div>
        <div class="col-md-4">
            <select class="form-select" name="month">
                <option value="">All Months</option>
                <?php
                foreach (range(1, 12) as $m) {
                    $monthName = date("F", mktime(0, 0, 0, $m, 1));
                    echo "<option value='$m' " . ($filterMonth == $m ? 'selected' : '') . ">$monthName</option>";
                }
                ?>
            </select>
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-primary">Filter</button>
        </div>
    </form>

    <!-- Event Cards -->
    <div class="row g-4">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $eventDate = date_create($row['Date']);
                $day = date_format($eventDate, 'd');
                $month = strtoupper(date_format($eventDate, 'M'));
                $timeFormatted = date("g:i A", strtotime($row['Time']));
                $eventID = $row['EventID'];

                // Card top color by status
                $statusColor = '#0d6efd'; // default blue for Upcoming
                if ($row['Status'] === 'Completed') {
                    $statusColor = '#198754'; // green
                } elseif ($row['Status'] === 'Cancelled') {
                    $statusColor = '#dc3545'; // red
                }

                // Modal header color
                $modalHeaderColor = $statusColor;
        ?>
            <div class="col-md-4">
                <div class="event-card">
                    <div class="event-date" style="background-color: <?= $statusColor ?>;">
                        <h6><?= $month ?></h6>
                        <h4><?= $day ?></h4>
                    </div>
                    <div class="p-3">
                        <h5><?= htmlspecialchars($row['Title']) ?></h5>
                        <p><strong>Location:</strong> <?= htmlspecialchars($row['Location']) ?></p>
                        <p><strong>Time:</strong> <?= $timeFormatted ?></p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#eventModal<?= $eventID ?>">View Details</button>
                    </div>
                </div>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="eventModal<?= $eventID ?>" tabindex="-1" aria-labelledby="eventModalLabel<?= $eventID ?>" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header" style="background-color: <?= $modalHeaderColor ?>;">
                            <h5 class="modal-title text-white" id="eventModalLabel<?= $eventID ?>"><?= htmlspecialchars($row['Title']) ?></h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p><strong>Description:</strong> <?= htmlspecialchars($row['Description']) ?></p>
                            <p><strong>Date:</strong> <?= $row['Date'] ?></p>
                            <p><strong>Time:</strong> <?= $timeFormatted ?></p>
                            <p><strong>Location:</strong> <?= htmlspecialchars($row['Location']) ?></p>
                            <p><strong>Status:</strong> <?= $row['Status'] ?></p>
                            <p><strong>Level:</strong> <?= $row['Level'] ?></p>
                        </div>
                        <div class="modal-footer">
                            <a href="event_detail.php?event_id=<?= $eventID ?>" class="btn btn-light">Full Details</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php
            }
        } else {
            echo '<p class="text-muted">No events found for the selected filters.</p>';
        }
        ?>
    </div>
</div>
