<?php
include '../layout/dashboard_layout.php';
include '../db/connect.php';
require_once '../lib/phpqrcode/qrlib.php';

if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== 'EA') {
    header("Location: ../module1/index.php");
    exit();
}

$UserID = $_SESSION['UserID'];

// Add Event
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_event'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $location = $_POST['location'];
    $status = $_POST['status'];
    $level = $_POST['level'];

    $res = $conn->query("SELECT EventID FROM event ORDER BY EventID DESC LIMIT 1");
    $lastId = ($res && $row = $res->fetch_assoc()) ? intval(substr($row['EventID'], 1)) + 1 : 1;
    $eventID = 'E' . str_pad($lastId, 3, '0', STR_PAD_LEFT);

    $approvalLetter = '';
    $uploadDir = '../uploads/approvalLetters/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    if ($_FILES['approval_letter']['error'] === 0) {
        $ext = pathinfo($_FILES['approval_letter']['name'], PATHINFO_EXTENSION);
        $approvalLetter = 'approval_' . $eventID . '.' . $ext;
        move_uploaded_file($_FILES['approval_letter']['tmp_name'], $uploadDir . $approvalLetter);
    }

    $stmt = $conn->prepare("INSERT INTO event (EventID, Title, Description, Date, Time, Location, Status, Level, UserID, ApprovalLetter) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssss", $eventID, $title, $description, $date, $time, $location, $status, $level, $UserID, $approvalLetter);
    $stmt->execute();
    $stmt->close();

    $qrFile = 'qr_' . $eventID . '.png';
    $qrPath = '../uploads/qrcodes/' . $qrFile;
    if (!is_dir('../uploads/qrcodes/')) mkdir('../uploads/qrcodes/', 0777, true);
    QRcode::png("../module2/view_event.php?id=$eventID", $qrPath, QR_ECLEVEL_L, 4);

    $stmt = $conn->prepare("UPDATE event SET QRCode = ? WHERE EventID = ?");
    $stmt->bind_param("ss", $qrFile, $eventID);
    $stmt->execute();
    $stmt->close();

    header("Location: manage_event.php?success=1");
    exit;
}

// Update Event
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_event'])) {
    $stmt = $conn->prepare("UPDATE event SET Title=?, Description=?, Date=?, Time=?, Location=?, Status=? WHERE EventID=?");
    $stmt->bind_param("sssssss", $_POST['title'], $_POST['description'], $_POST['date'], $_POST['time'], $_POST['location'], $_POST['status'], $_POST['event_id']);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_event.php?updated=1");
    exit;
}

// Delete Event
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_event'])) {
    $stmt = $conn->prepare("DELETE FROM event WHERE EventID = ?");
    $stmt->bind_param("s", $_POST['event_id']);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_event.php?deleted=1");
    exit;
}

// Generate QR
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_qr_id'])) {
    $eventID = $_POST['generate_qr_id'];
    $qrFile = 'qr_' . $eventID . '.png';
    $qrPath = '../uploads/qrcodes/' . $qrFile;
    QRcode::png("../module2/view_event.php?id=$eventID", $qrPath, QR_ECLEVEL_L, 4);

    $stmt = $conn->prepare("UPDATE event SET QRCode = ? WHERE EventID = ?");
    $stmt->bind_param("ss", $qrFile, $eventID);
    $stmt->execute();
    $stmt->close();

    header("Location: manage_event.php?qr_generated=1");
    exit;
}
?>


<div class="p-4 bg-white shadow rounded-3">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="fw-bold">Event Management</h3>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createEventModal">+ Create New Event</button>
  </div>

  <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">Event successfully registered!</div>
  <?php elseif (isset($_GET['updated'])): ?>
    <div class="alert alert-info">Event updated!</div>
  <?php elseif (isset($_GET['deleted'])): ?>
    <div class="alert alert-danger">Event deleted!</div>
  <?php elseif (isset($_GET['qr_generated'])): ?>
    <div class="alert alert-info">QR code generated!</div>
  <?php endif; ?>

  <ul class="nav nav-tabs mb-4" id="eventFilterTabs">
    <li class="nav-item"><a class="nav-link active" href="#" data-filter="all">All Events</a></li>
    <li class="nav-item"><a class="nav-link" href="#" data-filter="Completed">Completed</a></li>
    <li class="nav-item"><a class="nav-link" href="#" data-filter="Upcoming">Upcoming</a></li>
    <li class="nav-item"><a class="nav-link" href="#" data-filter="Cancelled">Cancelled</a></li>
    <li class="nav-item"><a class="nav-link" href="#" data-filter="search">Search</a></li>
  </ul>

  <div id="searchBar" class="mb-3" style="display: none;">
    <input type="text" id="searchInput" class="form-control" placeholder="Search by title...">
  </div>

  <?php
  $result = $conn->query("SELECT * FROM event WHERE UserID = '$UserID' ORDER BY Date DESC");
  while ($row = $result->fetch_assoc()):
    $qrPath = '../uploads/qrcodes/' . $row['QRCode'];
    $qrExists = file_exists($qrPath);
  ?>
<div class="card shadow-sm mb-3 event-card" data-status="<?= $row['Status'] ?>" data-title="<?= strtolower($row['Title']) ?>">
  <div class="card-body d-flex justify-content-between">
    <div>
      <span class="badge bg-<?= $row['Status'] === 'Completed' ? 'success' : ($row['Status'] === 'Upcoming' ? 'primary' : ($row['Status'] === 'Cancelled' ? 'danger' : 'secondary')) ?>"><?= $row['Status'] ?></span>
      <h5 class="mt-2"><?= htmlspecialchars($row['Title']) ?></h5>
      <p><i class="bi bi-calendar-event"></i> <?= $row['Date'] ?> | <?= $row['Time'] ?></p>
      <p><i class="bi bi-geo-alt-fill"></i> <?= htmlspecialchars($row['Location']) ?></p>
      <!-- <p><?= nl2br(htmlspecialchars($row['Description'])) ?></p>
      <?php if (!empty($row['ApprovalLetter'])): ?>
        <p><i class="bi bi-file-earmark-text"></i> <a href="../uploads/approvalLetters/<?= $row['ApprovalLetter'] ?>" target="_blank">View Approval Letter</a></p>
      <?php endif; ?> -->
      <div class="btn-group mt-2">
        <a href="view_event.php?id=<?= $row['EventID'] ?>" target="_blank" class="btn btn-sm btn-outline-primary">View</a>
        <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['EventID'] ?>">Edit</button>
        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $row['EventID'] ?>">Delete</button>
      </div>
    </div>
    <div class="text-center">
      <?php if ($qrExists): ?>
        <a href="view_event.php?id=<?= $row['EventID'] ?>" target="_blank">
          <img src="../uploads/qrcodes/<?= $row['QRCode'] ?>" width="80" alt="QR Code">
        </a>
        <p class="small mt-1 text-muted">Click or scan</p>
      <?php else: ?>
        <form method="post">
          <input type="hidden" name="generate_qr_id" value="<?= $row['EventID'] ?>">
          <button type="submit" class="btn btn-sm btn-outline-success">Generate QR</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Add Event Modal -->
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
            <option value="Completed">Completed</option>
            <option value="Upcoming">Upcoming</option>
            <option value="Cancelled">Cancelled</option>
          </select>
        </div>
        <div class="mb-2">
          <label class="form-label">Event Level</label>
          <select class="form-select" name="level" required>
            <option value="International">International</option>
            <option value="National">National</option>
            <option value="State">State</option>
            <option value="District">District</option>
            <option value="UMPSA">UMPSA</option>
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

<!-- Edit Modal -->
<div class="modal fade" id="editModal<?= $row['EventID'] ?>" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Event</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="event_id" value="<?= $row['EventID'] ?>">
        <div class="mb-2"><label class="form-label">Title</label><input type="text" name="title" class="form-control" value="<?= htmlspecialchars($row['Title']) ?>" required></div>
        <div class="mb-2"><label class="form-label">Description</label><textarea name="description" class="form-control" required><?= htmlspecialchars($row['Description']) ?></textarea></div>
        <div class="mb-2"><label class="form-label">Date</label><input type="date" name="date" class="form-control" value="<?= $row['Date'] ?>" required></div>
        <div class="mb-2"><label class="form-label">Time</label><input type="time" name="time" class="form-control" value="<?= $row['Time'] ?>" required></div>
        <div class="mb-2"><label class="form-label">Location</label><input type="text" name="location" class="form-control" value="<?= htmlspecialchars($row['Location']) ?>" required></div>
        <div class="mb-2">
          <label class="form-label">Status</label>
          <select name="status" class="form-select">
            <option value="Completed" <?= $row['Status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
            <option value="Upcoming" <?= $row['Status'] === 'Upcoming' ? 'selected' : '' ?>>Upcoming</option>
            <option value="Cancelled" <?= $row['Status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
          </select>
        </div>
        <div class="mb-2">
          <label class="form-label">Event Level</label>
          <select name="level" class="form-select">
            <option value="International" <?= $row['Level'] === 'International' ? 'selected' : '' ?>>International</option>
            <option value="National" <?= $row['Level'] === 'National' ? 'selected' : '' ?>>National</option>
            <option value="State" <?= $row['Level'] === 'State' ? 'selected' : '' ?>>State</option>
            <option value="District" <?= $row['Level'] === 'District' ? 'selected' : '' ?>>District</option>
            <option value="UMPSA" <?= $row['Level'] === 'UMPSA' ? 'selected' : '' ?>>UMPSA</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" name="update_event" class="btn btn-warning">Update</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal<?= $row['EventID'] ?>" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <form method="post" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Confirm Delete</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        Are you sure you want to delete the event "<strong><?= htmlspecialchars($row['Title']) ?></strong>"?
        <input type="hidden" name="event_id" value="<?= $row['EventID'] ?>">
      </div>
      <div class="modal-footer">
        <button type="submit" name="delete_event" class="btn btn-danger">Yes, Delete</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>
<?php endwhile; ?>
<script>
document.addEventListener("DOMContentLoaded", function () {
  const tabs = document.querySelectorAll('#eventFilterTabs .nav-link');
  const events = document.querySelectorAll('.event-card');
  const searchBar = document.getElementById('searchBar');
  const searchInput = document.getElementById('searchInput');

  tabs.forEach(tab => {
    tab.addEventListener('click', function (e) {
      e.preventDefault();
      tabs.forEach(t => t.classList.remove('active'));
      this.classList.add('active');
      const filter = this.dataset.filter;

      if (filter === 'search') {
        searchBar.style.display = 'block';
        events.forEach(e => e.style.display = 'block');
      } else {
        searchBar.style.display = 'none';
        searchInput.value = '';
        events.forEach(event => {
          const status = event.dataset.status;
          event.style.display = (filter === 'all' || status === filter) ? 'block' : 'none';
        });
      }
    });
  });

  searchInput.addEventListener('keyup', function () {
    const keyword = this.value.toLowerCase();
    events.forEach(event => {
      const title = event.dataset.title;
      event.style.display = title.includes(keyword) ? 'block' : 'none';
    });
  });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

      </div>
    </div> 
  </div> 
</body>
</html>
