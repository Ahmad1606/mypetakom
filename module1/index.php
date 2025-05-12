<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - MyPetakom</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
<div class="login-container">
    <h2>Login to MyPetakom</h2>

    <?php
    if (isset($_SESSION['LoginError'])) {
        echo "<div class='error'>{$_SESSION['LoginError']}</div>";
        unset($_SESSION['LoginError']);
    }
    ?>

    <form method="POST" action="login.php">
        <label>User ID:</label>
        <input type="text" name="UserID" required>

        <label>Password:</label>
        <input type="password" name="Password" required>

        <label>Role:</label>
        <div class="role-options">
            <label><input type="radio" name="Role" value="Student" required> Student</label>
            <label><input type="radio" name="Role" value="Event advisor"> Event Advisor</label>
            <label><input type="radio" name="Role" value="Petakom administrator"> Petakom Admin</label>
        </div>

        <button type="submit">Login</button>
    </form>
</div>
</body>
</html>
