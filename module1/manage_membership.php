<?php
session_start();
include '../layout/dashboard_layout.php';
include '../db/connect.php';

$sql = "SELECT m.MembershipID, u.UserID, u.Name, m.Status, m.StudentCard
        FROM Membership m
        JOIN User u ON m.UserID = u.UserID
        ORDER BY u.UserID ASC";

$result = $conn->query($sql);
?>

<?php if (isset($_SESSION['message'])): ?>
  <div class="alert alert-<?= $_SESSION['msg_type'] ?>">
    <?= htmlspecialchars($_SESSION['message']) ?>
  </div>
  <?php unset($_SESSION['message'], $_SESSION['msg_type']); ?>
<?php endif; ?>

<div class="card p-4">
  <h3>Manage Membership Applications</h3>

  <?php if ($result->num_rows > 0): ?>
    <table cellpadding="8" width="100%">
      <thead style="background-color:rgb(196, 203, 255);">
        <tr>
          <th>Student ID</th>
          <th>Name</th>
          <th>Status</th>
          <th>Card</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['UserID']) ?></td>
            <td><?= htmlspecialchars($row['Name']) ?></td>
            <td><?= htmlspecialchars($row['Status']) ?></td>
            <td>
              <?php if (!empty($row['StudentCard'])): ?>
                <a href="<?= htmlspecialchars($row['StudentCard']) ?>" target="_blank">View</a>
              <?php else: ?>
                <span class="text-muted">No file</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if (strtolower($row['Status']) === 'pending'): ?>
                <form method="POST" action="process_membership.php" style="display:inline;">
                  <input type="hidden" name="MembershipID" value="<?= $row['MembershipID'] ?>">
                  <button type="submit" name="action" value="approve" class="btn btn-outline-primary btn-sm">Approve</button>
                  <button type="submit" name="action" value="reject" class="btn btn-outline-danger btn-sm">Reject</button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p>No membership applications found.</p>
  <?php endif; ?>
</div>
