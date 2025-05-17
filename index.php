<?php
require 'connexion.php'; 
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
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4cc9f0;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --success-color: #4bb543;
            --danger-color: #ff3333;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            background-color: #f5f7fa;
            color: var(--dark-color);
            padding: 2rem;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        
        header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        h1 {
            color: var(--primary-color);
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .subject-selection {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            justify-content: center;
            margin-bottom: 3rem;
        }
        
        .subject-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 50px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .subject-btn:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
        }
        
        .quiz-container {
            background-color: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        
        .question-card {
            margin-bottom: 2rem;
            padding: 1.5rem;
            border-radius: 8px;
            background-color: var(--light-color);
            border-left: 4px solid var(--accent-color);
        }
        
        .question-text {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--dark-color);
        }
        
        .answer-options {
            display: flex;
            flex-direction: column;
            gap: 0.8rem;
        }
        
        .answer-option {
            display: flex;
            align-items: center;
            padding: 0.8rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .answer-option:hover {
            background-color: #f0f4f8;
        }
        
        .answer-option input[type="radio"] {
            margin-right: 1rem;
            accent-color: var(--primary-color);
            transform: scale(1.2);
        }
        
        .submit-btn {
            display: block;
            width: 100%;
            max-width: 200px;
            margin: 2rem auto 0;
            padding: 1rem;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .submit-btn:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
        }
        
        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }
            
            h1 {
                font-size: 2rem;
            }
            
            .subject-selection {
                flex-direction: column;
                align-items: center;
            }
            
            .subject-btn {
                width: 100%;
                max-width: 300px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Quiz Academy</h1>
            <p>Test your knowledge on various subjects</p>
        </header>
        
        <div class="subject-selection">
            <?php foreach($subjects as $subject): ?>
                <a href="index.php?subject_Id=<?= $subject->subject_Id ?>">
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