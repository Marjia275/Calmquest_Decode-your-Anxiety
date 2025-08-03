<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: main.php");
    exit();
}

// Set timezone
date_default_timezone_set('Asia/Dhaka');

$user_id = $_SESSION['user_id'];

// Handle Delete Request (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_date'])) {
    $delete_date = $_POST['delete_date'];

    // Validate date format (YYYY-MM-DD)
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $delete_date)) {
        // Delete from stress_logs
        $stmt = $conn->prepare("DELETE FROM stress_logs WHERE user_id=? AND date=?");
        $stmt->bind_param("is", $user_id, $delete_date);
        $stmt->execute();
        $stmt->close();

        // Delete from user_daily_tasks
        $stmt = $conn->prepare("DELETE FROM user_daily_tasks WHERE user_id=? AND date=?");
        $stmt->bind_param("is", $user_id, $delete_date);
        $stmt->execute();
        $stmt->close();

        // Delete from user_progress
        $stmt = $conn->prepare("DELETE FROM user_progress WHERE user_id=? AND log_date=?");
        $stmt->bind_param("is", $user_id, $delete_date);
        $stmt->execute();
        $stmt->close();

        // Redirect to avoid form resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $error_message = "Invalid date format for deletion.";
    }
}

// Handle Search Request (GET)
$search_date = $_GET['search_date'] ?? '';

// Initialize array to hold all entries grouped by date
$entriesByDate = [];

// Base queries have no date filter or filter by $search_date
$date_filter_sql = "";
if ($search_date && preg_match('/^\d{4}-\d{2}-\d{2}$/', $search_date)) {
    $date_filter_sql = " AND date = ?";
}

// -------- STRESS LOGS --------
$sql_stress = "SELECT date, stress_level FROM stress_logs WHERE user_id=?" . $date_filter_sql . " ORDER BY date DESC, id ASC";
$stmt_stress = $conn->prepare($sql_stress);
if ($date_filter_sql) {
    $stmt_stress->bind_param("is", $user_id, $search_date);
} else {
    $stmt_stress->bind_param("i", $user_id);
}
$stmt_stress->execute();
$result_stress = $stmt_stress->get_result();

while ($row = $result_stress->fetch_assoc()) {
    $date = $row['date'];
    if (!isset($entriesByDate[$date])) {
        $entriesByDate[$date] = ['stress' => [], 'meditation' => [], 'yoga' => [], 'tasks' => []];
    }
    if (is_numeric($row['stress_level']) && $row['stress_level'] > 0) {
        $entriesByDate[$date]['stress'][] = $row['stress_level'];
    }
}
$stmt_stress->close();

// -------- TASKS --------
$sql_tasks = "SELECT date, task_name, duration FROM user_daily_tasks WHERE user_id=?" . $date_filter_sql . " ORDER BY date DESC, id ASC";
$stmt_tasks = $conn->prepare($sql_tasks);
if ($date_filter_sql) {
    $stmt_tasks->bind_param("is", $user_id, $search_date);
} else {
    $stmt_tasks->bind_param("i", $user_id);
}
$stmt_tasks->execute();
$result_tasks = $stmt_tasks->get_result();

while ($row = $result_tasks->fetch_assoc()) {
    $date = $row['date'];
    if (!isset($entriesByDate[$date])) {
        $entriesByDate[$date] = ['stress' => [], 'meditation' => [], 'yoga' => [], 'tasks' => []];
    }
    $entriesByDate[$date]['tasks'][] = [
        'name' => $row['task_name'],
        'time' => (int)$row['duration']
    ];
}
$stmt_tasks->close();

// -------- MEDITATION + YOGA --------
$sql_progress = "SELECT log_date, type, duration FROM user_progress WHERE user_id=?" . ($date_filter_sql ? " AND log_date = ?" : "") . " ORDER BY log_date DESC, id ASC";
$stmt_progress = $conn->prepare($sql_progress);
if ($date_filter_sql) {
    $stmt_progress->bind_param("is", $user_id, $search_date);
} else {
    $stmt_progress->bind_param("i", $user_id);
}
$stmt_progress->execute();
$result_progress = $stmt_progress->get_result();

while ($row = $result_progress->fetch_assoc()) {
    $date = $row['log_date'];
    $type = $row['type'];
    $duration = (int)$row['duration'];

    if (!isset($entriesByDate[$date])) {
        $entriesByDate[$date] = ['stress' => [], 'meditation' => [], 'yoga' => [], 'tasks' => []];
    }

    if ($type === 'meditation' && $duration > 0) {
        $entriesByDate[$date]['meditation'][] = $duration;
    } elseif ($type === 'yoga' && $duration > 0) {
        $entriesByDate[$date]['yoga'][] = $duration;
    }
}
$stmt_progress->close();

// Sort entries by date descending
ksort($entriesByDate);
$entriesByDate = array_reverse($entriesByDate, true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Full History | CalmQuest</title>
    <style>
        /* Reset and base */
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to right, #c6ecf2ff, #e5cdd5ff);
            margin: 0;
            padding: 30px 15px;
            color: #333;
        }
        .box {
            max-width: 1000px;
            margin: 0 auto 50px;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
            padding: 40px 50px 50px;
        }
        h2 {
            text-align: center;
            font-weight: 700;
            font-size: 2.2rem;
            color: #004d80;
            margin-bottom: 40px;
        }

        /* Search form */
        form.search-form {
            margin-bottom: 30px;
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        form.search-form input[type="date"] {
            padding: 10px 15px;
            font-size: 1.1rem;
            border: 1.5px solid #007acc;
            border-radius: 8px;
            transition: border-color 0.3s ease;
            width: 180px;
        }
        form.search-form input[type="date"]:focus {
            outline: none;
            border-color: #005a99;
            box-shadow: 0 0 6px #005a99aa;
        }
        form.search-form button {
            padding: 10px 25px;
            font-size: 1.1rem;
            background-color: #007acc;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            box-shadow: 0 3px 6px rgba(0,122,204,0.4);
        }
        form.search-form button:hover {
            background-color: #005a99;
            box-shadow: 0 5px 12px rgba(0,90,153,0.6);
        }
        form.search-form a {
            align-self: center;
            color: #007acc;
            font-weight: 600;
            text-decoration: none;
            padding: 0 10px;
            font-size: 1rem;
            border-radius: 6px;
            transition: background-color 0.2s ease;
        }
        form.search-form a:hover {
            background-color: #e1f0ff;
        }

        /* Error message */
        p.error-message {
            color: #c0392b;
            font-weight: 600;
            text-align: center;
            margin-bottom: 25px;
            font-size: 1.1rem;
        }

        /* Date blocks */
        .date-block {
            border: 1px solid #ddd;
            border-radius: 12px;
            padding: 25px 30px 35px;
            margin-bottom: 2.8em;
            background: #fefefe;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            transition: box-shadow 0.25s ease;
            position: relative;
        }
        .date-block:hover {
            box-shadow: 0 10px 25px rgba(0,0,0,0.12);
        }

        /* Date title with delete button */
        .date-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #007acc;
            margin-bottom: 1.3em;
            cursor: pointer;
            user-select: none;
            position: relative;
            padding-right: 120px;
        }
        .date-title:hover::after {
            content: "📌 Data is here";
            position: absolute;
            top: 105%;
            left: 0;
            background: #007acc;
            color: white;
            padding: 5px 10px;
            font-size: 0.85rem;
            border-radius: 5px;
            white-space: nowrap;
            opacity: 0.95;
            pointer-events: none;
            font-weight: 600;
            letter-spacing: 0.02em;
        }

        /* Delete button */
        form.delete-form {
            position: absolute;
            right: 30px;
            top: 20px;
            margin: 0;
        }
        .delete-btn {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 0.95rem;
            font-weight: 600;
            box-shadow: 0 3px 7px rgba(231, 76, 60, 0.7);
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }
        .delete-btn:hover {
            background-color: #c0392b;
            box-shadow: 0 5px 14px rgba(192, 57, 43, 0.8);
        }

        /* Split content */
        .split-box {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
        }
        .left, .right {
            flex: 1 1 45%;
            padding: 15px 25px;
            background: #f9f9f9;
            border-radius: 15px;
            border: 1px solid #ddd;
            box-shadow: inset 0 2px 5px #e0e0e0;
        }

        /* Log sections */
        .log-section {
            margin-bottom: 1.6em;
        }
        .log-section strong {
            display: block;
            margin-bottom: 0.8em;
            color: #555;
            font-size: 1.05rem;
            border-bottom: 2px solid #007acc;
            padding-bottom: 5px;
            font-weight: 700;
            letter-spacing: 0.03em;
        }
        .log-section ul {
            padding-left: 25px;
            margin: 0;
        }
        .log-section li {
            margin-bottom: 7px;
            font-size: 1rem;
            color: #444;
        }
        .log-section small {
            color: #777;
            font-style: italic;
            display: block;
            margin-top: 6px;
            font-weight: 600;
        }

        .empty-state {
            font-style: italic;
            color: #999;
            font-size: 1rem;
            margin-top: 8px;
        }

        /* Back button */
        a.btn {
            display: inline-block;
            margin-top: 25px;
            padding: 14px 30px;
            background-color: #007acc;
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1.1rem;
            box-shadow: 0 4px 15px rgba(0,122,204,0.6);
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }
        a.btn:hover {
            background-color: #005a99;
            box-shadow: 0 6px 22px rgba(0,90,153,0.8);
        }
    </style>
</head>
<body>
<div class="box">
    <h2><?= htmlspecialchars($_SESSION['username']) ?>'s Activity Log</h2>

    <!-- Search form -->
    <form method="GET" class="search-form" action="">
        <input type="date" name="search_date" value="<?= htmlspecialchars($search_date) ?>" />
        <button type="submit">Search</button>
        <?php if ($search_date): ?>
            <a href="<?= $_SERVER['PHP_SELF'] ?>">Clear</a>
        <?php endif; ?>
    </form>

    <?php if (!empty($error_message)): ?>
        <p class="error-message"><?= htmlspecialchars($error_message) ?></p>
    <?php endif; ?>

    <?php if (!empty($entriesByDate)): ?>
        <?php foreach ($entriesByDate as $date => $logs): ?>
            <div class="date-block">
                <div class="date-title">
                    📅 <?= htmlspecialchars($date) ?>
                    <form method="POST" class="delete-form" onsubmit="return confirm('Delete all data for <?= htmlspecialchars($date) ?>? This action cannot be undone.');">
                        <input type="hidden" name="delete_date" value="<?= htmlspecialchars($date) ?>" />
                        <button type="submit" class="delete-btn" title="Delete all data for this date">Delete</button>
                    </form>
                </div>

                <div class="split-box">
                    <div class="left">
                        <div class="log-section">
                            <strong>🔥 Stress Quizzes:</strong>
                            <?php if (!empty($logs['stress'])): ?>
                                <ul>
                                    <?php foreach ($logs['stress'] as $level): ?>
                                        <li>Level <?= htmlspecialchars($level) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p class="empty-state">No stress quizzes recorded.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="right">
                        <div class="log-section">
                            <strong>🧘 Meditation Sessions:</strong>
                            <?php if (!empty($logs['meditation'])): ?>
                                <ul>
                                    <?php foreach ($logs['meditation'] as $time): ?>
                                        <li><?= htmlspecialchars($time) ?> minutes</li>
                                    <?php endforeach; ?>
                                </ul>
                                <small>Total: <?= array_sum($logs['meditation']) ?> minutes</small>
                            <?php else: ?>
                                <p class="empty-state">No meditation logged.</p>
                            <?php endif; ?>
                        </div>

                        <div class="log-section">
                            <strong>🧘‍♂️ Yoga Sessions:</strong>
                            <?php if (!empty($logs['yoga'])): ?>
                                <ul>
                                    <?php foreach ($logs['yoga'] as $time): ?>
                                        <li><?= htmlspecialchars($time) ?> minutes</li>
                                    <?php endforeach; ?>
                                </ul>
                                <small>Total: <?= array_sum($logs['yoga']) ?> minutes</small>
                            <?php else: ?>
                                <p class="empty-state">No yoga logged.</p>
                            <?php endif; ?>
                        </div>

                        <div class="log-section">
                            <strong>📋 Daily Tasks:</strong>
                            <?php if (!empty($logs['tasks'])): ?>
                                <ul>
                                    <?php 
                                    $totalTaskTime = 0;
                                    foreach ($logs['tasks'] as $task): 
                                        $totalTaskTime += $task['time'];
                                    ?>
                                        <li><?= htmlspecialchars($task['name']) ?> — <?= htmlspecialchars($task['time']) ?> min</li>
                                    <?php endforeach; ?>
                                </ul>
                                <small>Total: <?= $totalTaskTime ?> minutes</small>
                            <?php else: ?>
                                <p class="empty-state">No tasks completed.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty-state" style="text-align:center; margin-top: 50px;">
            <h3>📊 No data available</h3>
            <p>Start using CalmQuest to see your progress here!</p>
        </div>
    <?php endif; ?>

    <a href="dashboard.php" class="btn">⬅ Back to Dashboard</a>
</div>
</body>
</html>
