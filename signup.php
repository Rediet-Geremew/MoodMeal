<?php
session_start();
require '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    try {
        $stmt->execute([$username, $email, $password]);
        $_SESSION['user_id'] = $conn->lastInsertId();
        $_SESSION['username'] = $username;
        header("Location: ../login.html");
        exit();
    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) {
            echo "Email already registered.";
        } else {
            echo "Error: " . $e->getMessage();
        }
    }
}
?>
