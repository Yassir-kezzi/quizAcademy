<?php

require 'connexion.php';  
session_start();
echo "Welcome " . $_SESSION['user'];
$query = "select * from subjects";
$subjects = $pdo->query($query)->fetchAll(PDO::FETCH_OBJ);
$questions = []; // Initialize $questions as an empty array

if (isset($_GET['subject_Id'])) {
    $query = "SELECT a.question_Id, q.question_Name, a.answer_Id, a.answer_Name 
        FROM questions q
        JOIN answers a ON a.question_Id = q.question_Id
        WHERE q.subject_Id = " . $_GET['subject_Id'];
    $questions = $pdo->query($query)->fetchAll(PDO::FETCH_OBJ);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Academy</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./style/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Quiz Academy</h1>
            <p>Test your knowledge on various subjects</p>
        </header>
        
        <div class="subject-selection">
            <?php foreach($subjects as $subject): ?>
                <a href="quiz.php?subject_Id=<?= $subject->subject_Id ?>">
                    <button class="subject-btn"><?= htmlspecialchars($subject->subject_Name) ?></button>
                </a>
            <?php endforeach; ?>
        </div>
        
        <?php if (!empty($questions)): ?>
        <div class="quiz-container">
            <form method="post">
                <?php
                $currentQuestionId = null;
                foreach($questions as $index => $question): 
                    if ($currentQuestionId !== $question->question_Id):
                        if ($currentQuestionId !== null):
                            echo '</div></div>'; // Close previous answer-options and question-card
                        endif;
                        $currentQuestionId = $question->question_Id;
                ?>
                    <div class="question-card">
                        <div class="question-text">
                            <?= htmlspecialchars($question->question_Name) ?>
                        </div>
                        <div class="answer-options">
                            <div class="answer-option">
                                <label>
                                    <input type="radio" name="q<?= $question->question_Id ?>" 
                                        value="<?= $question->answer_Id ?>">
                                    <?= htmlspecialchars($question->answer_Name) ?>
                                </label>
                            </div>
                <?php else: ?>
                            <div class="answer-option">
                                <label>
                                    <input type="radio" name="q<?= $question->question_Id ?>" 
                                        value="<?= $question->answer_Id ?>">
                                    <?= htmlspecialchars($question->answer_Name) ?>
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


<!-- hello yassir -->