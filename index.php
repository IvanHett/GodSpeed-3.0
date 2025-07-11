<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['isLoggedIn']) || $_SESSION['isLoggedIn'] !== true) {
    header('Location: login.html');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inventory Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav>
        <a href="index.php" class="active">Dashboard</a>
        <a href="items.html">Items</a>
        <a href="logout.php" style="float: right;">Logout</a>
    </nav>
    <main>
        <h1>Inventory Dashboard</h1>
        <div id="dashboard-summary">
            <!-- Summary will be populated by JS -->
        </div>
    </main>
    <script src="auth.js"></script>
    <script src="app.js"></script>
</body>
</html> 