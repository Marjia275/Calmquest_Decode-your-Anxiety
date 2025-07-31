<?php
session_start();
include "db.php";
if (!isset($_SESSION['user_id'])) header("Location: main.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $total = array_sum($_POST);
    $suggest = ($total < 20) ? "You're doing well! Just maintain balance." :
               (($total <= 35) ? "You're a bit anxious. Try some meditation and light yoga." :
               "You're highly anxious. Please take time for deep yoga, meditation, and rest.");

    $day = date('z');
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO stress_logs (user_id, day, stress_level, suggestion) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $user_id, $day, $total, $suggest);
    $stmt->execute();

    header("Location: suggestion.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Anxiety Quiz</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .box {
            max-width: 600px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 0 15px rgba(215, 11, 11, 0.1);
            font-family: Arial, sans-serif;
            color: black;
            text-align: center;
        }
        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
            text-align: left;
        }
        input[type=number] {
            width: 100%;
            padding: 8px;
            margin-bottom: 20px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 16px;
        }
        .btn, .exit-btn {
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
            background-color: #a0cae9ff; /* soft blue */
        }
        .exit-btn {
            background-color: #f8bbd0; /* soft pink */
        }
    </style>
</head>
<body>

<form class="box" method="POST">
    <h2>Anxiety Level Assessment</h2>
    <p>Rate each question from 1 (Never) to 5 (Always)</p>

    <?php
    $questions = [
        "I have trouble sleeping or staying asleep.",
        "I feel overwhelmed by responsibilities.",
        "I get irritated or angry easily.",
        "I feel tired even after sleeping.",
        "I have trouble focusing or concentrating.",
        "I feel anxious or worried most of the day.",
        "I feel like I can't control important things in my life.",
        "I avoid social situations or people.",
        "I feel physically tense (shoulders, jaw, back).",
        "I feel unmotivated or helpless."
    ];

    foreach ($questions as $index => $q) {
        $qNum = $index + 1;
        echo "<label for='q$qNum'>Q$qNum: $q</label>";
        echo "<input type='number' id='q$qNum' name='$qNum' min='1' max='5' required>";
    }
    ?>

    <input type="submit" value="Submit Quiz" class="btn">
    <button type="button" class="exit-btn" onclick="history.back();">Back</button>
</form>

</body>
</html>