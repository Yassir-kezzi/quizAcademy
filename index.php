<?php 

require 'connexion.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];


 $stmt = $pdo->prepare("SELECT * FROM users WHERE userName = ? AND userPassword = ?");
    $stmt->execute([$username, $password]);
     $result = $stmt->fetch(PDO::FETCH_OBJ); 

if ($result) {
    $_SESSION['user'] = $result->userName;
    header("Location: quiz.php");
    exit();
} else {
?>
<p id="log-failed"> username or password  incorrect</p>
<?php
}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>login</title>
    <link rel="stylesheet" href="./style/login.css">
</head>
<body>
    <div class="login-form">

        <form class="form" action="index.php" method="post">
            <p class="form-title">Sign in to your account</p>
            <div class="input-container">
                <input type="text" name="username" placeholder="Enter username">
                <span>
                    </span>
                </div>
                <div class="input-container">
                    <input type="password" name="password" placeholder="Enter password">
                </div>
                <button type="submit" class="submit">
                    Sign in
                </button>
        </form>
    </div>
</body>
</html>