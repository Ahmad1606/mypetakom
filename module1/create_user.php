<?php
include '../layout/dashboard_layout.php';

// Fetch role list
$roleQuery = $conn->query("SELECT RoleID, Description FROM Role");
?>

<div class="card p-4">
  <h3 class="mb-3">Add New User</h3>
  <?php if (isset($_SESSION['message'])): ?>
  <div class="alert alert-<?= $_SESSION['msg_type'] ?>">
    <?= htmlspecialchars($_SESSION['message']) ?>
  </div>
  <?php unset($_SESSION['message'], $_SESSION['msg_type']); ?>
<?php endif; ?>


  <form method="POST" action="create_user_process.php">
    
    <div class="mb-3">
      <label for="userid" class="form-label">User ID</label>
      <input type="text" name="UserID" id="userid" class="form-control" required>
    </div>

    <div class="mb-3">
      <label for="name" class="form-label">Full Name</label>
      <input type="text" name="Name" id="name" class="form-control" required>
    </div>

    <div class="mb-3">
      <label for="password" class="form-label">Password</label>
      <input type="password" name="Password" id="password" class="form-control" required>
    </div>

    <div class="mb-3">
      <label for="email" class="form-label">Email</label>
      <input type="email" name="Email" id="email" class="form-control" required>
    </div>

    <div class="mb-3">
      <label for="phone" class="form-label">Phone Number</label>
      <input type="text" name="PhoneNumber" id="phone" class="form-control" required>
    </div>

    <div class="mb-5">
      <label for="role" class="form-label">Role</label>
      <select name="Role" id="role" class="form-select" required>
        <option value="">-- Select Role --</option>
        <?php while ($role = $roleQuery->fetch_assoc()): ?>
          <option value="<?= $role['RoleID'] ?>"><?= $role['Description'] ?></option>
        <?php endwhile; ?>
      </select>
    </div>

    <button type="submit" class="btn btn-success">Create User</button>
    <a href="admin_profile.php" class="btn btn-secondary">Cancel</a>
  </form>
</div>
