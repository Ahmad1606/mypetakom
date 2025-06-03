<?php
session_start();
include '../layout/dashboard_layout.php';
include '../db/connect.php';

if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== 'EA') {
    header("Location: ../module1/index.php");
    exit();
}

$advisorID = $_SESSION['UserID'];
$Role = $_SESSION['Role'];
$message = '';

// Handle reapply submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reapply_merit_id'])) {
    $meritID = $_POST['reapply_merit_id'];
    $submittedDate = date('Y-m-d');

    // Assign new random PA
    $approverQuery = $conn->query("SELECT UserID FROM user WHERE Role = 'PA' ORDER BY RAND() LIMIT 1");
    $newApprover = $approverQuery->fetch_assoc()['UserID'];

    $reapply = $conn->prepare("UPDATE merit_application SET Status = 'Pending', SubmittedDate = ?, ApprovedBy = ? WHERE MeritID = ?");
    $reapply->bind_param("sss", $submittedDate, $newApprover, $meritID);
    if ($reapply->execute()) {
        $message = '<div class="alert alert-success">Reapplied successfully. Status set to Pending.</div>';
    } else {
        $message = '<div class="alert alert-danger">Failed to reapply.</div>';
    }
    $reapply->close();
}

// Handle new merit submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_id'])) {
    $eventID = $_POST['event_id'];
    $submittedDate = date('Y-m-d');
    $meritID = uniqid('M');

    // Assign random PA
    $approverQuery = $conn->query("SELECT UserID FROM user WHERE Role = 'PA' ORDER BY RAND() LIMIT 1");
    $approver = $approverQuery->fetch_assoc()['UserID'];

    // Prevent duplicates
    $check = $conn->prepare("SELECT * FROM merit_application WHERE EventID = ?");
    $check->bind_param("s", $eventID);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $message = '<div class="alert alert-warning">Merit application already exists for this event.</div>';
    } else {
        $insert = $conn->prepare("INSERT INTO merit_application (MeritID, Status, SubmittedDate, SubmittedBy, ApprovedBy, EventID) VALUES (?, 'Pending', ?, ?, ?, ?)");
        $insert->bind_param("sssss", $meritID, $submittedDate, $advisorID, $approver, $eventID);
        if ($insert->execute()) {
            $message = '<div class="alert alert-success">Merit application submitted!</div>';
        } else {
            $message = '<div class="alert alert-danger">Failed to submit application.</div>';
        }
        $insert->close();
    }
    $check->close();
}

// Get advisor's own merit applications
$apps = $conn->prepare("
    SELECT m.MeritID, e.Title, m.Status, m.SubmittedDate, u.Name AS Approver
    FROM merit_application m
    JOIN event e ON m.EventID = e.EventID
    LEFT JOIN user u ON m.ApprovedBy = u.UserID
    WHERE m.SubmittedBy = ?
    ORDER BY m.SubmittedDate DESC
");
$apps->bind_param("s", $advisorID);
$apps->execute();
$appList = $apps->get_result();

// Get ALL events created by advisor, including merit status
$events = $conn->prepare("
    SELECT 
        e.EventID, 
        e.Title,
        COALESCE(m.Status, 'Not Applied') AS MeritStatus
    FROM event e
    LEFT JOIN merit_application m ON m.EventID = e.EventID
    WHERE e.UserID = ?
    ORDER BY e.EventID DESC
");
$events->bind_param("s", $advisorID);
$events->execute();
$eventOptions = $events->get_result();
?>

<!-- Page-specific content here -->
<div class="p-4 bg-white shadow rounded-3">
  <div class="container mt-5">
      <h3 class="mb-4">Merit Application</h3>
      <?= $message ?>

      <!-- Apply for New Merit -->
      <form method="POST" class="mb-5">
          <div class="row g-3 align-items-center">
              <div class="col-md-6 position-relative">
                  <label class="form-label">Select an Event to Apply Merit</label>
                  <select name="event_id" class="form-select" style="direction: ltr;" required>
                      <option value="">-- Choose Event --</option>
                      <?php if ($eventOptions->num_rows === 0): ?>
                          <option disabled>No events found</option>
                      <?php endif; ?>
                      <?php while ($row = $eventOptions->fetch_assoc()) : ?>
                          <option value="<?= $row['EventID'] ?>" 
                                  <?= $row['MeritStatus'] !== 'Not Applied' ? 'disabled' : '' ?>>
                              <?= $row['Title'] ?> (<?= $row['EventID'] ?>) 
                              <?= $row['MeritStatus'] !== 'Not Applied' ? ' - Already Applied' : '' ?>
                          </option>
                      <?php endwhile; ?>
                  </select>
              </div>
              <div class="col-md-3">
                  <button type="submit" class="btn btn-primary mt-4">Apply</button>
              </div>
          </div>
      </form>

      <!-- Application List -->
      <h5>Your Merit Applications</h5>
      <table class="table table-bordered table-hover mt-3">
          <thead class="table-light">
              <tr>
                  <th>#</th>
                  <th>Event Title</th>
                  <th>Submitted Date</th>
                  <th>Status</th>
                  <th>Approved By / Action</th>
              </tr>
          </thead>
          <tbody>
              <?php if ($appList->num_rows === 0): ?>
                  <tr><td colspan="5" class="text-center">No merit applications yet.</td></tr>
              <?php else: ?>
                  <?php $i = 1; while ($row = $appList->fetch_assoc()) : ?>
                      <tr>
                          <td><?= $i++ ?></td>
                          <td><?= htmlspecialchars($row['Title']) ?></td>
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
                              <?php if ($row['Status'] === 'Pending'): ?>
                                  <!-- leave blank -->
                              <?php elseif ($row['Status'] === 'Rejected'): ?>
                                  <form method="POST">
                                      <input type="hidden" name="reapply_merit_id" value="<?= $row['MeritID'] ?>">
                                      <button type="submit" class="btn btn-sm btn-warning">Reapply</button>
                                  </form>
                              <?php else: ?>
                                  <?= $row['Approver'] ?>
                              <?php endif; ?>
                          </td>
                      </tr>
                  <?php endwhile; ?>
              <?php endif; ?>
          </tbody>
      </table>
  </div>
</div>
