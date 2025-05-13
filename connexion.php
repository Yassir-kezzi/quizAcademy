<?php
$host = 'localhost';
$dbname = 'quizacademy';  
$username = 'root';      
$password = '';          

$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);