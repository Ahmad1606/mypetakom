<?php
session_start();
include '../layout/dashboard_layout.php';
include '../db/connect.php';

if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== 'EA') {
    header("Location: ../module1/index.php");
    exit();
}

$advisorID = $_SESSION['UserID'];

// Get all events created by the logged-in EA
$eventQuery = $conn->prepare("SELECT EventID, Title, Date FROM event WHERE UserID = ?");
$eventQuery->bind_param("s", $advisorID);
$eventQuery->execute();
$eventResult = $eventQuery->get_result();
?>

<div class="p-4 bg-white shadow rounded-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="fw-bold">Committees Management</h3>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#registerCommitteeModal">
        Register Committees</button>
  </div>

    <?php while ($event = $eventResult->fetch_assoc()): ?>
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <strong><?= htmlspecialchars($event['Title']) ?></strong> (<?= $event['Date'] ?>)
            </div>
            <div class="card-body p-3">
                <?php
                // For each event, get committee members and their roles
                $committeeQuery = $conn->prepare("
                    SELECT u.Name AS StudentName, cr.Description AS RoleName
                    FROM committee c
                    JOIN user u ON c.UserID = u.UserID
                    JOIN committee_role cr ON c.C_RoleID = cr.C_RoleID
                    WHERE c.EventID = ?
                ");
                $committeeQuery->bind_param("s", $event['EventID']);
                $committeeQuery->execute();
                $committeeResult = $committeeQuery->get_result();
                ?>

                <?php if ($committeeResult->num_rows > 0): ?>
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Role</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($member = $committeeResult->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($member['StudentName']) ?></td>
                                    <td><?= htmlspecialchars($member['RoleName']) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-muted">No committee members registered yet.</p>
                <?php endif; ?>
            </div>
        </div>
    <?php endwhile; ?>
</div>
