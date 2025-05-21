<?php
include '../layout/dashboard_layout.php';
include '../db/connect.php';

// Handle event submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_event'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $location = $_POST['location'];
    $status = $_POST['status'];
    $UserID = $_SESSION['UserID'];

    // Generate EventID like EV001
    $res = $conn->query("SELECT EventID FROM event ORDER BY EventID DESC LIMIT 1");
    $lastId = ($res && $row = $res->fetch_assoc()) ? intval(substr($row['EventID'], 2)) + 1 : 1;
    $eventID = 'E' . str_pad($lastId, 3, '0', STR_PAD_LEFT);

    $stmt = $conn->prepare("INSERT INTO event (EventID, Title, Description, Date, Time, Location, Status, UserID) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $eventID, $title, $description, $date, $time, $location, $status, $UserID);
    $stmt->execute();
    $stmt->close();

    header("Location: manage_event.php?success=1");
    exit;
}
?>

<!-- Page-specific content here -->
<div class="p-4 bg-white shadow rounded-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="fw-bold">Event Management</h3>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createEventModal">
      + Create New Event
    </button>
  </div>
</div>

<div class="p-4">
  <!-- Tabs (optional functionality later) -->
  <ul class="nav nav-tabs mb-4">
    <li class="nav-item">
      <a class="nav-link active" href="#">All Events</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="#">Active</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="#">Postponed</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="#">Cancelled</a>
    </li>
  </ul>

  <!-- Event Cards -->
  <?php
  $result = $conn->query("SELECT * FROM event ORDER BY Date DESC");
  while ($row = $result->fetch_assoc()):
  ?>
    <div class="card shadow-sm mb-3">
      <div class="card-body d-flex justify-content-between">
        <div>
          <span class="badge bg-<?= $row['Status'] === 'Approved' ? 'success' : 'danger' ?>">
            <?= $row['Status'] ?>
          </span>
          <h5 class="mt-2"><?= htmlspecialchars($row['Title']) ?></h5>
          <p class="mb-1"><i class="bi bi-calendar-event"></i> <?= $row['Date'] ?> | <?= $row['Time'] ?></p>
          <p class="mb-1"><i class="bi bi-geo-alt-fill"></i> <?= htmlspecialchars($row['Location']) ?></p>
          <p><?= nl2br(htmlspecialchars($row['Description'])) ?></p>
          <div class="mt-2">
            <button class="btn btn-sm btn-primary">Edit</button>
            <button class="btn btn-sm btn-warning text-dark">Postpone</button>
            <button class="btn btn-sm btn-danger">Cancel</button>
          </div>
        </div>
        <div class="text-center">
          <img src="placeholder_qr.png" alt="QR Code" style="width: 80px;">
          <p class="small mt-1">Scan for details</p>
        </div>
      </div>
    </div>
  <?php endwhile; ?>

</div>

<!-- Modal: Create Event -->
<div class="modal fade" id="createEventModal" tabindex="-1" aria-labelledby="createEventLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
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
        <!-- <div class="mb-3">
          <label class="form-label">Merit Status</label>
          <select name="merit_status" class="form-select" required>
            <option value="Approved">Approved</option>
            <option value="Pending">Pending</option>
            <option value="Rejected">Rejected</option>
          </select>
        </div> -->
      </div>
      <div class="modal-footer">
        <button type="submit" name="add_event" class="btn btn-success">Save</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


<!-- Close tags from dashboard_layout.php -->
      </div> <!-- .col-md-9 -->
    </div> <!-- .row -->
  </div> <!-- .container-fluid -->
</body>
</html>



