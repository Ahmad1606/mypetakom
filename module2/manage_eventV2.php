<?php
include '../layout/dashboard_layout.php';
include '../db/connect.php';
require_once '../lib/phpqrcode/qrlib.php'; // QR code library

$UserID = $_SESSION['UserID'];

// Handle event submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_event'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $location = $_POST['location'];
    $status = $_POST['status'];

    // File upload (Approval Letter)
    $approvalLetter = '';
    $uploadDir = '../uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    if (isset($_FILES['approval_letter']) && $_FILES['approval_letter']['error'] === 0) {
        $ext = pathinfo($_FILES['approval_letter']['name'], PATHINFO_EXTENSION);
        $approvalLetter = 'approval_' . time() . '_' . $UserID . '.' . $ext;
        move_uploaded_file($_FILES['approval_letter']['tmp_name'], $uploadDir . $approvalLetter);
    }

    // Generate EventID like EV001
    $res = $conn->query("SELECT EventID FROM event ORDER BY EventID DESC LIMIT 1");
    $lastId = ($res && $row = $res->fetch_assoc()) ? intval(substr($row['EventID'], 2)) + 1 : 1;
    $eventID = 'EV' . str_pad($lastId, 3, '0', STR_PAD_LEFT);

    // Insert event
    $stmt = $conn->prepare("INSERT INTO event (EventID, Title, Description, Date, Time, Location, Status, UserID, ApprovalLetter) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssss", $eventID, $title, $description, $date, $time, $location, $status, $UserID, $approvalLetter);
    $stmt->execute();
    $stmt->close();

    // Generate QR code
    $qrDir = '../uploads/qrcodes/';
    if (!is_dir($qrDir)) mkdir($qrDir, 0777, true);
    $qrPath = $qrDir . $eventID . '.png';
    QRcode::png("https://yourdomain.com/view_event.php?id=" . $eventID, $qrPath, QR_ECLEVEL_L, 4);

    $conn->query("UPDATE event SET QRCode = '" . $eventID . ".png' WHERE EventID = '$eventID'");

    header("Location: manage_event.php?success=1");
    exit;
}
?>

<div class="p-4 bg-white shadow rounded-3">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="fw-bold">Event Management</h3>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createEventModal">+ Create New Event</button>
  </div>
</div>

<div class="p-4">
  <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">Event successfully registered!</div>
  <?php endif; ?>

  <ul class="nav nav-tabs mb-4">
    <li class="nav-item"><a class="nav-link active" href="#">All Events</a></li>
    <li class="nav-item"><a class="nav-link" href="#">Active</a></li>
    <li class="nav-item"><a class="nav-link" href="#">Postponed</a></li>
    <li class="nav-item"><a class="nav-link" href="#">Cancelled</a></li>
  </ul>

  <?php
  $result = $conn->query("SELECT * FROM event WHERE UserID = '$UserID' ORDER BY Date DESC");
  while ($row = $result->fetch_assoc()):
  ?>
    <div class="card shadow-sm mb-3">
      <div class="card-body d-flex justify-content-between">
        <div>
          <span class="badge bg-<?= $row['Status'] === 'Approved' ? 'success' : 'danger' ?>"><?= $row['Status'] ?></span>
          <h5 class="mt-2"><?= htmlspecialchars($row['Title']) ?></h5>
          <p><i class="bi bi-calendar-event"></i> <?= $row['Date'] ?> | <?= $row['Time'] ?></p>
          <p><i class="bi bi-geo-alt-fill"></i> <?= htmlspecialchars($row['Location']) ?></p>
          <p><?= nl2br(htmlspecialchars($row['Description'])) ?></p>
          <?php if (!empty($row['ApprovalLetter'])): ?>
            <p><i class="bi bi-file-earmark-text"></i> <a href="../uploads/<?= $row['ApprovalLetter'] ?>" target="_blank">View Approval Letter</a></p>
          <?php endif; ?>
        </div>
        <div class="text-center">
          <?php if (!empty($row['QRCode'])): ?>
            <img src="../uploads/qrcodes/<?= $row['QRCode'] ?>" width="80" alt="QR Code">
            <p class="small mt-1">Scan for details</p>
          <?php else: ?>
            <span class="text-muted small">No QR Code</span>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php endwhile; ?>
</div>

<!-- Modal -->
<div class="modal fade" id="createEventModal" tabindex="-1" aria-labelledby="createEventLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" enctype="multipart/form-data" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="createEventLabel">Create New Event</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-2">
          <label class="form-label">Event Title</label>
          <input type="text" class="form-control" name="title" required>
        </div>
        <div class="mb-2">
          <label class="form-label">Description</label>
          <textarea class="form-control" name="description" rows="3" required></textarea>
        </div>
        <div class="mb-2">
          <label class="form-label">Date</label>
          <input type="date" class="form-control" name="date" required>
        </div>
        <div class="mb-2">
          <label class="form-label">Time</label>
          <input type="time" class="form-control" name="time" required>
        </div>
        <div class="mb-2">
          <label class="form-label">Location</label>
          <input type="text" class="form-control" name="location" required>
        </div>
        <div class="mb-2">
          <label class="form-label">Status</label>
          <select class="form-select" name="status" required>
            <option value="Approved">Approved</option>
            <option value="Rejected">Rejected</option>
          </select>
        </div>
        <div class="mb-2">
          <label class="form-label">Upload Approval Letter (PDF)</label>
          <input type="file" class="form-control" name="approval_letter" accept=".pdf" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" name="add_event" class="btn btn-success">Save</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</div></div></div>
</body>
</html>
