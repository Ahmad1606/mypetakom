<?php
session_start();
include '../layout/dashboard_layout.php';
include '../db/connect.php';

if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== 'PA') {
    header("Location: ../module1/index.php");
    exit();
}

$paID = $_SESSION['UserID'];
$message = '';

// Handle Approve or Reject
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['merit_id'])) {
    $meritID = $_POST['merit_id'];
    $status = $_POST['action'] === 'approve' ? 'Approved' : 'Rejected';

    $update = $conn->prepare("UPDATE merit_application SET Status = ?, ApprovedBy = ? WHERE MeritID = ?");
    $update->bind_param("sss", $status, $paID, $meritID);
    if ($update->execute()) {
        $message = '<div class="alert alert-success">Application has been ' . strtolower($status) . '.</div>';
    } else {
        $message = '<div class="alert alert-danger">Failed to update application.</div>';
    }
    $update->close();
}

// Fetch all merit applications
$query = $conn->query("
    SELECT m.MeritID, m.Status, m.SubmittedDate, e.Title AS EventTitle, u.Name AS AdvisorName
    FROM merit_application m
    JOIN event e ON m.EventID = e.EventID
    JOIN user u ON m.SubmittedBy = u.UserID
    ORDER BY m.SubmittedDate DESC
");
?>

<div class="p-4 bg-white shadow rounded-3">
  <h3>Manage Merit Applications</h3>
  <?= $message ?>

  <table class="table table-bordered table-hover mt-4">
    <thead class="table-light">
        <tr>
            <th>#</th>
            <th>Event Title</th>
            <th>Submitted By</th>
            <th>Submitted Date</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($query->num_rows === 0): ?>
            <tr><td colspan="6" class="text-center">No applications found.</td></tr>
        <?php else: ?>
            <?php $i = 1; while ($row = $query->fetch_assoc()) : ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($row['EventTitle']) ?></td>
                    <td><?= htmlspecialchars($row['AdvisorName']) ?></td>
                    <td><?= $row['SubmittedDate'] ?></td>
                    <td>
                        <?php
                            $statusClass = match ($row['Status']) {
                                'Approved' => 'text-success',
                                'Rejected' => 'text-danger',
                                default => 'text-warning'
                            };
                        ?>
                        <span class="<?= $statusClass ?> fw-bold"><?= $row['Status'] ?></span>
                    </td>
                    <td>
                        <?php if ($row['Status'] === 'Pending') : ?>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="merit_id" value="<?= $row['MeritID'] ?>">
                                <button name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
                                <button name="action" value="reject" class="btn btn-danger btn-sm">Reject</button>
                            </form>
                        <?php else: ?>
                            <em>No action</em>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php endif; ?>
    </tbody>
  </table>
</div>
