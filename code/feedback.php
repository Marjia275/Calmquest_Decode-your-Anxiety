<?php
session_start();
include "db.php";
if (!isset($_SESSION['user_id'])) header("Location: main.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $feedback = $_POST['feedback'];
    $user_id = $_SESSION['user_id'];
    $conn->query("UPDATE stress_logs SET feedback='$feedback' WHERE user_id=$user_id ORDER BY id DESC LIMIT 1");
    header("Location: dashboard.php");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Feedback | CalmQuest</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<form class="box" method="POST">
    <h2><?php echo $_SESSION['name']; ?>, how do you feel?</h2>
    <textarea name="feedback" required placeholder="e.g. relaxed, sleepy, happy"></textarea><br>
    <input type="submit" value="Submit Feedback" class="btn">
    <a href="exit.php" class="exit-btn">Exit</a>
</form>
</body>
</html>