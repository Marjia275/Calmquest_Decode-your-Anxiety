<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: main.php");
    exit();
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
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 10px 15px;
            font-size: 16px;
            margin-top: 15px;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            cursor: pointer;
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
    </style>
</head>
<body>

<div class="box">
    <h2>🧘‍♀️ Hello, <?= htmlspecialchars($_SESSION['username']) ?>! Let’s Breathe & Stretch</h2>
    <h2>"Complete both parts to finish the task."</h2>

    <form id="breathingForm" onsubmit="resetAll(); return false;">
        <!-- Meditation Section -->
        <div style="margin-bottom: 30px;">
            <h3>🫁 Meditation</h3>
            <label for="breathing_time">Select slow breathing time (minutes):</label><br />
            <select name="breathing_time" id="breathing_time" required>
                <option value="" disabled selected>Select time</option>
                <?php for ($i = 1; $i <= 10; $i++): ?>
                    <option value="<?= $i ?>"><?= $i ?> minute<?= $i > 1 ? "s" : "" ?></option>
                <?php endfor; ?>
            </select>
            <br />
            <button type="button" class="btn" id="startMeditationBtn">Start Meditation</button>
            <div id="meditation_timer" style="display:none;">00:00</div>
            <label><input type="checkbox" id="meditation_check" name="meditation_check" value="1" disabled> Meditation Done</label>
        </div>

        <!-- Yoga Section -->
        <div>
            <h3>🧘‍♂️ Yoga Poses</h3>
            <ul>
                <li>Child’s Pose (Balasana)</li>
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
            <label><input type="checkbox" id="yoga_check" name="yoga_check" value="1" disabled>Yoga Done</label>
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

    function resetAll() {
        clearInterval(window.meditationInterval);
        clearInterval(window.yogaInterval);
        document.getElementById('breathingForm').reset();
        meditationCheck.checked = false;
        meditationCheck.disabled = true;
        yogaCheck.checked = false;
        yogaCheck.disabled = true;
        breathTimerDisplay.style.display = 'none';
        yogaTimerDisplay.style.display = 'none';
        submitBtn.disabled = true;
    }
</script>

</body>
</html>