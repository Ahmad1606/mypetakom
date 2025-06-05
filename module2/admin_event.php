<?php
include '../layout/dashboard_layout.php';
include '../db/connect.php';
require_once '../lib/phpqrcode/qrlib.php';

// QR generation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_qr_id'])) {
    $eventID = $_POST['generate_qr_id'];
    $qrFile = 'qr_' . $eventID . '.png';
    $qrPath = '../uploads/qrcodes/' . $qrFile;
    QRcode::png("../module2/view_event.php?id=$eventID", $qrPath, QR_ECLEVEL_L, 4);

    $stmt = $conn->prepare("UPDATE event SET QRCode = ? WHERE EventID = ?");
    $stmt->bind_param("ss", $qrFile, $eventID);
    $stmt->execute();
    $stmt->close();

    header("Location: admin_event.php?qr_generated=1");
    exit;
}

// Update Event
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_event'])) {
    $stmt = $conn->prepare("UPDATE event SET Title=?, Description=?, Date=?, Time=?, Location=?, Status=? WHERE EventID=?");
    $stmt->bind_param("sssssss", $_POST['title'], $_POST['description'], $_POST['date'], $_POST['time'], $_POST['location'], $_POST['status'], $_POST['event_id']);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_event.php?updated=1");
    exit;
}

// Delete Event
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_event'])) {
    $stmt = $conn->prepare("DELETE FROM event WHERE EventID = ?");
    $stmt->bind_param("s", $_POST['event_id']);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_event.php?deleted=1");
    exit;
}
?>

<div class="p-4 bg-white shadow rounded-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold">Event Management</h3>
    </div>

    <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-info">Event updated!</div>
    <?php elseif (isset($_GET['deleted'])): ?>
        <div class="alert alert-danger">Event deleted!</div>
    <?php elseif (isset($_GET['qr_generated'])): ?>
        <div class="alert alert-success">QR code generated!</div>
    <?php endif; ?>

    <!-- Filter Tabs -->
    <ul class="nav nav-tabs mb-3" id="eventFilterTabs">
        <li class="nav-item"><a class="nav-link active" href="#" data-filter="all">All Events</a></li>
        <li class="nav-item"><a class="nav-link" href="#" data-filter="Completed">Completed</a></li>
        <li class="nav-item"><a class="nav-link" href="#" data-filter="Upcoming">Upcoming</a></li>
        <li class="nav-item"><a class="nav-link" href="#" data-filter="Cancelled">Cancelled</a></li>
        <li class="nav-item"><a class="nav-link" href="#" data-filter="search">Search</a></li>
    </ul>

    <!-- Advanced Filters -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <select class="form-select" id="levelFilter">
                <option value="">All Levels</option>
                <?php
                $levels = ['International', 'National', 'State', 'District', 'UMPSA'];
                foreach ($levels as $level) {
                    echo "<option value=\"$level\">$level</option>";
                }
                ?>
            </select>
        </div>
        <div class="col-md-6">
            <select class="form-select" id="creatorFilter">
                <option value="">All Creators</option>
                <?php
                $users = $conn->query("SELECT DISTINCT u.UserID, u.Name FROM event e JOIN user u ON e.UserID = u.UserID WHERE u.Role = 'EA'");
                while ($u = $users->fetch_assoc()) {
                    echo "<option value=\"{$u['Name']}\">{$u['Name']}</option>";
                }
                ?>
            </select>
        </div>
    </div>

    <div id="searchBar" class="mb-3" style="display: none;">
        <input type="text" id="searchInput" class="form-control" placeholder="Search by title...">
    </div>

    <?php
    $result = $conn->query("SELECT e.*, u.Name as CreatorName FROM event e JOIN user u ON e.UserID = u.UserID ORDER BY Date DESC");
    while ($row = $result->fetch_assoc()):
        $qrPath = '../uploads/qrcodes/' . $row['QRCode'];
        $qrExists = file_exists($qrPath);
        $isCompleted = $row['Status'] === 'Completed';
    ?>

    <div class="card shadow-sm mb-3 event-card" data-status="<?= $row['Status'] ?>" data-title="<?= strtolower($row['Title']) ?>" data-level="<?= $row['Level'] ?>" data-creator="<?= htmlspecialchars($row['CreatorName']) ?>">
        <div class="card-body d-flex justify-content-between">
            <div>
                <span class="badge bg-<?= $row['Status'] === 'Completed' ? 'success' : ($row['Status'] === 'Upcoming' ? 'primary' : 'danger') ?>"><?= $row['Status'] ?></span>
                <h5 class="mt-2"><?= htmlspecialchars($row['Title']) ?></h5>
                <p><i class="bi bi-calendar-event"></i> <?= $row['Date'] ?> | <?= $row['Time'] ?></p>
                <p><i class="bi bi-geo-alt-fill"></i> <?= htmlspecialchars($row['Location']) ?></p>
                <p><i class="bi bi-person-circle"></i> Created by: <?= $row['CreatorName'] ?> | Level: <?= $row['Level'] ?></p>
                <div class="btn-group mt-2">
                    <a href="view_event.php?id=<?= $row['EventID'] ?>" target="_blank" class="btn btn-sm btn-outline-primary">View</a>
                    <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['EventID'] ?>" <?= $isCompleted ? 'disabled' : '' ?>>Edit</button>
                    <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $row['EventID'] ?>" <?= $isCompleted ? 'disabled' : '' ?>>Delete</button>
                </div>
            </div>
            <div class="text-center d-flex flex-column align-items-center justify-content-between">
                <?php if ($qrExists): ?>
                    <a href="view_event.php?id=<?= $row['EventID'] ?>" target="_blank">
                        <img src="../uploads/qrcodes/<?= $row['QRCode'] ?>" width="80" alt="QR Code">
                    </a>
                    <p class="small text-muted mt-1">Click or scan</p>
                <?php else: ?>
                    <form method="post">
                        <input type="hidden" name="generate_qr_id" value="<?= $row['EventID'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline-success mb-2">Generate QR</button>
                    </form>
                <?php endif; ?>

                <!-- Committee Button and Modal -->
                <button class="btn btn-sm btn-outline-info mt-2" data-bs-toggle="modal" data-bs-target="#committeeModal<?= $row['EventID'] ?>">Committee Members</button>
            </div>
        </div>
    </div>

    <!-- Committee Modal -->
    <div class="modal fade" id="committeeModal<?= $row['EventID'] ?>" tabindex="-1" aria-labelledby="committeeModalLabel<?= $row['EventID'] ?>" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header bg-info text-white">
            <h5 class="modal-title" id="committeeModalLabel<?= $row['EventID'] ?>">Committee Members – <?= htmlspecialchars($row['Title']) ?></h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <table class="table table-bordered">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Role</th>
                  <th>Student ID</th>
                  <th>Student Name</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $eventID = $row['EventID'];
                $committeeSQL = $conn->query("SELECT c.UserID, u.Name, r.Description AS RoleName FROM committee c JOIN user u ON c.UserID = u.UserID JOIN committee_role r ON c.C_RoleID = r.C_RoleID WHERE c.EventID = '$eventID' ORDER BY r.C_RoleID ASC");
                $num = 1;
                while ($cm = $committeeSQL->fetch_assoc()):
                ?>
                  <tr>
                    <td><?= $num++ ?></td>
                    <td><?= $cm['RoleName'] ?></td>
                    <td><?= $cm['UserID'] ?></td>
                    <td><?= htmlspecialchars($cm['Name']) ?></td>
                  </tr>
                <?php endwhile; ?>
                <?php if ($num === 1): ?>
                  <tr><td colspan="4" class="text-center">No committee members assigned.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit and Delete modals go here if needed -->
    <?php endwhile; ?>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const tabs = document.querySelectorAll('#eventFilterTabs .nav-link');
    const events = document.querySelectorAll('.event-card');
    const searchBar = document.getElementById('searchBar');
    const searchInput = document.getElementById('searchInput');
    const levelFilter = document.getElementById('levelFilter');
    const creatorFilter = document.getElementById('creatorFilter');

    function filterCards() {
        const level = levelFilter.value.toLowerCase();
        const creator = creatorFilter.value.toLowerCase();
        const keyword = searchInput.value.toLowerCase();

        events.forEach(event => {
            const status = event.dataset.status;
            const title = event.dataset.title;
            const cardLevel = event.dataset.level.toLowerCase();
            const cardCreator = event.dataset.creator.toLowerCase();

            const matchesLevel = !level || cardLevel === level;
            const matchesCreator = !creator || cardCreator.includes(creator);
            const matchesSearch = !keyword || title.includes(keyword);

            event.style.display = matchesLevel && matchesCreator && matchesSearch ? 'block' : 'none';
        });
    }

    tabs.forEach(tab => {
        tab.addEventListener('click', function (e) {
            e.preventDefault();
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            const filter = this.dataset.filter;

            if (filter === 'search') {
                searchBar.style.display = 'block';
                searchInput.value = '';
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

    searchInput.addEventListener('keyup', filterCards);
    levelFilter.addEventListener('change', filterCards);
    creatorFilter.addEventListener('change', filterCards);
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
