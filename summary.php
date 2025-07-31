<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: main.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch logs for the last 7 days
$sql = "SELECT * FROM stress_logs WHERE user_id=? AND date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) ORDER BY date DESC, id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$logs = $stmt->get_result();

if (!$logs) {
    die("Database query failed: " . $conn->error);
}

$totalStress = 0;
$entryCount = 0;
$entries = [];

while ($row = $logs->fetch_assoc()) {
    $entries[] = $row;
    $entryCount++;
    if (is_numeric($row['stress_level'])) $totalStress += (int)$row['stress_level'];
}

// Weekly feedback logic
$feedback = "";
$showEntries = true;  // Show entries only if less than 7 days

if ($entryCount === 7) {
    $showEntries = false;  // Hide entries after 7 days logged
    $avgStress = $totalStress / 7;

    if ($avgStress <= 10) {
        $feedback .= "🌟 Excellent! You had a calm and productive week.";
    } elseif ($avgStress <= 20) {
        $feedback .= "👍 Good job! Anxiety levels were okay overall.";
    } elseif ($avgStress <= 30) {
        $feedback .= "⚠️Your anxiety levels are moderate. Consider incorporating regular meditation and physical exercise into your routine to help manage your anxiety more effectively.";
    } else {
        $feedback .= "🚨 High anxiety levels detected. Consider taking breaks and reducing pressure.";
    }
} elseif ($entryCount > 0) {
    $feedback .= "📝 You have logged some entries. Keep tracking for a full 7-day insight.";
} else {
    $feedback .= "📬 No entries logged yet. Start using CalmQuest daily to track your well-being.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>7-Day Summary | CalmQuest</title>
    <link rel="stylesheet" href="style.css" />
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #c6ecf2ff, #e5cdd5ff);
            margin: 0;
            padding: 20px;
        }
        .box {
            max-width: 600px;
            margin: auto;
            padding: 2em;
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin-top: 1em;
            border: none;
            border-radius: 10px;
            text-decoration: none;
            font-weight: bold;
            background-color: #cceeff;
            color: #000;
        }
        .btn.delete-btn {
            background-color: #ffcccc;
            color: #900;
            margin-left: 1em;
            cursor: pointer;
        }
        .log-entry {
            margin-bottom: 1em;
            padding: 0.5em;
            border-bottom: 1px solid #ccc;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .log-text {
            flex-grow: 1;
        }
        .feedback {
            margin-top: 1.5em;
            padding: 1em;
            background: #f0f8ff;
            border-radius: 10px;
        }
    </style>
    <script>
        function confirmDelete(id) {
            if (confirm("Are you sure you want to delete this entry?")) {
                window.location.href = "delete_log.php?id=" + encodeURIComponent(id);
            }
        }
    </script>
</head>
<body>
<div class="box">
    <h2><?= htmlspecialchars($_SESSION['username']) ?>'s 7-Day Summary</h2>

    <?php if ($showEntries && $entryCount > 0): ?>
        <?php foreach ($entries as $row): ?>
            <div class="log-entry">
                <div class="log-text">
                    <strong>Date:</strong> <?= htmlspecialchars($row['date']) ?><br />
                    <strong>Anxiety Level:</strong> <?= htmlspecialchars($row['stress_level']) ?>
                </div>
                <button onclick="confirmDelete(<?= (int)$row['id'] ?>)" class="btn delete-btn">Delete</button>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="feedback">
        <h3>📊 Weekly Feedback:</h3>
        <p><?= nl2br(htmlspecialchars($feedback)) ?></p>
    </div>

    <a href="dashboard.php" class="btn">Back</a>
</div>
</body>
</html>