<?php
session_start();
include "db.php";
if (!isset($_SESSION['user_id'])) header("Location: main.php");

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $update = $conn->prepare("UPDATE users SET name=?, email=?, password=? WHERE id=?");
    $update->bind_param("sssi", $name, $email, $password, $user_id);
    if ($update->execute()) {
        $_SESSION['name'] = $name;
        $_SESSION['email'] = $email;
        $message = "Profile updated!";
    } else {
        $message = "Update failed!";
    }
}

$result = $conn->query("SELECT * FROM users WHERE id=$user_id");
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Profile | CalmQuest</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<form class="box" method="POST">
    <h2>Update Your Profile</h2>
    <input type="text" name="name" value="<?= $user['name'] ?>" required>
    <input type="email" name="email" value="<?= $user['email'] ?>" required>
    <input type="password" name="password" value="<?= $user['password'] ?>" required>
    <input type="submit" value="Update" class="btn">
    <a href="dashboard.php" class="btn">Back</a>
    <a href="exit.php" class="exit-btn">Exit</a>
    <?php if (isset($message)) echo "<p class='message'>$message</p>"; ?>
</form>
</body>
</html>