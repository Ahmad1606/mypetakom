<?php
session_start();
include '../layout/dashboard_layout.php';
include '../db/connect.php';

if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== 'EA') {
    header("Location: ../module1/index.php");
    exit();
}

$advisorID = $_SESSION['UserID'];

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_id'], $_POST['committee'])) {
    $eventID = $_POST['event_id'];
    $committees = $_POST['committee'];
    $assignedUsers = [];

    // Get event date
    $dateStmt = $conn->prepare("SELECT Date FROM event WHERE EventID = ?");
    $dateStmt->bind_param("s", $eventID);
    $dateStmt->execute();
    $dateStmt->bind_result($eventDate);
    $dateStmt->fetch();
    $dateStmt->close();

    foreach ($committees as $roleID => $value) {
        $userIDs = is_array($value) ? $value : [$value];

        foreach ($userIDs as $userID) {
            if (empty($userID)) continue;

            if (in_array($userID, $assignedUsers)) {
                header("Location: manage_committeeV2.php?msg=role_duplicate");
                exit();
            }
            $assignedUsers[] = $userID;

            $conflictStmt = $conn->prepare("
                SELECT c.CommitteeID FROM committee c
                JOIN event e ON c.EventID = e.EventID
                WHERE c.UserID = ? AND e.Date = ? AND c.EventID != ?
            ");
            $conflictStmt->bind_param("sss", $userID, $eventDate, $eventID);
            $conflictStmt->execute();
            $conflictResult = $conflictStmt->get_result();

            if ($conflictResult->num_rows > 0) {
                header("Location: manage_committeeV2.php?msg=date_conflict");
                exit();
            }

            $committeeID = 'C' . bin2hex(random_bytes(5));
            $stmt = $conn->prepare("INSERT INTO committee (CommitteeID, EventID, C_RoleID, UserID) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $committeeID, $eventID, $roleID, $userID);
            $stmt->execute();
            $stmt->close();
        }
    }

    header("Location: manage_committeeV2.php?msg=success");
    exit();
}

// Fetch data
$eventQuery = $conn->prepare("SELECT EventID, Title, Date FROM event WHERE UserID = ?");
$eventQuery->bind_param("s", $advisorID);
$eventQuery->execute();
$eventResult = $eventQuery->get_result();

$allEvents = [];
while ($row = $eventResult->fetch_assoc()) {
    $allEvents[] = $row;
}

$roleQuery = $conn->query("SELECT C_RoleID, Description FROM committee_role");
$roles = [];
while ($r = $roleQuery->fetch_assoc()) {
    $roles[$r['C_RoleID']] = $r['Description'];
}
$students = $conn->query("SELECT UserID, Name FROM user WHERE Role = 'ST'")->fetch_all(MYSQLI_ASSOC);
?>

<div class="p-4 bg-white shadow rounded-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold">Committees Management</h3>
        <div class="d-flex align-items-center gap-2">
            <input type="text" id="eventSearch" class="form-control form-control-sm" placeholder="Search event..." style="max-width: 250px;">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#registerCommitteeModal">Register Committees</button>
        </div>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <?php
            switch ($_GET['msg']) {
                case 'success': echo "Committee successfully registered."; break;
                case 'edit_success': echo "Committee updated successfully."; break;
                case 'edit_fail': echo "Failed to update committee."; break;
                case 'delete_success': echo "Committee deleted."; break;
                case 'delete_fail': echo "Failed to delete committee."; break;
                case 'role_duplicate': echo "A student cannot be assigned to more than one role in the same event."; break;
                case 'date_conflict': echo "A student is already assigned to another event on this date."; break;
            }
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?> 

    <?php foreach ($allEvents as $event): ?>
        <div class="card mb-4 event-card">
            <div class="card-header bg-primary text-white">
                <strong><?= htmlspecialchars($event['Title']) ?></strong> (<?= $event['Date'] ?>)
            </div>
            <div class="card-body p-3">
                <?php
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
                        <thead><tr><th>Student Name</th><th>Role</th></tr></thead>
                        <tbody>
                        <?php
                        $mainRoles = ['Leader', 'Secretary', 'Treasurer'];
                        $mainRows = [];
                        $extraRows = [];

                        while ($member = $committeeResult->fetch_assoc()) {
                            $row = "<tr><td>" . htmlspecialchars($member['StudentName']) . "</td><td>" . htmlspecialchars($member['RoleName']) . "</td></tr>";
                            if (in_array($member['RoleName'], $mainRoles)) {
                                $mainRows[] = $row;
                            } else {
                                $extraRows[] = $row;
                            }
                        }

                        // Output main rows
                        foreach ($mainRows as $row) echo $row;
                        ?>
                        </tbody>
                        </table>

                        <?php if (!empty($extraRows)): ?>
                        <div class="text-center">
                        <button class="btn btn-outline-secondary btn-sm mb-2" type="button" data-bs-toggle="collapse" data-bs-target="#extra_<?= $event['EventID'] ?>" aria-expanded="false" aria-controls="extra_<?= $event['EventID'] ?>">
                            More Members
                        </button>
                        </div>
                        <div class="collapse" id="extra_<?= $event['EventID'] ?>">
                        <table class="table table-bordered">
                            <thead><tr><th>Student Name</th><th>Role</th></tr></thead>
                            <tbody>
                            <?= implode("\n", $extraRows) ?>
                            </tbody>
                        </table>
                        </div>
                        <?php endif; ?> 
                        </tbody>
                    </table>
                    <div class="text-end mt-3">
                    <button class="btn btn-warning btn-sm me-2" data-bs-toggle="modal" data-bs-target="#editCommitteeModal_<?= $event['EventID'] ?>">Edit Committee</button>
                    <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteCommitteeModal_<?= $event['EventID'] ?>">Delete Committee</button>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No committee members registered yet.</p>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Register Modal -->
<div class="modal fade" id="registerCommitteeModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h5 class="modal-title">Register New Committee</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Event</label>
            <select name="event_id" class="form-select" required>
              <option value="" disabled selected>Select Event</option>
              <?php foreach ($allEvents as $e): ?>
                <option value="<?= $e['EventID'] ?>"><?= $e['Title'] ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <?php foreach ($roles as $roleID => $roleName): ?>
            <div class="mb-3">
              <label class="form-label"><?= $roleName ?> (<?= $roleID ?>)</label>

              <?php if ($roleID === 'EC006'): ?>
                <?php for ($i = 0; $i < 5; $i++): ?>
                  <select name="committee[<?= $roleID ?>][]" class="form-select mb-2">
                    <option value="" disabled selected>Select Member <?= $i + 1 ?></option>
                    <?php foreach ($students as $stu): ?>
                      <option value="<?= $stu['UserID'] ?>"><?= $stu['Name'] ?> (<?= $stu['UserID'] ?>)</option>
                    <?php endforeach; ?>
                  </select>
                <?php endfor; ?>
              <?php else: ?>
                <select name="committee[<?= $roleID ?>]" class="form-select" required>
                  <option value="" disabled selected>Select Student</option>
                  <?php foreach ($students as $stu): ?>
                    <option value="<?= $stu['UserID'] ?>"><?= $stu['Name'] ?> (<?= $stu['UserID'] ?>)</option>
                  <?php endforeach; ?>
                </select>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Register Committees</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php foreach ($allEvents as $event): ?>
<?php
$assignmentStmt = $conn->prepare("SELECT C_RoleID, UserID FROM committee WHERE EventID = ?");
$assignmentStmt->bind_param("s", $event['EventID']);
$assignmentStmt->execute();
$assignmentResult = $assignmentStmt->get_result();

$assignments = [];
while ($row = $assignmentResult->fetch_assoc()) {
    $roleID = $row['C_RoleID'];
    if (!isset($assignments[$roleID])) {
        $assignments[$roleID] = [];
    }
    $assignments[$roleID][] = $row['UserID'];
}
$assignmentStmt->close();
?>

<!-- Edit Modal -->
<div class="modal fade" id="editCommitteeModal_<?= $event['EventID'] ?>" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" action="edit_committee.php">
        <div class="modal-header">
          <h5 class="modal-title">Edit Committee - <?= htmlspecialchars($event['Title']) ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="event_id" value="<?= $event['EventID'] ?>">

          <?php foreach ($roles as $roleID => $roleName): ?>
            <div class="mb-3">
              <label class="form-label"><?= $roleName ?> (<?= $roleID ?>)</label>

              <?php if ($roleID === 'EC006'): ?>
                <?php for ($i = 0; $i < 5; $i++): ?>
                  <?php $selectedMember = $assignments[$roleID][$i] ?? ''; ?>
                  <select name="committee[<?= $roleID ?>][]" class="form-select mb-2">
                    <option value="">Do not change</option>
                    <?php foreach ($students as $stu): ?>
                      <option value="<?= $stu['UserID'] ?>" <?= $selectedMember === $stu['UserID'] ? 'selected' : '' ?>>
                        <?= $stu['Name'] ?> (<?= $stu['UserID'] ?>)
                      </option>
                    <?php endforeach; ?>
                  </select>
                <?php endfor; ?>
              <?php else: ?>
                <?php $selected = $assignments[$roleID][0] ?? ''; ?>
                <select name="committee[<?= $roleID ?>]" class="form-select">
                  <option value="">Do not change</option>
                  <?php foreach ($students as $stu): ?>
                    <option value="<?= $stu['UserID'] ?>" <?= $selected === $stu['UserID'] ? 'selected' : '' ?>>
                      <?= $stu['Name'] ?> (<?= $stu['UserID'] ?>)
                    </option>
                  <?php endforeach; ?>
                </select>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="modal-footer">
          <button class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>


<!-- Delete Modal -->
<div class="modal fade" id="deleteCommitteeModal_<?= $event['EventID'] ?>" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="delete_committee.php">
        <div class="modal-header">
          <h5 class="modal-title">Delete Committee - <?= htmlspecialchars($event['Title']) ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="event_id" value="<?= $event['EventID'] ?>">
          <p class="text-danger">Are you sure you want to delete all committee members for this event?</p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-danger">Yes, Delete</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endforeach; ?>


<script>
document.getElementById("eventSearch").addEventListener("keyup", function() {
  let filter = this.value.toLowerCase();
  let cards = document.querySelectorAll(".event-card");

  cards.forEach(card => {
    let title = card.querySelector(".card-header strong")?.textContent.toLowerCase() || "";
    card.style.display = title.includes(filter) ? "" : "none";
  });
});
</script>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
