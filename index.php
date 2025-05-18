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
    $error="username or password  incorrect" ;
}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>login_page</title>
    <link rel="stylesheet" href="./style/login.css">
</head>
<body>
    <div class="login-form">

        <form class="form" action="index.php" method="post">
            <p class="form-title">Sign in to your account</p>
            
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="input-container">
                <input type="text" name="username" placeholder="Enter username">
                <i class="fas fa-user"></i>
            </div>

            <div class="input-container">
                <input type="password" name="password" placeholder="Enter password">
                <i class="fas fa-lock"></i>
            </div>

            <button type="submit" class="submit">
                    Sign in
            </button>
        </form>
    </div>
</body>
</html>