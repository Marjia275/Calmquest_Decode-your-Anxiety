<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// DB connection
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
    $name = trim($_POST['name'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $action = $_POST['action'] ?? '';

    if (empty($name) || empty($password)) {
        $message = "Name and Password are required!";
    } elseif (strlen($password) < 5 || !preg_match('/[a-zA-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $message = "Password must be at least 5 characters and contain both letters and numbers.";
    } else {
        if ($action === "register") {
            $passwordKey = hash('sha256', $password); // Deterministic hash

            // Check if same name + password combination exists
            $checkCombo = $conn->prepare("SELECT id FROM users WHERE name = ? AND password_key = ?");
            $checkCombo->bind_param("ss", $name, $passwordKey);
            $checkCombo->execute();
            $resultCombo = $checkCombo->get_result();

            if ($resultCombo && $resultCombo->num_rows > 0) {
                $message = "This name and password combination is already in use. Try a different name or password.";
            } else {
               // Use signup name as username (check uniqueness)
$generatedUsername = $name;
$checkUsername = $conn->prepare("SELECT id FROM users WHERE username = ?");
$checkUsername->bind_param("s", $generatedUsername);
$checkUsername->execute();
$usernameExists = $checkUsername->get_result()->num_rows > 0;
$checkUsername->close();

if ($usernameExists) {
    $message = "This username is already taken. Please use a different name.";
}

                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $conn->prepare("INSERT INTO users (name, username, password, password_key) VALUES (?, ?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param("ssss", $name, $generatedUsername, $hashedPassword, $passwordKey);
                    if ($stmt->execute()) {
                        $_SESSION['name'] = $name;
                        $_SESSION['user_id'] = $stmt->insert_id;
                        $_SESSION['username'] = $generatedUsername;
                        header("Location: dashboard.php");
                        exit();
                    } else {
                        $message = "Registration failed: " . $stmt->error;
                    }
                    $stmt->close();
                } else {
                    $message = "Prepare failed: " . $conn->error;
                }
            }
            $checkCombo->close();
        } elseif ($action === "login") {
            // Login logic - check all users with this name
            $stmt = $conn->prepare("SELECT id, password, username FROM users WHERE name = ?");
            $stmt->bind_param("s", $name);
            $stmt->execute();
            $result = $stmt->get_result();

            $loginSuccess = false;
            $userData = null;

            if ($result && $result->num_rows > 0) {
                // Check password against ALL users with this name
                while ($row = $result->fetch_assoc()) {
                    if (password_verify($password, $row['password'])) {
                        $loginSuccess = true;
                        $userData = $row;
                        break; // Found matching user, stop checking
                    }
                }

                if ($loginSuccess) {
                    $_SESSION['name'] = $name;
                    $_SESSION['user_id'] = $userData['id'];
                    $_SESSION['username'] = $userData['username'];
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $message = "Incorrect password.";
                }
            } else {
                $message = "User not found. Please register.";
            }
            $stmt->close();
        } else {
            $message = "Invalid action.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>CalmQuest | Login or Register</title>
    <style>
        body {
            background: linear-gradient(to right, #c8eff1ff, #dcc1d5ff);
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
        }
        .box {
            width: 700px;
            margin: 100px auto;
            padding: 24px;
            border-radius: 20px;
            background-color: #F2F2F6;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        input, select {
            width: 90%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 14px;
        }
        button {
            padding: 10px 16px;
            margin: 5px;
            background-color: #4da6ff;
            border: none;
            color: white;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
        }
        button:hover {
            background-color: #3399ff;
        }
        .message {
            color: red;
            font-size: 14px;
            min-height: 20px;
        }
    </style>
</head>
<body>
<div class="box">
    <h2>CalmQuest</h2>
    <h3>Decode Your Anxiety</h3>
    <p>Login or Register</p>

    <form method="POST" action="">
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="password" name="password" placeholder="Password (min 5 characters with letters and numbers)" required>
        <div>
            <button type="submit" name="action" value="login">Login</button>
            <button type="submit" name="action" value="register">Sign Up</button>
        </div>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
    </form>
</div>
</body>
</html>