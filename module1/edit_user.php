<?php
session_start();
include '../layout/dashboard_layout.php';
include '../db/connect.php';

$UserID = $_GET['id'] ?? null;


// Fetch current data
$stmt = $conn->prepare("SELECT Name, Email, PhoneNumber FROM User WHERE UserID = ?");
$stmt->bind_param("s", $UserID);
$stmt->execute();
$stmt->bind_result($Name, $Email, $PhoneNumber);
$stmt->fetch();
$stmt->close();
?>

<div class="card p-4">
  <h3 class="mb-3">Edit User: <?= htmlspecialchars($Name) ?></h3>

  <?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-<?= $_SESSION['msg_type'] ?> mb-3">
      <?= htmlspecialchars($_SESSION['message']) ?>
    </div>
    <?php unset($_SESSION['message'], $_SESSION['msg_type']); ?>
  <?php endif; ?>

  <form action="edit_profile_process.php?id=<?= urlencode($UserID) ?>" method="POST">
    <div class="mb-3">
      <label for="email" class="form-label">Email</label>
      <input type="email" name="Email" id="email" value="<?= htmlspecialchars($Email) ?>" class="form-control" required>
    </div>

    <div class="mb-3">
      <label for="password" class="form-label">New Password (Not required)</label>
      <input type="password" name="Password" id="password" class="form-control">
    </div>

    <div class="mb-3">
      <label for="phone" class="form-label">Phone Number</label>
      <input type="text" name="PhoneNumber" id="phone" value="<?= htmlspecialchars($PhoneNumber) ?>" class="form-control" required>
    </div>

    <button type="submit" class="btn btn-primary">Update</button>
    <a href="admin_profile.php" class="btn btn-secondary">Cancel</a>
  </form>
</div>
