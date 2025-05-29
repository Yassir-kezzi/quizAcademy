<?php
require 'connexion.php';
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

echo "Welcome " . $_SESSION['user'] . "<br>";

$username = $_SESSION['user'];

// Get full user info
$stmt = $pdo->prepare("SELECT * FROM users WHERE userName = ?");
$stmt->execute([$username]);
$userInfo = $stmt->fetch(PDO::FETCH_OBJ);

if ($userInfo) {
    $userEmail = $userInfo->userEmail;
    $userGroupe = $userInfo->userGroupe;
    echo "email : $userEmail <br>";
    echo "groupe : $userGroupe <br>";
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
    <style>
        .score-message {
            font-family: 'Poppins', sans-serif;
            text-align: center;
            font-size: 1.5rem;
            font-weight: bold;
            background-color: #f0f0f0;
            border: 2px solid #007bff;
            padding: 15px;
            margin-bottom: 20px;
            color: #007bff;
            border-radius: 10px;
        }
        .correct {
            background-color: #d4edda;
            border: 2px solid #28a745;
            border-radius: 8px;
            padding: 5px;
            margin: 4px 0;
        }
        .incorrect {
            background-color: #f8d7da;
            border: 2px solid #dc3545;
            border-radius: 8px;
            padding: 5px;
            margin: 4px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Quiz Academy</h1>
        </header>

        <?php if ($submitted): ?>
            <div class="score-message">
                <p>you got <?= $score ?> points from<?= count(array_unique(array_column($questions, 'questionId'))) ?> </p>
            </div>
        <?php endif; ?>

        <div class="subject-selection">
            <?php foreach ($subjects as $subject): ?>
                <a href="quiz.php?subjectId=<?= $subject->subjectId ?>">
                    <button class="subject-btn"><?= htmlspecialchars($subject->subjectName) ?></button>
                </a>
            <?php endforeach; ?>
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
                        } elseif ($isSelected) {
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
                            </label>
                        </div>
                    <?php endforeach; ?>
                    <?php if (!empty($questions)) echo '</div></div>'; ?>

                    <button type="submit" name="submit" class="submit-btn">submit answers</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
