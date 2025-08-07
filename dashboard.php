<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    header("Location: main.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "calmquest_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($usernameFromDB);
$stmt->fetch();
$stmt->close();

if (empty($usernameFromDB)) {
    $usernameFromDB = "User";
}

$_SESSION['username'] = $usernameFromDB;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard | CalmQuest</title>
  <style>
/* Base styles for all devices (as before) */
body {
    background: linear-gradient(to bottom right, #b2e4ecff, #efd6deff);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #333;
    margin: 0;
    padding: 20px;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    text-align: center;
    box-sizing: border-box;
    overflow-x: hidden;
}

.container {
    background: white;
    padding: 20px 15px;
    border-radius: 20px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 350px;
    box-sizing: border-box;
    word-wrap: break-word;
}

h2 {
    margin-bottom: 20px;
    font-size: 1.3rem;
    line-height: 1.2;
}

h3 {
    margin: 20px 0 10px;
    color: #444;
    font-size: 1rem;
}

.btn {
    display: block;
    width: 100%;
    margin: 10px 0;
    padding: 10px 0;
    font-size: 0.9rem;
    background-color: #d0eaff;
    color: #000;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    text-decoration: none;
    transition: background-color 0.2s ease;
    box-sizing: border-box;
}

.btn:hover {
    background-color: #b3ddff;
}

.logout, .exit, .password-change {
    display: inline-block;
    margin: 20px 5px 0;
    padding: 8px 14px;
    background-color: #f8bbd0;
    border-radius: 10px;
    text-decoration: none;
    color: black;
    font-size: 0.85rem;
}

.message {
    color: green;
    margin-top: 15px;
    font-weight: bold;
    word-break: break-word;
}

/* UPDATED edit username button style ONLY */
.edit-username-btn {
    display: inline-block !important;
    vertical-align: middle !important;
    padding: 6px 12px !important;
    font-size: 0.85rem !important;
    margin-left: 12px !important;
    background-color: #a0cae9ff;
    color: black !important;
    border-radius: 12px;
    text-decoration: none !important;
    font-weight: 600;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    cursor: pointer;
    transition: background-color 0.25s ease;
}
.edit-username-btn:hover {
    background-color: #88b7d9ff;
}

/* Small devices (phones) */
@media (max-width: 400px) {
    body {
        padding: 10px;
    }
    .container {
        max-width: 100%;
        padding: 15px 12px;
        border-radius: 15px;
    }
    h2 {
        font-size: 1.2rem;
    }
    h3 {
        font-size: 0.95rem;
        margin: 18px 0 8px;
    }
    .btn {
        font-size: 0.85rem;
        padding: 8px 0;
    }
    .logout, .exit, .password-change {
        font-size: 0.8rem;
        padding: 6px 10px;
        margin: 15px 4px 0;
    }
    .edit-username-btn {
        font-size: 0.75rem !important;
        padding: 4px 7px !important;
        margin-left: 6px !important;
    }
}

/* Tablet devices (portrait and small landscape) */
@media (min-width: 401px) and (max-width: 767px) {
    body {
        padding: 20px 15px;
    }
    .container {
        max-width: 420px;
        padding: 25px 20px;
        border-radius: 18px;
    }
    h2 {
        font-size: 1.5rem;
    }
    h3 {
        font-size: 1.1rem;
        margin: 22px 0 10px;
    }
    .btn {
        font-size: 1rem;
        padding: 12px 0;
    }
    .logout, .exit, .password-change {
        font-size: 0.9rem;
        padding: 8px 16px;
        margin: 20px 6px 0;
    }
    .edit-username-btn {
        font-size: 0.85rem !important;
        padding: 6px 10px !important;
        margin-left: 8px !important;
    }
}

/* Larger tablets and small desktops */
@media (min-width: 768px) and (max-width: 1024px) {
    body {
        padding: 25px 20px;
    }
    .container {
        max-width: 480px;
        padding: 30px 25px;
        border-radius: 20px;
    }
    h2 {
        font-size: 1.7rem;
    }
    h3 {
        font-size: 1.25rem;
        margin: 25px 0 12px;
    }
    .btn {
        font-size: 1.1rem;
        padding: 14px 0;
    }
    .logout, .exit, .password-change {
        font-size: 1rem;
        padding: 10px 18px;
        margin: 25px 8px 0;
    }
    .edit-username-btn {
        font-size: 0.9rem !important;
        padding: 7px 12px !important;
        margin-left: 10px !important;
    }
}

</style>


</head>
<body>

<div class="container">
  <h2>
    Welcome, <span id="currentName"><?= htmlspecialchars($_SESSION['username']) ?></span> 👋
    <a href="update_username.php" class="edit-username-btn">✏️ Edit Username</a>
  </h2>

  <?php if (!empty($_SESSION['message'])): ?>
    <p class="message"><?= htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></p>
  <?php endif; ?>

  <h3>📊 Check Your Anxiety Level</h3>
  <a href="stress_quiz.php" class="btn">Take Quiz</a>

  <h3>🧘 Relaxation Practice</h3>
  <a href="task_manager.php" class="btn">Start Meditation/Yoga</a>

  <h3>🏆 Daily Task Challenge</h3>
  <a href="tasks_challenge.php" class="btn">Start Challenge</a>

  <h3>📅 Activity log</h3>
  <a href="summary.php" class="btn">View Progress</a>

  <div>
    <a href="change_password.php" class="password-change">Change Password</a>
    <a href="exit.php" class="exit">Logout</a>
  </div>
</div>

</body>
</html>
