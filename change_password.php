<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
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

$message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $new = trim($_POST['new_password'] ?? '');
    $confirm = trim($_POST['confirm_password'] ?? '');

    if (empty($new) || empty($confirm)) {
        $message = "All fields are required.";
    } elseif ($new !== $confirm) {
        $message = "New passwords do not match.";
    } elseif (strlen($new) < 5 || !preg_match('/[a-zA-Z]/', $new) || !preg_match('/[0-9]/', $new)) {
        $message = "New password must be at least 5 characters and contain letters and numbers.";
    } else {
        $userId = $_SESSION['user_id'];

        // Get user's name
        $stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->bind_result($name);
        $stmt->fetch();
        $stmt->close();

        if (!$name) {
            $message = "User not found.";
        } else {
            // Generate new hashed password + key
            $newHashed = password_hash($new, PASSWORD_DEFAULT);
            $newKey = hash('sha256', $new);

            // Check if this name + password combination already exists
            $check = $conn->prepare("SELECT id FROM users WHERE name = ? AND password_key = ? AND id != ?");
            $check->bind_param("ssi", $name, $newKey, $userId);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $message = "This name and password combination is already in use. Please choose a different password.";
            } else {
                // Update password and password_key
                $update = $conn->prepare("UPDATE users SET password = ?, password_key = ? WHERE id = ?");
                $update->bind_param("ssi", $newHashed, $newKey, $userId);
                if ($update->execute()) {
                    $message = "Password successfully changed.";
                } else {
                    $message = "Error updating password.";
                }
                $update->close();
            }
            $check->close();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Change Password | CalmQuest</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to right, #e9f2f5ff, #70bdc8ff);
            margin: 0;
            padding: 0;
        }
        .box {
            width: 500px;
            margin: 100px auto;
            padding: 24px;
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
            text-align: center;
        }
        input {
            width: 90%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 8px;
            border: 1px solid #ccc;
        }
        button {
            padding: 10px 20px;
            background-color: #4da6ff;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            margin: 5px;
        }
        .message {
            color: red;
            margin-top: 10px;
            min-height: 20px;
        }
        .success {
            color: green;
        }
        .back-btn {
            background-color: #c6caccff;
            color: black;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 6px;
            display: inline-block;
            margin-top: 15px;
        }
        .back-btn:hover {
            background-color: #bbb;
        }
    </style>
</head>
<body>
<div class="box">
    <h2>Change Your Password</h2>
    <form method="POST" action="">
        <input type="password" name="new_password" placeholder="New Password" required><br>
        <input type="password" name="confirm_password" placeholder="Confirm New Password" required><br>
        <button type="submit">Update Password</button>
    </form>
    <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>
    <div class="message <?php echo (strpos($message, 'successfully') !== false) ? 'success' : ''; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
</div>
</body>
</html>