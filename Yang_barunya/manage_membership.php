<?php
include 'dashboard_layout.php';
include 'connect.php';

// Fetch membership applications with user info
$sql = "SELECT m.MembershipID, u.Name, m.StudentCard, m.Status
        FROM Membership m
        JOIN User u ON m.UserID = u.UserID";
$result = $conn->query($sql);
?>

<h2>Manage Membership Applications</h2>

<?php if (isset($_SESSION['message'])): ?>
    <div class="alert <?= $_SESSION['msg_type'] === 'success' ? 'alert-success' : 'alert-error' ?>">
        <?= htmlspecialchars($_SESSION['message']) ?>
    </div>
    <?php unset($_SESSION['message'], $_SESSION['msg_type']); ?>
<?php endif; ?>

<?php if ($result->num_rows > 0): ?>
    <table class="data-table">
        <thead>
            <tr>
                <th>Student Name</th>
                <th>Student Card</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['Name']) ?></td>
                <td><a href="uploads/<?= htmlspecialchars($row['StudentCard']) ?>" target="_blank">View</a></td>
                <td><?= htmlspecialchars($row['Status'] ?? 'Pending') ?></td>
                <td>
                    <form method="POST" action="process_membership.php" style="display:flex; gap:5px;">
                        <input type="hidden" name="MembershipID" value="<?= $row['MembershipID'] ?>">
                        <button name="action" value="approve">Approve</button>
                        <button name="action" value="reject" style="background-color:red;">Reject</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No membership applications found.</p>
<?php endif; ?>
