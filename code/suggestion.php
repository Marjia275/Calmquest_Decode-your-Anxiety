<?php
session_start();
include "db.php";

$user_id = $_SESSION['user_id'];
$result = $conn->query("SELECT * FROM stress_logs WHERE user_id=$user_id ORDER BY id DESC LIMIT 1");
$data = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Suggestion</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .box {
            max-width: 600px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            font-family: Arial, sans-serif;
            color: black;
            text-align: center;
        }
        .btn, .exit-btn, .dashboard-btn {
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            margin: 10px 5px 0 0;
            display: inline-block;
            text-decoration: none;
            color: black;
        }
        .btn {
            background-color: #b5d3ecff; /* soft blue */
        }
        .exit-btn {
            background-color: #f8bbd0; /* soft pink */
            border: none;
        }
       
        .dashboard-btn {
            background-color: #e3ebeeff; /* medium soft blue */
        }
        .dashboard-btn:hover {
            background-color: #e9ddddff;
            color:#007ecc;

        }
    </style>
</head>
<body>
<div class="box">
    <h2>Your Anxiety Level: <?= htmlspecialchars($data['stress_level']) ?></h2>
    <p><?= htmlspecialchars($data['suggestion']) ?></p>

    <a href="task_manager.php" class="btn">Go to Task</a>
    <a href="dashboard.php" class="dashboard-btn">Go to Dashboard</a>
    <button type="button" class="exit-btn" onclick="history.back();">Back</button>
</div>
</body>
</html>