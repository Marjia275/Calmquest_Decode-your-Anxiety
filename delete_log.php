<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: main.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$log_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($log_id > 0) {
    // Verify ownership
    $check = $conn->query("SELECT * FROM stress_logs WHERE id=$log_id AND user_id=$user_id");
    if ($check === false) {
        die("SQL Error: " . $conn->error);
    }
    if ($check->num_rows == 1) {
        $del = $conn->query("DELETE FROM stress_logs WHERE id=$log_id");
        if (!$del) {
            die("Delete failed: " . $conn->error);
        }
    } else {
        die("No such log or unauthorized deletion attempt.");
    }
} else {
    die("Invalid log ID.");
}

header("Location: summary.php");
exit();
?>