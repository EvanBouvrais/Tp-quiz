<?php
session_start();
require_once("connexion.php");

try {
if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}

$quizIdInput = $_POST["quiz_id"] ?? $_GET["id"] ?? null;
$quizId = is_scalar($quizIdInput) ? filter_var(
    $quizIdInput,
    FILTER_VALIDATE_INT,
    ["options" => ["min_range" => 1]]
) : false;

if ($quizId === false || $quizId === null) {
    header("Location: index.php");
    exit;
}

if (isset($_GET["new"]) && $_SERVER["REQUEST_METHOD"] === "GET") {
    unset($_SESSION["quiz_id"]);
    header("Location: quiz.php?id=" . (int) $quizId);
    exit;
}

$quizQuery = $mysqlClient->prepare(
    "SELECT quiz_id, title
     FROM quizzes
     WHERE quiz_id = :quiz_id"
);
$quizQuery->execute(["quiz_id" => $quizId]);
$quiz = $quizQuery->fetch();

if (!$quiz) {
    http_response_code(404);
    exit("Ce quiz n'existe pas.");
}

$questionQuery = $mysqlClient->prepare(
    "SELECT q.question_id, q.question_text, q.points,
            a.option_id, a.option_text, a.is_correct
     FROM questions AS q
     LEFT JOIN answer_options AS a ON a.question_id = q.question_id
     WHERE q.quiz_id = :quiz_id
     ORDER BY q.question_order, a.option_id"
);
$questionQuery->execute(["quiz_id" => $quizId]);
$questions = [];

foreach ($questionQuery->fetchAll() as $row) {
    $questionId = (int) $row["question_id"];

    if (!isset($questions[$questionId])) {
        $questions[$questionId] = [
            "question" => $row["question_text"],
            "points" => (int) $row["points"],
            "reponses" => [],
            "correcte" => null,
        ];
    }

    if ($row["option_id"] !== null) {
        $optionId = (int) $row["option_id"];
        $questions[$questionId]["reponses"][$optionId] = $row["option_text"];
        if ((int) $row["is_correct"] === 1) {
            $questions[$questionId]["correcte"] = $optionId;
        }
    }
}

if ($questions === []) {
    http_response_code(404);
    exit("Ce quiz ne contient aucune question.");
}

foreach ($questions as $question) {
    if ($question["reponses"] === [] || $question["correcte"] === null) {
        http_response_code(500);
        exit("Une question de ce quiz n'a aucune réponse correcte configurée.");
    }
}

$total = array_sum(array_column($questions, "points"));
if ($total <= 0) {
    http_response_code(500);
    exit("Le total des points de ce quiz doit être supérieur à zéro.");
}
$nouvellePartie = !isset($_SESSION["quiz_id"])
    || (int) $_SESSION["quiz_id"] !== (int) $quizId
    || !isset($_SESSION["question"], $_SESSION["score"])
    || !isset($_SESSION["ordre_questions"], $_SESSION["ordre_reponses"])
    || !is_array($_SESSION["ordre_questions"])
    || !is_array($_SESSION["ordre_reponses"])
    || count($_SESSION["ordre_questions"]) !== count($questions);

if ($nouvellePartie) {
    $_SESSION["quiz_id"] = (int) $quizId;
    $_SESSION["question"] = 0;
    $_SESSION["score"] = 0;
    $_SESSION["score_saved"] = false;
    unset($_SESSION["reponse_selectionnee"]);
    $_SESSION["ordre_questions"] = array_keys($questions);
    shuffle($_SESSION["ordre_questions"]);

    $_SESSION["ordre_reponses"] = [];
    foreach ($questions as $questionId => $question) {
        $_SESSION["ordre_reponses"][$questionId] = array_keys($question["reponses"]);
        shuffle($_SESSION["ordre_reponses"][$questionId]);
    }
}

if (isset($_POST["restart"])) {
    unset($_SESSION["quiz_id"]);
    header("Location: quiz.php?id=" . (int) $quizId);
    exit;
}

if (isset($_POST["suivant"])) {
    $index = $_SESSION["question"];
    $questionId = $_SESSION["ordre_questions"][$index] ?? null;
    if (
        $index < count($questions)
        && is_array($_SESSION["reponse_selectionnee"] ?? null)
        && isset($_SESSION["reponse_selectionnee"])
        && (int) $_SESSION["reponse_selectionnee"]["question_id"] === (int) $questionId
    ) {
        unset($_SESSION["reponse_selectionnee"]);
        $_SESSION["question"]++;
    }

} elseif (isset($_POST["valider_reponse"])) {
    $index = $_SESSION["question"];
    $questionId = $_SESSION["ordre_questions"][$index] ?? null;

    $tempsEcoule = isset($_POST["temps_ecoule"]);

    if (
        $index < count($questions)
        && !isset($_SESSION["reponse_selectionnee"])
        && $questionId !== null
    ) {
        if ($tempsEcoule) {
            // Aucune réponse : la question est considérée comme incorrecte.
            $_SESSION["reponse_selectionnee"] = [
                "question_id" => (int) $questionId,
                "option_id" => null,
            ];
        } else {
            $reponseInput = $_POST["reponse"] ?? null;
            $reponseId = is_scalar($reponseInput)
                ? filter_var($reponseInput, FILTER_VALIDATE_INT)
                : false;

            if (
                $reponseId !== false
                && isset($questions[$questionId]["reponses"][$reponseId])
            ) {
                $_SESSION["reponse_selectionnee"] = [
                    "question_id" => (int) $questionId,
                    "option_id" => $reponseId,
                ];

                if ($reponseId === $questions[$questionId]["correcte"]) {
                    $_SESSION["score"] += $questions[$questionId]["points"];
                }
            }
        }
    }
}


$index = $_SESSION["question"];
$nombreQuestions = count($questions);
$termine = $index >= $nombreQuestions;
$questionId = $_SESSION["ordre_questions"][$index] ?? null;
$reponseSelectionnee = $_SESSION["reponse_selectionnee"] ?? null;
if (
    $questionId === null
    || !is_array($reponseSelectionnee)
    || (int) ($reponseSelectionnee["question_id"] ?? 0) !== (int) $questionId
) {
    $reponseSelectionnee = null;
}
$nomQuiz = $quiz["title"];
$scoreFinal = (int) $_SESSION["score"];
$questionTerminee = min($index, $nombreQuestions);
$classementJoueurs = [];

if ($termine) {
    if (empty($_SESSION["score_saved"])) {
        $saveScoreQuery = $mysqlClient->prepare(
            "INSERT INTO quiz_scores (user_id, quiz_id, score)
             VALUES (:user_id, :quiz_id, :score)"
        );
        $saveScoreQuery->execute([
            "user_id" => (int) $_SESSION["user_id"],
            "quiz_id" => (int) $quizId,
            "score" => $scoreFinal,
        ]);
        $_SESSION["score_saved"] = true;
    }

    $rankingQuery = $mysqlClient->prepare(
        "SELECT u.user_id, u.username AS nom, MAX(qs.score) AS score
         FROM quiz_scores AS qs
         INNER JOIN users AS u ON u.user_id = qs.user_id
         WHERE qs.quiz_id = :quiz_id
         GROUP BY u.user_id, u.username
         ORDER BY score DESC, u.username ASC"
    );
    $rankingQuery->execute(["quiz_id" => $quizId]);
    $classementJoueurs = $rankingQuery->fetchAll();
}

$classementActuel = null;
foreach ($classementJoueurs as $position => $joueur) {
    if ((int) $joueur["user_id"] === (int) $_SESSION["user_id"]) {
        $classementActuel = $position + 1;
        break;
    }
}
} catch (PDOException $exception) {
    error_log("Erreur pendant le chargement du quiz : " . $exception->getMessage());
    http_response_code(500);
    exit("Impossible de charger le quiz ou son classement depuis la base de données.");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz - <?= htmlspecialchars($nomQuiz, ENT_QUOTES, 'UTF-8') ?></title>
    <link href="style.css" rel="stylesheet">
    <link href="quiz.css" rel="stylesheet">
</head>

<body class="index-main-bg">

<div class="interface<?= $termine ? ' interface-resultat' : ' interface-interactive' ?>">

    <div class="carte">

        <?php if (!$termine): ?>

            <h1><?= htmlspecialchars($nomQuiz, ENT_QUOTES, 'UTF-8') ?></h1>

            <div class="progression">
                Question n°<?= $index + 1 ?>/<?= $nombreQuestions ?>
            </div>

            
            <div class="question">
                <?= htmlspecialchars($questions[$questionId]["question"]) ?>
            </div>

            <form method="POST" id="quizForm">

                <div class="reponses">

                    <?php foreach ($_SESSION["ordre_reponses"][$questionId] as $reponseIndex): ?>
                        <?php
                        $reponseVerrouillee = $reponseSelectionnee !== null;
                        $reponseCorrecte = $reponseIndex === $questions[$questionId]["correcte"];
                        $classeReponse = "reponse";
                        if ($reponseVerrouillee) {
                            $classeReponse .= $reponseCorrecte ? " reponse-correcte" : " reponse-incorrecte"; $classeReponse .= " reponse-verrouillee";
                        }
                        ?>

                        <label class="<?= $classeReponse ?>" data-correct="<?= $reponseCorrecte ? 'true' : 'false' ?>">
                            <input type="radio" name="reponse" value="<?= $reponseIndex ?>" <?= $reponseSelectionnee !== null && $reponseSelectionnee["option_id"] !== null && (int) $reponseSelectionnee["option_id"] === $reponseIndex ? "checked" : "" ?> <?= $reponseVerrouillee ? "disabled" : "required" ?> onchange="verifierReponse(this)">
                            <span>
                                <?= htmlspecialchars($questions[$questionId]["reponses"][$reponseIndex]) ?>
                            </span>
                        </label>

                    <?php endforeach; ?>

                </div>

                <input type="hidden" name="quiz_id" value="<?= (int) $quizId ?>">
                <?php if ($reponseSelectionnee === null): ?>
                    <input type="hidden" name="valider_reponse" value="1">
                <?php endif; ?>

            </form>

            <?php if ($reponseSelectionnee === null): ?>
                <div id="timer" class="timer">
                    Temps restant : <span id="tempsRestant">30</span> s

                    <div class="timer-barre-fond">
                        <div id="timerBarre" class="timer-barre"></div>
                    </div>
                </div>
            <?php endif; ?>


        <?php else: ?>

            <div class="resultat">
                <h1 class="resultat-titre">
                    <?= htmlspecialchars($nomQuiz, ENT_QUOTES, 'UTF-8') ?>
                </h1>

                <p class="resultat-progression">
                    Question n°<?= $questionTerminee ?>
                </p>

                <div class="resultat-resume">
                    <p>Félicitations pour avoir</p>
                    <p>complété le quiz !</p>
                    <p>Score final : <strong><?= $scoreFinal ?>/<?= $total ?></strong></p>
                    <p>
                        Classement :
                        <strong>
                            <?= $classementActuel !== null ? $classementActuel . '/' . count($classementJoueurs) : 'indisponible' ?>
                        </strong>
                    </p>
                </div>

                <div class="classement" aria-label="Classement des joueurs">
                    <?php if ($classementJoueurs === []): ?>
                        <p class="classement-indisponible">
                            Aucun classement disponible : les scores ne sont pas enregistrés.
                        </p>
                    <?php else: ?>
                        <?php foreach ($classementJoueurs as $position => $joueur): ?>
                            <?php
                                $estJoueurActuel = isset($_SESSION["user_id"])
                                    && (int) $joueur["user_id"] === (int) $_SESSION["user_id"];
                                $nomJoueur = $estJoueurActuel ? "vous" : htmlspecialchars((string) $joueur["nom"], ENT_QUOTES, 'UTF-8');
                            ?>
                            <p class="classement-ligne<?= $estJoueurActuel ? ' classement-ligne-vous' : '' ?>">
                                <?= $nomJoueur ?> : <?= $position + 1 ?>e <?= (int) $joueur["score"] ?>/<?= $total ?>
                            </p>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="resultat-actions">
                    <form method="POST">
                        <input type="hidden" name="quiz_id" value="<?= (int) $quizId ?>">
                        <button type="submit" name="restart" class="bouton-recommencer">Recommencer</button>
                    </form>
                    <a href="index.php" class="bouton-menu">Retour au menu des quiz</a>
                </div>
            </div>

        <?php endif; ?>

    </div>

    <?php if (!$termine): ?>

        <div class="navigation">
            <button type="submit" name="suivant" value="1" class="fleche" form="quizForm" aria-label="Question suivante" <?= $reponseSelectionnee === null ? "disabled" : "" ?>>▶</button>

            <div class="aide"><?= $reponseSelectionnee === null ? "Choisis une réponse" : "Suivant" ?></div>
        </div>

    <?php endif; ?>

</div>
<script src="quiz.js"></script>
</body>
</html>
