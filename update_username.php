<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    header("Location: login_register.php");
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

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $newUsername = trim($_POST['username'] ?? '');

    if (empty($newUsername)) {
        $message = "Username cannot be empty.";
    } elseif (strlen($newUsername) < 3) {
        $message = "Username must be at least 3 characters.";
    } else {
        $userId = $_SESSION['user_id'];

        // Optional: check if username already exists in DB
        $check = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $check->bind_param("si", $newUsername, $userId);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = "Username already taken, please choose another.";
        } else {
            $update = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
            $update->bind_param("si", $newUsername, $userId);
            if ($update->execute()) {
                $_SESSION['username'] = $newUsername;  // update session immediately
                $message = "Username successfully updated.";
            } else {
                $message = "Error updating username.";
            }
            $update->close();
        }
        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Update Username | CalmQuest</title>
    <style>
        body {
            background: linear-gradient(to bottom right, #b2e4ecff, #efd6deff);
            font-family: Arial, sans-serif;
            text-align: center;
            color: black;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 500px;
            margin: 80px auto;
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        h2 {
            margin-bottom: 25px;
            color: #333;
        }
        input[type="text"] {
            width: 90%;
            padding: 12px;
            margin: 10px 0 20px;
            border: 1px solid #ccc;
            border-radius: 12px;
            font-size: 16px;
            box-sizing: border-box;
        }
        .btn {
            display: inline-block;
            width: 90%;
            padding: 15px;
            font-size: 18px;
            background-color: #d0eaff;
            color: black;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            text-decoration: none;
            margin-top: 10px;
            transition: background-color 0.3s ease;
        }
        .btn:hover {
            background-color: #b3ddff;
        }
        .back-link {
            display: inline-block;
            margin-top: 25px;
            padding: 12px 25px;
            background-color: #f8bbd0;
            border-radius: 12px;
            color: black;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }
        .back-link:hover {
            background-color: #f48fb1;
        }
        .message {
            margin-top: 20px;
            font-weight: bold;
            color: <?php echo strpos($message, 'successfully') !== false ? 'green' : 'red'; ?>;
            min-height: 24px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Update Your Username</h2>
        <form method="POST" action="">
            <input
                type="text"
                name="username"
                placeholder="Enter new username"
                required
                value="<?= htmlspecialchars($_SESSION['username']); ?>"
            />
            <button type="submit" class="btn">Update Username</button>
        </form>
        <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    </div>
</body>
</html>
