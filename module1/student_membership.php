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
    <input type="file" name="student_card" id="student_card" required accept=".jpg,.jpeg,.png,.pdf" onchange="previewFile(this)">
    
    <div id="preview" style="margin-top: 10px;"></div>
    
    <button type="submit" name="apply">Submit</button>
</form>

<script>
function previewFile(input) {
    const preview = document.getElementById('preview');
    preview.innerHTML = ''; // clear old preview

    const file = input.files[0];
    if (!file) return;

    const ext = file.name.split('.').pop().toLowerCase();

    if (['jpg', 'jpeg', 'png'].includes(ext)) {
        const img = document.createElement('img');
        img.src = URL.createObjectURL(file);
        img.style.maxWidth = '100%';
        img.style.maxHeight = '300px';
        img.onload = () => URL.revokeObjectURL(img.src); // free memory
        preview.appendChild(img);
    } else if (ext === 'pdf') {
        const link = document.createElement('a');
        link.href = URL.createObjectURL(file);
        link.target = '_blank';
        link.textContent = 'Preview PDF';
        preview.appendChild(link);
    } else {
        preview.textContent = 'Preview not available for this file type.';
    }
}
</script>

    <?php endif; ?>
</div>
