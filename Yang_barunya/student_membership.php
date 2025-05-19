<?php
include 'dashboard_layout.php';
include 'connect.php';

$UserID = $_SESSION['UserID'];

// Get membership status if exists
$stmt = $conn->prepare("SELECT Status FROM Membership WHERE UserID = ?");
$stmt->bind_param("s", $UserID);
$stmt->execute();
$stmt->bind_result($Status);
$stmt->fetch();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Generate MembershipID like MS001
    function generateMembershipID($conn) {
        $res = $conn->query("SELECT MembershipID FROM Membership ORDER BY MembershipID DESC LIMIT 1");
        if ($res && $row = $res->fetch_assoc()) {
            $num = intval(substr($row['MembershipID'], 2));
            return 'MS' . str_pad($num + 1, 3, '0', STR_PAD_LEFT);
        }
        return 'MS001';
    }

    $MembershipID = generateMembershipID($conn);

    // Handle uploaded file
    $file = $_FILES['student_card'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $StudentCard = "card_{$UserID}.$ext";
    $targetFile = "uploads/$StudentCard";

    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        $stmt = $conn->prepare("INSERT INTO Membership (MembershipID, UserID, StudentCard, Status) VALUES (?, ?, ?, 'Pending')");
        $stmt->bind_param("sss", $MembershipID, $UserID, $StudentCard);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Membership application submitted successfully.";
            $_SESSION['msg_type'] = 'success';
        } else {
            $_SESSION['message'] = "Database error: " . $stmt->error;
            $_SESSION['msg_type'] = 'error';
        }
    } else {
        $_SESSION['message'] = "File upload failed.";
        $_SESSION['msg_type'] = 'error';
    }

    header("Location: student_membership.php");
    exit();
}
?>

<h2>Register Membership</h2>

<?php if (isset($_SESSION['message'])): ?>
    <div class="alert <?= $_SESSION['msg_type'] === 'success' ? 'alert-success' : 'alert-error' ?>">
        <?= htmlspecialchars($_SESSION['message']) ?>
    </div>
    <?php unset($_SESSION['message'], $_SESSION['msg_type']); ?>
<?php endif; ?>

<div class="card">
    <h3>Student Card Upload</h3>
    <?php if (!empty($Status)): ?>
        <p>Your application status is: <strong><?= htmlspecialchars($Status) ?></strong></p>
    <?php else: ?>
        <form method="POST" enctype="multipart/form-data">
            <p>Please upload your student card:</p>
            <input type="file" name="student_card" id="student_card" required accept=".jpg,.jpeg,.png,.pdf">
            <div id="preview" style="margin-top: 10px;"></div>
            <button type="submit" name="apply">Submit</button>
        </form>

        <script>
            function previewFile(input) {
                const preview = document.getElementById('preview');
                preview.innerHTML = '';
                const file = input.files[0];
                if (!file) return;

                const ext = file.name.split('.').pop().toLowerCase();
                if (['jpg', 'jpeg', 'png'].includes(ext)) {
                    const img = document.createElement('img');
                    img.src = URL.createObjectURL(file);
                    img.style.maxWidth = '100%';
                    img.style.maxHeight = '300px';
                    img.onload = () => URL.revokeObjectURL(img.src);
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

            document.getElementById('student_card').addEventListener('change', function () {
                previewFile(this);
            });
        </script>
    <?php endif; ?>
</div>
</body>
</html>
