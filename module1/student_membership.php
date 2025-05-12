<?php
include 'dashboard_layout.php';

$UserID = $_SESSION['UserID'];
$Status = "";

// Get status if already applied
$stmt = $conn->prepare("SELECT Status FROM Membership WHERE UserID = ?");
$stmt->bind_param("s", $UserID);
$stmt->execute();
$stmt->bind_result($Status);
$stmt->fetch();
$stmt->close();
?>

<h2>Register Membership</h2>

<div class="card">
    <h3>Student Card Upload</h3>
    <?php if ($Status): ?>
        <p>Your application status is: <strong><?= htmlspecialchars($Status) ?></strong></p>
    <?php else: ?>
        <form method="POST" action="apply_membership.php" enctype="multipart/form-data">
            <p>Please upload your student card:</p>
            <input type="file" name="student_card" required accept=".jpg,.jpeg,.png,.pdf">
            <button type="submit" name="apply">Submit</button>
        </form>
    <?php endif; ?>
</div>
