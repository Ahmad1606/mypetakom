<?php
session_start();
include '../layout/dashboard_layout.php';  // Adjust the path if needed
?>

<!-- Page-specific content here -->
<div class="p-4 bg-white shadow rounded-3">
  <h3>Welcome to the Event Management</h3>
  <p>This is your dashboard content, displayed based on role: <?= htmlspecialchars($Role) ?>.</p>
</div>

<!-- Close tags from dashboard_layout.php -->
      </div> <!-- .col-md-9 -->
    </div> <!-- .row -->
  </div> <!-- .container-fluid -->
</body>
</html>