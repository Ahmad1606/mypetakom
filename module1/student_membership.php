<?php
session_start();
include '../layout/dashboard_layout.php';
include '../db/connect.php';

$UserID = isset($_SESSION['UserID']) ? $_SESSION['UserID'] : null;
$Status = "";

// Check if already applied
if ($UserID) {
    $sql = "SELECT Status FROM Membership WHERE UserID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $UserID);
    $stmt->execute();
    $stmt->bind_result($Status);
    $stmt->fetch();
    $stmt->close();
}

// allow student if he is still not applied or get rejected
$canReapply = !$Status || strtolower($Status) === 'rejected';
?>

<div class="card shadow-sm rounded-4 p-4">
  <h3 class="mb-4">Register Membership</h3>

  <?php if ($Status): ?>
    <p>Your application status is: <strong><?= htmlspecialchars($Status) ?></strong></p>
  <?php endif; ?>

  <?php if ($canReapply): ?>
    <form method="POST" action="apply_membership.php" enctype="multipart/form-data">
      <div class="mb-3">
        <label for="student_card" class="form-label">Upload Student Card</label>
        <input type="file" class="form-control" id="student_card" name="student_card" required accept=".jpg,.jpeg,.png,.pdf" onchange="previewFile(this)">
        <div class="form-text">Accepted file types: JPG, PNG.</div>
      </div>

      <div id="preview" class="mb-3"></div>

      <button type="submit" name="apply" class="btn btn-primary">Submit</button>
    </form>
  <?php endif; ?>
</div>

<?php if (isset($_SESSION['message'])): ?>
  <div class="alert alert-<?= $_SESSION['msg_type'] ?> alert-dismissible fade show mt-3" role="alert">
    <?= htmlspecialchars($_SESSION['message']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  <?php unset($_SESSION['message'], $_SESSION['msg_type']); ?>
<?php endif; ?>

<script>
  function previewFile(input) {
    const preview = document.getElementById('preview');
    preview.innerHTML = '';
    const file = input.files[0];
    if (!file) return;

    const ext = file.name.split('.').pop().toLowerCase();
      const img = document.createElement('img');
      img.src = URL.createObjectURL(file);
      img.style.maxWidth = '100%';
      img.style.maxHeight = '300px';
      img.onload = () => URL.revokeObjectURL(img.src);
      preview.appendChild(img);
    }
  
</script>
