

<?php 

require 'connexion.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];


 $stmt = $pdo->prepare("SELECT userName,userPassword FROM users WHERE userName = ? AND userPassword = ?");
    $stmt->execute([$username, $password]);
     $result = $stmt->fetchAll(PDO::FETCH_OBJ); 

if (count($result) > 0 ){
    $_SESSION['user'] = $result['userName'];
    header("Location: quiz.php");
        exit(); 
}
else {
    echo "login failed";
};
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <div class="login">
        <form action="index.php" method="post">
            <input type="text" name="username">
            <input type="password" name="password">
            <input type="submit" value="login">
        </form>
    </div>
</body>
</html>