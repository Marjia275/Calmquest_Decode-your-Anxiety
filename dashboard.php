<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    header("Location: main.php");
    exit();
}

// Connect to DB
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "calmquest_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch the latest username from DB
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($usernameFromDB);
$stmt->fetch();
$stmt->close();

// Fallback if somehow still empty
if (empty($usernameFromDB)) {
    $usernameFromDB = "User";
}

// Update session for consistency
$_SESSION['username'] = $usernameFromDB;
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Dashboard | CalmQuest</title>
    <link rel="stylesheet" href="style.css" />
    <style>
        body {
            background: linear-gradient(to bottom right, #b2e4ecff, #efd6deff);
            font-family: Arial, sans-serif;
            text-align: center;
            color: black;
            margin: 0;
        }
        .container {
            max-width: 700px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        h2 {
            margin-bottom: 20px;
        }
        h3 {
            margin-top: 30px;
            margin-bottom: 10px;
            color: #444;
        }
        .btn {
            display: block;
            width: 80%;
            margin: 10px auto;
            padding: 15px;
            font-size: 18px;
            background-color: #d0eaff;
            color: black;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            text-decoration: none;
        }
        .btn:hover {
            background-color: #b3ddff;
        }
        .logout, .exit, .password-change {
            display: inline-block;
            margin-top: 30px;
            margin-right: 10px;
            padding: 10px 20px;
            background-color: #f8bbd0;
            border-radius: 10px;
            text-decoration: none;
            color: black;
        }
        .message {
            color: green;
            margin-top: 15px;
            font-weight: bold;
        }
        /* Inline edit button link next to username */
        .edit-username-btn {
            width: auto !important;
            padding: 8px 15px !important;
            font-size: 14px !important;
            margin-left: 10px !important;
            display: inline-block !important;
            vertical-align: middle !important;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>
        Welcome, <span id="currentName"><?= htmlspecialchars($_SESSION['username']) ?></span> 👋
        <a href="update_username.php" class="btn edit-username-btn">✏️ Edit Username</a>
    </h2>

    <?php
    if (!empty($_SESSION['message'])) {
        echo '<p class="message">' . htmlspecialchars($_SESSION['message']) . '</p>';
        unset($_SESSION['message']);
    }
    ?>

    <h3>📊 Check Your Anxiety Level</h3>
    <a href="stress_quiz.php" class="btn">Take Quiz</a>

    <h3>🧘 Relaxation Practice</h3>
    <a href="task_manager.php" class="btn">Start Meditation/Yoga</a>

    <h3>🏆 Daily Task Challenge</h3>
    <a href="tasks_challenge.php" class="btn">Start Challenge</a>

    <h3>📅 Weekly Summary</h3>
    <a href="summary.php" class="btn">View Progress</a>

    <div>
        <a href="change_password.php" class="password-change">Change Password</a>
        <a href="exit.php" class="exit">Logout</a>
    </div>
</div>

</body>
</html>
