<?php
session_start();
include '../layout/dashboard_layout.php';
include '../db/connect.php';

$UserID = $_SESSION['UserID'] ?? null;
$Role = $_SESSION['Role'] ?? null;

if (!$UserID) {
    header("Location: ../module1/index.php");
    exit();
}


// Get current user info
$stmt = $conn->prepare("SELECT Email, PhoneNumber FROM User WHERE UserID = ?");
$stmt->bind_param("s", $UserID);
$stmt->execute();
$stmt->bind_result($Email, $PhoneNumber);
$stmt->fetch();
$stmt->close();
?>

<div class="card p-4">
  <h3 class="mb-3">Edit My Profile</h3>

  <?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-<?= $_SESSION['msg_type'] ?> mb-3">
      <?= htmlspecialchars($_SESSION['message']) ?>
    </div>
    <?php unset($_SESSION['message'], $_SESSION['msg_type']); ?>
  <?php endif; ?>

  <form action="edit_profile_process.php" method="POST">
    <div class="mb-3">
      <label for="email" class="form-label">Email</label>
      <input type="email" name="Email" id="email" class="form-control" value="<?= htmlspecialchars($Email) ?>" required>
    </div>

    <div class="mb-3">
      <label for="password" class="form-label">New Password (optional)</label>
      <input type="password" name="Password" id="password" class="form-control" placeholder="Leave blank to keep current password">
    </div>

    <div class="mb-3">
      <label for="phone" class="form-label">Phone Number</label>
      <input type="text" name="PhoneNumber" id="phone" class="form-control" value="<?= htmlspecialchars($PhoneNumber) ?>" required>
    </div>

    <button type="submit" class="btn btn-primary">Update</button>
  </form>
</div>
