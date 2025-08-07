<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: main.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

$task_names = ['Gardening', 'Swimming', 'Walking', 'Stair Climbing', 'Jogging'];

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['task_name'], $_POST['task_time'], $_POST['confirm_complete'])
    && $_POST['confirm_complete'] === "1"
) {
    $task_name = $_POST['task_name'];
    $task_time = (int)$_POST['task_time'];

    if (!in_array($task_name, $task_names)) {
        $message = "❌ Invalid task selected.";
    } elseif ($task_time <= 0) {
        $message = "⏳ Please enter a valid time.";
    } else {
        $stmt = $conn->prepare("INSERT INTO user_daily_tasks (user_id, task_name, duration) VALUES (?, ?, ?)");
        if (!$stmt) {
            die("❌ Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("isi", $user_id, $task_name, $task_time);
        if (!$stmt->execute()) {
            die("❌ Execute failed: " . $stmt->error);
        }
        $stmt->close();

        
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Task Challenge | CalmQuest</title>
    <style>
        body {
            background-color:  #d2e5eeff;
            font-family: Arial, sans-serif;
            text-align: center;
            color: black;
        }
        .box {
            max-width: 600px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        select, input[type=number], .btn {
            width: 100%;
            max-width: 300px;
            padding: 10px;
            margin: 15px auto;
            border-radius: 8px;
            border: 1px solid #ccc;
            background: #d0eaff;
            font-size: 16px;
        }
        .btn {
            background-color: #4caf50;
            color: white;
            font-weight: bold;
            border: none;
            cursor: pointer;
        }
        .message {
            color: green;
            font-weight: bold;
            margin: 15px 0;
        }
        #timer {
            font-size: 48px;
            margin: 20px 0;
            color: #007acc;
            font-weight: bold;
        }
        .back-btn {
            display: inline-block;
            margin-top: 20px;
            background-color: #007acc;
            text-decoration: none;
            color: white;
            font-weight: bold;
            padding: 10px 20px;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }
        .back-btn:hover {
            background-color: #005a99;
        }
    </style>
</head>
<body>
<div class="box">
    <h2>Daily Task Challenge</h2>

    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST" id="taskForm">
        <label>Select Task:</label><br />
        <select name="task_name" id="task_name" required>
            <option value="" disabled selected>Select a task</option>
            <?php foreach ($task_names as $task): ?>
                <option value="<?= htmlspecialchars($task) ?>"><?= htmlspecialchars($task) ?></option>
            <?php endforeach; ?>
        </select><br />

        <label>Time (minutes):</label><br />
        <input type="number" name="task_time" id="task_time" min="1" max="180" required /><br />

        <div id="timer" style="display:none;">00:00</div>
        <input type="hidden" name="confirm_complete" id="confirm_complete" value="0" />

        <button type="button" class="btn" id="startBtn">Start Timer</button>
        <button type="submit" class="btn" id="submitBtn" disabled>Submit</button>
    </form>

    <a href="dashboard.php" class="back-btn">← Back</a>
</div>

<script>
    const startBtn = document.getElementById('startBtn');
    const submitBtn = document.getElementById('submitBtn');
    const timerDisplay = document.getElementById('timer');
    const confirmCompleteInput = document.getElementById('confirm_complete');
    const taskTimeInput = document.getElementById('task_time');
    const taskSelect = document.getElementById('task_name');

    let timerInterval;

    startBtn.addEventListener('click', () => {
        const minutes = parseInt(taskTimeInput.value, 10);
        const taskSelected = taskSelect.value;
        if (!taskSelected || isNaN(minutes) || minutes <= 0) {
            alert("Please select a valid task and time.");
            return;
        }

        let timeLeft = minutes * 60;
        taskTimeInput.readOnly = true;   // readOnly still submits value
        taskSelect.readOnly = true;      // custom attribute, does not block submit
        startBtn.disabled = true;
        submitBtn.disabled = true;
        confirmCompleteInput.value = 0;

        timerDisplay.style.display = "block";
        updateTimerDisplay(timeLeft);

        if (timerInterval) clearInterval(timerInterval);

        timerInterval = setInterval(() => {
            timeLeft--;
            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                timerDisplay.textContent = "⏰ Time's up! You can submit.";
                submitBtn.disabled = false;
                confirmCompleteInput.value = 1;
                return;
            }
            updateTimerDisplay(timeLeft);
        }, 1000);
    });

    function updateTimerDisplay(seconds) {
        const m = Math.floor(seconds / 60);
        const s = seconds % 60;
        timerDisplay.textContent = `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
    }
</script>
</body>
</html>
