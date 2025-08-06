<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: main.php");
    exit();
}

$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['meditation_time'], $_POST['yoga_time'])) {
    $user_id = $_SESSION['user_id'];
    $meditation_time = (int)$_POST['meditation_time'];
    $yoga_time = (int)$_POST['yoga_time'];

    if ($meditation_time > 0 && $yoga_time > 0) {
        // Set timezone to ensure consistent date handling
        date_default_timezone_set('Asia/Dhaka'); // Adjust to your timezone
        $today = date('Y-m-d');

        // Insert meditation
        $stmt = $conn->prepare("INSERT INTO user_progress (user_id, log_date, type, duration) VALUES (?, ?, 'meditation', ?)");
        $stmt->bind_param("isi", $user_id, $today, $meditation_time);
        $stmt->execute();
        $stmt->close();

        // Insert yoga
        $stmt = $conn->prepare("INSERT INTO user_progress (user_id, log_date, type, duration) VALUES (?, ?, 'yoga', ?)");
        $stmt->bind_param("isi", $user_id, $today, $yoga_time);
        $stmt->execute();
        $stmt->close();

     
    } else {
        $message = "❌ Invalid meditation or yoga time.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Breathing & Yoga Task | CalmQuest</title>
    <link rel="stylesheet" href="style.css" />
    <style>
        body {
            background-color: #102129ff;
            font-family: Arial, sans-serif;
            color: black;
            text-align: center;
            margin: 0;
            padding: 0;
        }

        .box {
            max-width: 700px;
            margin: 40px auto;
            background: #f7f6f7ff;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        select, .btn, .exit-btn {
            padding: 10px 15px;
            font-size: 16px;
            margin-top: 15px;
            border-radius: 8px;
            border: 1px solid #ccc;
            background-color: #91C8E4;
            color: black;
            cursor: pointer;
            display: inline-block;
        }

        select {
            background-color: #dae7edff;
            color: black;
            appearance: none;
        }

        .exit-btn {
            background-color: #f3e0e7ff;
            margin-left: 10px;
            border: none;
        }

        input[type=checkbox] {
            transform: scale(1.5);
            margin: 3px;
            cursor: default;
        }

        ul {
            text-align: left;
            padding-left: 40px;
        }

        #meditation_timer, #yoga_timer {
            font-size: 36px;
            font-weight: bold;
            margin: 15px 0;
            color: #267fbbff;
        }

        .message {
            font-weight: bold;
            margin: 15px 0;
            color: green;
        }

        .debug-info {
            background: #e8f4fd;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            font-size: 14px;
            color: #333;
        }
    </style>
</head>
<body>

<div class="box">
    <h2>🧘‍♀️ Hello, <?= htmlspecialchars($_SESSION['username']) ?>! Let's Breathe & Stretch</h2>
    <h2>"Complete both parts to finish the task."</h2>

    <!-- Debug info to show current date -->
    

    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form id="breathingForm" method="POST">
        <!-- Meditation Section -->
        <div style="margin-bottom: 30px;">
            <h3>🫁 Meditation</h3>
            <label for="breathing_time">Select slow breathing time (minutes):</label><br />
            <select name="meditation_time" id="breathing_time" required>
                <option value="" disabled selected>Select time</option>
                <?php for ($i = 1; $i <= 10; $i++): ?>
                    <option value="<?= $i ?>"><?= $i ?> minute<?= $i > 1 ? "s" : "" ?></option>
                <?php endfor; ?>
            </select>
            <br />
            <button type="button" class="btn" id="startMeditationBtn">Start Meditation</button>
            <div id="meditation_timer" style="display:none;">00:00</div>
            <label><input type="checkbox" id="meditation_check" value="1" disabled> Meditation Done</label>
        </div>

        <!-- Yoga Section -->
        <div>
            <h3>🧘‍♂️ Yoga Poses</h3>
            <ul>
                <li>Child's Pose (Balasana)</li>
                <li>Cat-Cow Stretch</li>
                <li>Legs-Up-The-Wall (Viparita Karani)</li>
                <li>Seated Forward Bend</li>
                <li>Corpse Pose (Savasana)</li>
            </ul>
            <label for="yoga_time">Select yoga time (minutes):</label><br />
            <select name="yoga_time" id="yoga_time" required>
                <option value="" disabled selected>Select time</option>
                <?php for ($i = 1; $i <= 10; $i++): ?>
                    <option value="<?= $i ?>"><?= $i ?> minute<?= $i > 1 ? "s" : "" ?></option>
                <?php endfor; ?>
            </select>
            <br />
            <button type="button" class="btn" id="startYogaBtn">Start Yoga</button>
            <div id="yoga_timer" style="display:none;">00:00</div>
            <label><input type="checkbox" id="yoga_check" value="1" disabled> Yoga Done</label>
        </div>

        <br />
        <button type="submit" class="btn" id="submitBtn" disabled>Done</button>
        <a href="dashboard.php" class="exit-btn">← Back</a>
    </form>
</div>

<script>
    const breathSelect = document.getElementById('breathing_time');
    const yogaSelect = document.getElementById('yoga_time');
    const breathTimerDisplay = document.getElementById('meditation_timer');
    const yogaTimerDisplay = document.getElementById('yoga_timer');
    const meditationCheck = document.getElementById('meditation_check');
    const yogaCheck = document.getElementById('yoga_check');
    const submitBtn = document.getElementById('submitBtn');

    document.getElementById('startMeditationBtn').addEventListener('click', () => {
        clearInterval(window.meditationInterval);
        const minutes = parseInt(breathSelect.value, 10);
        if (!minutes) {
            alert("Please select meditation time first.");
            return;
        }
        let timeLeft = minutes * 60;
        meditationCheck.checked = false;
        meditationCheck.disabled = true;
        breathTimerDisplay.style.display = 'block';
        updateTimer(breathTimerDisplay, timeLeft);

        window.meditationInterval = setInterval(() => {
            timeLeft--;
            if (timeLeft <= 0) {
                clearInterval(window.meditationInterval);
                breathTimerDisplay.textContent = "⏰ Done!";
                meditationCheck.disabled = false;
                meditationCheck.checked = true;
                checkReadyToSubmit();
            } else {
                updateTimer(breathTimerDisplay, timeLeft);
            }
        }, 1000);
    });

    document.getElementById('startYogaBtn').addEventListener('click', () => {
        clearInterval(window.yogaInterval);
        const minutes = parseInt(yogaSelect.value, 10);
        if (!minutes) {
            alert("Please select yoga time first.");
            return;
        }
        let timeLeft = minutes * 60;
        yogaCheck.checked = false;
        yogaCheck.disabled = true;
        yogaTimerDisplay.style.display = 'block';
        updateTimer(yogaTimerDisplay, timeLeft);

        window.yogaInterval = setInterval(() => {
            timeLeft--;
            if (timeLeft <= 0) {
                clearInterval(window.yogaInterval);
                yogaTimerDisplay.textContent = "⏰ Done!";
                yogaCheck.disabled = false;
                yogaCheck.checked = true;
                checkReadyToSubmit();
            } else {
                updateTimer(yogaTimerDisplay, timeLeft);
            }
        }, 1000);
    });

    function updateTimer(displayElement, seconds) {
        const m = Math.floor(seconds / 60);
        const s = seconds % 60;
        displayElement.textContent = `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
    }

    function checkReadyToSubmit() {
        submitBtn.disabled = !(meditationCheck.checked && yogaCheck.checked);
    }
</script>

</body>
</html>