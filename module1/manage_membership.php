<?php
include '../layout/dashboard_layout.php';

// Fetch membership applications ordered by Student ID
$sql = "SELECT m.MembershipID, u.UserID, u.Name, m.StudentCard, m.Status
        FROM Membership m
        JOIN User u ON m.UserID = u.UserID
        ORDER BY u.UserID ASC";
$result = $conn->query($sql);
?>

<div class="card shadow-sm rounded-4 p-4">
  <h3 class="mb-4">Manage Membership Applications</h3>

  <?php if ($result->num_rows > 0): ?>
    <div class="table-responsive">
      <table class="table table-bordered align-middle text-center">
        <thead class="table-light">
          <tr>
            <th>Student ID</th>
            <th>Student Name</th>
            <th>Student Card</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($row['UserID']) ?></td>
              <td><?= htmlspecialchars($row['Name']) ?></td>
              <td>
                <?php if (!empty($row['StudentCard'])): ?>
                  <a href="<?= htmlspecialchars($row['StudentCard']) ?>" target="_blank">View</a>
                <?php else: ?>
                  <span class="text-muted">No file</span>
                <?php endif; ?>
              </td>
              <td><?= htmlspecialchars($row['Status'] ?? 'Pending') ?></td>
              <td style="width: 140px; white-space: nowrap;">
                <?php if (strtolower($row['Status']) === 'pending'): ?>
                  <form method="POST" action="process_membership.php" class="d-flex justify-content-center gap-2 mb-0">
                    <input type="hidden" name="MembershipID" value="<?= $row['MembershipID'] ?>">
                    <button name="action" value="approve" class="btn btn-primary btn-sm px-2">Approve</button>
                    <button name="action" value="reject" class="btn btn-danger btn-sm px-2">Reject</button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div class="alert alert-info">No membership applications found.</div>
  <?php endif; ?>
</div>
