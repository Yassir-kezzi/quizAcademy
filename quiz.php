<?php
require 'connexion.php';
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$username = $_SESSION['user'];

// Get full user info
$stmt = $pdo->prepare("SELECT * FROM users WHERE userName = ?");
$stmt->execute([$username]);
$userInfo = $stmt->fetch(PDO::FETCH_OBJ);

if ($userInfo) {
    $userEmail = $userInfo->userEmail;
    $userGroupe = $userInfo->userGroupe;
    
} else {
    echo "User not found.";
}

$query = "SELECT * FROM subjects";
$subjects = $pdo->query($query)->fetchAll(PDO::FETCH_OBJ);

$questions = [];
$submitted = false;
$score = 0;
$answersStatus = [];

if (isset($_GET['subjectId'])) {
    $subjectId = (int)$_GET['subjectId'];

    $stmt = $pdo->prepare("SELECT a.questionId, q.questionName, a.answerId, a.answerName 
                        FROM questions q
                        JOIN answers a ON a.questionId = q.questionId
                        WHERE q.subjectId = ?");
    $stmt->execute([$subjectId]);
    $questions = $stmt->fetchAll(PDO::FETCH_OBJ);

    // Process form submission
    if (isset($_POST['submit'])) {
        $submitted = true;
        $questionIds = array_unique(array_column($questions, 'questionId'));
        $totalQuestions = count($questionIds); 

        foreach ($questionIds as $questionId) {
            $inputName = "q" . $questionId;

            if (isset($_POST[$inputName])) {
                $selectedAnswerId = (int)$_POST[$inputName];

                // Fetch correct answer from DB
                $stmt = $pdo->prepare("SELECT answerId FROM answers WHERE questionId = ? AND isCorrect = 1 LIMIT 1");
                $stmt->execute([$questionId]);
                $correctAnswerId = (int)$stmt->fetchColumn();

                if ($selectedAnswerId === $correctAnswerId) {
                    $score++;
                }

                $answersStatus[$questionId] = [
                    'selected' => $selectedAnswerId,
                    'correct' => $correctAnswerId
                ];
            }
        }
        // Save Score
        if ($totalQuestions > 0) {
            $note = ($score / $totalQuestions) * 10;
        } else {
            $note = 0.00;
        }
        
        $examDate = date('Y-m-d'); 

        // Préparer et exécuter la requête d'insertion
        $stmt = $pdo->prepare("INSERT INTO scores (subjectId, note, examDate) VALUES (?, ?, ?)");
        $stmt->execute([$subjectId, $note, $examDate]);
    }
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <title>Quiz Academy</title>
    <link rel="stylesheet" href="./style/style.css">
    <link rel="shortcut icon" href="images/Q-A.png" type="image/x-png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="container">
        <header>
            <div class="logo">
                <img src="images/Q-A.png" alt="Quiz Academy Logo">
                <h1>Quiz Academy</h1>
            </div>
            <div class="user-info-header">
                <i class="fas fa-user-circle"></i>
                <span>Welcome, <?= htmlspecialchars($_SESSION['user']) ?></span>
            </div>
        </header>

        <div class="user-details-card">
            <?php if ($userInfo): ?>
                <p><i class="fas fa-envelope"></i> Email: <?= htmlspecialchars($userEmail) ?></p>
                <p><i class="fas fa-users"></i> Group: <?= htmlspecialchars($userGroupe) ?></p>
            <?php endif; ?>
        </div>

        <?php if ($submitted): ?>
            <div class="score-message">
                <p>You got <?= $score ?> points out of <?= count(array_unique(array_column($questions, 'questionId'))) ?> questions.</p>
            </div>
        <?php endif; ?>

        <div class="subject-selection">
            <h2>Choose a Subject</h2>
            <div class="subject-buttons-wrapper">
                <?php foreach ($subjects as $subject): ?>
                    <a href="quiz.php?subjectId=<?= $subject->subjectId ?>">
                        <button class="subject-btn"><i class="fas fa-book"></i> <?= htmlspecialchars($subject->subjectName) ?></button>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if (!empty($questions)): ?>
            <div class="quiz-container">
                <form method="post" action="quiz.php?subjectId=<?= htmlspecialchars($_GET['subjectId']) ?>">
                    <?php
                    $currentQuestionId = null;
                    foreach ($questions as $index => $question):
                        if ($currentQuestionId !== $question->questionId):
                            if ($currentQuestionId !== null):
                                echo '</div></div>'; // close previous blocks
                            endif;
                            $currentQuestionId = $question->questionId;
                    ?>
                        <div class="question-card">
                            <div class="question-text">
                                <span class="question-number">Question <?= array_search($question->questionId, array_unique(array_column($questions, 'questionId'))) + 1 ?>:</span>
                                <?= htmlspecialchars($question->questionName) ?>
                            </div>
                            <div class="answer-options">
                    <?php endif;

                    $questionId = $question->questionId;
                    $answerId = $question->answerId;
                    $isSelected = isset($answersStatus[$questionId]) && $answersStatus[$questionId]['selected'] == $answerId;
                    $isCorrectAnswer = isset($answersStatus[$questionId]) && $answersStatus[$questionId]['correct'] == $answerId;

                    $class = '';
                    if ($submitted) {
                        if ($isCorrectAnswer) {
                            $class = 'correct';
                        } elseif ($isSelected && !$isCorrectAnswer) { // Only mark incorrect if selected and not correct
                            $class = 'incorrect';
                        }
                    }
                    ?>
                        <div class="answer-option <?= $class ?>">
                            <label>
                                <input type="radio" name="q<?= $questionId ?>" value="<?= $answerId ?>"
                                    <?= $isSelected ? 'checked' : '' ?>
                                    <?= $submitted ? 'disabled' : '' ?>>
                                <?= htmlspecialchars($question->answerName) ?>
                                <?php if ($submitted): ?>
                                    <?php if ($isCorrectAnswer): ?>
                                        <i class="fas fa-check-circle correct-icon"></i>
                                    <?php elseif ($isSelected && !$isCorrectAnswer): ?>
                                        <i class="fas fa-times-circle incorrect-icon"></i>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                    <?php if (!empty($questions)) echo '</div></div>'; ?>

                    <?php if (!$submitted): ?>
                        <button type="submit" name="submit" class="submit-btn">Submit Answers <i class="fas fa-paper-plane"></i></button>
                    <?php else: ?>
                        <div class="quiz-completed-message">
                            Quiz Completed! Review your answers above.
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
