<?php

require 'connexion.php';
session_start();
echo "Welcome " . $_SESSION['user'] . "<br>"; 

if (!isset($_SESSION['user'])) {
    // Redirect to login if user is not logged in
    header("Location: index.php");
    exit();
}

$username = $_SESSION['user'];

// Get full user info
$stmt = $pdo->prepare("SELECT * FROM users WHERE userName = ?");
$stmt->execute([$username]);
$userInfo = $stmt->fetch(PDO::FETCH_OBJ);

if ($userInfo) {
    $userEmail = $userInfo->userEmail ;
    $userGroupe = $userInfo->userGroupe ;
    echo "email : $userEmail <br>";
    echo "groupe : $userGroupe <br>";

} else {
    echo "User not found.";
}

$query = "select * from subjects";
$subjects = $pdo->query($query)->fetchAll(PDO::FETCH_OBJ);
$questions = []; // Initialize $questions as an empty array

if (isset($_GET['subjectId'])) {
    $query = "SELECT a.questionId, q.questionName, a.answerId, a.answerName 
        FROM questions q
        JOIN answers a ON a.questionId = q.questionId
        WHERE q.subjectId = " . $_GET['subjectId'];
    $questions = $pdo->query($query)->fetchAll(PDO::FETCH_OBJ);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="images/Q-A.png" type="image/x-png">
    <link rel="stylesheet" href="./style/style.css">
    <title>Quiz Academy</title>
</head>
<body>
    <div class="container">
        <header>
            <h1>Quiz Academy</h1>
            <p>Test your knowledge on various subjects</p>
        </header>
        
        <div class="subject-selection">
            <?php foreach($subjects as $subject): ?>
                <a href="quiz.php?subjectId=<?= $subject->subjectId ?>">
                    <button class="subject-btn"><?= htmlspecialchars($subject->subjectName) ?></button>
                </a>
            <?php endforeach; ?>
        </div>
        
        <?php if (!empty($questions)): ?>
        <div class="quiz-container">
            <form method="post" action="quiz.php">
                <?php
                $currentQuestionId = null;
                foreach($questions as $index => $question): 
                    if ($currentQuestionId !== $question->questionId):
                        if ($currentQuestionId !== null):
                            echo '</div></div>'; // Close previous answer-options and question-card
                        endif;
                        $currentQuestionId = $question->questionId;
                ?>
                    <div class="question-card">
                        <div class="question-text">
                            <?= htmlspecialchars($question->questionName) ?>
                        </div>
                        <div class="answer-options">
                            <div class="answer-option">
                                <label>
                                    <input type="radio" name="q<?= $question->questionId ?>" 
                                        value="<?= $question->answerId ?>">
                                    <?= htmlspecialchars($question->answerName) ?>
                                </label>
                            </div>
                <?php else: ?>
                            <div class="answer-option">
                                <label>
                                    <input type="radio" name="q<?= $question->questionId ?>" 
                                        value="<?= $question->answerId ?>">
                                    <?= htmlspecialchars($question->answerName) ?>
                                </label>
                            </div>
                <?php endif; ?>
                <?php endforeach; ?>
                <?php if (!empty($questions)) echo '</div></div>'; // Close last answer-options and question-card ?>
                
                <button type="submit" class="submit-btn">Submit Answers</button>
            </form>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>