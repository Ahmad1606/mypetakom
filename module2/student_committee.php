<?php
session_start();
include '../layout/dashboard_layout.php';
include '../db/connect.php';

if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== 'ST') {
    header("Location: ../module1/index.php");
    exit();
}

$userID = $_SESSION['UserID'];
$mainRoles = ['Leader', 'Secretary', 'Treasurer'];

$query = $conn->prepare("
    SELECT 
        e.EventID, e.Title, e.Date, e.Description AS EventDesc, e.Level, e.Status,
        cr.Description AS RoleName
    FROM committee c
    JOIN event e ON c.EventID = e.EventID
    JOIN committee_role cr ON c.C_RoleID = cr.C_RoleID
    WHERE c.UserID = ?
    ORDER BY e.Date DESC
");
$query->bind_param("s", $userID);
$query->execute();
$result = $query->get_result();

$positions = [];
$currentCount = $pastCount = $totalPoints = 0;

function calculateMerit($level, $role) {
    $main = ['Leader', 'Secretary', 'Treasurer'];
    $isMain = in_array($role, $main);
    return match ($level) {
        'International' => $isMain ? 100 : 70,
        'National'      => $isMain ? 80 : 50,
        'State'         => $isMain ? 60 : 40,
        'District'      => $isMain ? 40 : 30,
        'UMPSA'         => $isMain ? 30 : 20,
        default         => 10
    };
}

while ($row = $result->fetch_assoc()) {
    $row['MeritPoints'] = calculateMerit($row['Level'], $row['RoleName']);
    $positions[] = $row;
    $totalPoints += $row['MeritPoints'];
    if ($row['Status'] === 'Completed') $pastCount++;
    else $currentCount++;
}
?>

<div class="container py-4">
    <h2 class="fw-bold mb-4">My Committee Positions</h2>

    <div class="d-flex justify-content-end mb-3">
        <select class="form-select w-auto" id="filterSelect">
            <option value="All">All Events</option>
            <option value="Current">Current</option>
            <option value="Past">Past</option>
        </select>
    </div>

    <div class="row" id="positionCards">
        <?php foreach ($positions as $p): ?>
        <div class="col-md-4 mb-4 position-card" data-status="<?= $p['Status'] ?>">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($p['Title']) ?>
                        <span class="badge bg-<?= $p['Status'] === 'Completed' ? 'secondary' : 'success' ?> float-end">
                            <?= $p['Status'] === 'Completed' ? 'Past' : 'Current' ?>
                        </span>
                    </h5>
                    <p><strong>Position:</strong> <?= htmlspecialchars($p['RoleName']) ?></p>
                    <p><strong>Event Date:</strong> <?= date("F j, Y", strtotime($p['Date'])) ?></p>
                    <p><strong>Merit Points:</strong> <?= $p['MeritPoints'] ?> points<?= $p['Status'] === 'Completed' ? ' (Awarded)' : '' ?></p>
                    <p><?= nl2br(htmlspecialchars($p['EventDesc'])) ?></p>
                    <button class="btn btn-sm btn-outline-primary w-100 mt-2" data-bs-toggle="modal" data-bs-target="#committeeModal_<?= $p['EventID'] ?>">
                        View Committee Members
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="committeeModal_<?= $p['EventID'] ?>" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title"><?= htmlspecialchars($p['Title']) ?> - Committee Members</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <table class="table table-bordered table-sm">
                  <thead><tr><th>Name</th><th>Role</th></tr></thead>
                  <tbody>
                    <?php
                    $stmtModal = $conn->prepare("
                      SELECT u.Name, cr.Description
                      FROM committee c
                      JOIN user u ON c.UserID = u.UserID
                      JOIN committee_role cr ON c.C_RoleID = cr.C_RoleID
                      WHERE c.EventID = ?
                    ");
                    $stmtModal->bind_param("s", $p['EventID']);
                    $stmtModal->execute();
                    $members = $stmtModal->get_result();
                    while ($m = $members->fetch_assoc()):
                    ?>
                      <tr>
                        <td><?= htmlspecialchars($m['Name']) ?></td>
                        <td><?= htmlspecialchars($m['Description']) ?></td>
                      </tr>
                    <?php endwhile; $stmtModal->close(); ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="card mt-4 shadow-sm">
        <div class="card-body d-flex justify-content-around text-center">
            <div>
                <p class="fw-bold mb-1">Current Committees:</p>
                <span class="fs-5"><?= $currentCount ?></span>
            </div>
            <div>
                <p class="fw-bold mb-1">Past Committees:</p>
                <span class="fs-5"><?= $pastCount ?></span>
            </div>
            <div>
                <p class="fw-bold mb-1">Total Merit Points:</p>
                <span class="fs-5 text-primary fw-bold"><?= $totalPoints ?> points</span>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('filterSelect').addEventListener('change', function() {
    let value = this.value;
    document.querySelectorAll('.position-card').forEach(card => {
        let status = card.dataset.status;
        if (value === 'All' || 
            (value === 'Current' && status !== 'Completed') || 
            (value === 'Past' && status === 'Completed')) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
