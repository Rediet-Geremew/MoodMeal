<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mood_meal";
$port = 3307;

try {
    $conn = new PDO("mysql:host=$servername;port=$port;dbname=$dbname", $username, $password);

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
 
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
