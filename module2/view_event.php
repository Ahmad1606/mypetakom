<?php
include '../db/connect.php';

if (!isset($_GET['id'])) {
    die('Event ID is missing.');
}

$eventID = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM event WHERE EventID = ?");
$stmt->bind_param("s", $eventID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('Event not found.');
}

$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($row['Title']) ?> - Event Details</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: rgba(0, 0, 0, 0.05);
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      padding: 20px;
    }
    .modal-content {
      max-width: 700px;
      width: 100%;
      border-radius: 1rem;
    }
  </style>
</head>
<body>
  <div class="modal show d-block" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Event Details: <?= htmlspecialchars($row['Title']) ?></h5>
          <a href="javascript:history.back();" class="btn-close"></a>
        </div>
        <div class="modal-body">
          <p><strong>Description:</strong><br><?= nl2br(htmlspecialchars($row['Description'])) ?></p>
          <p><strong>Date:</strong> <?= $row['Date'] ?></p>
          <p><strong>Time:</strong> <?= $row['Time'] ?></p>
          <p><strong>Location:</strong> <?= htmlspecialchars($row['Location']) ?></p>
          <p><strong>Status:</strong> <span class="badge bg-<?= $row['Status'] === 'Completed' ? 'success' : ($row['Status'] === 'Upcoming' ? 'primary' : ($row['Status'] === 'Cancelled' ? 'danger' : 'secondary')) ?>"><?= $row['Status'] ?></span></p>
          <?php if (!empty($row['ApprovalLetter'])): ?>
            <p><strong>Approval Letter:</strong> <a href="../uploads/approvalLetters/<?= $row['ApprovalLetter'] ?>" target="_blank">View PDF</a></p>
          <?php endif; ?>
          <?php if (!empty($row['QRCode']) && file_exists("../uploads/qrcodes/" . $row['QRCode'])): ?>
            <p><strong>QR Code:</strong><br>
              <img src="../uploads/qrcodes/<?= $row['QRCode'] ?>" width="120" alt="QR Code">
            </p>
          <?php endif; ?>
        </div>
        <div class="modal-footer">
          <a href="javascript:history.back();" class="btn btn-secondary">Close</a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>