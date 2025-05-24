<?php
session_start();
include '../layout/dashboard_layout.php';

// Fetch all users with role descriptions
$sql = "SELECT u.UserID, u.Name, r.Description AS RoleDescription
        FROM User u
        JOIN Role r ON u.Role = r.RoleID
        WHERE u.Role IN ('ST', 'EA')
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
  <h3>Manage User Profiles</h3>

  <div style="margin: 10px 0;">
    <a href="edit_profile.php" class="btn btn-success btn-sm">Edit Profile</a>
    <a href="create_user.php" class="btn btn-success btn-sm">Add New User</a>
  </div>

  <?php if ($result->num_rows > 0): ?>
    <table cellpadding="8" width="100%">
      <thead style="background-color:rgb(196, 203, 255);">
        <tr>
          <th>User ID</th>
          <th>Name</th>
          <th>Role</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['UserID']) ?></td>
            <td><?= htmlspecialchars($row['Name']) ?></td>
            <td><?= htmlspecialchars($row['RoleDescription']) ?></td>
            <td>
              <a href="edit_user.php?id=<?= $row['UserID'] ?>" class="btn btn-outline-primary btn-sm">Edit</a>
              <a href="delete_user.php?id=<?= $row['UserID'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure to delete this user?');">Delete</a>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p>No users found.</p>
  <?php endif; ?>
</div>
