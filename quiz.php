
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

    <style>
        
        .timer {
            width: 100%;
            max-width: 400px;
            margin: 20px auto;
            color: #5100b9;
            font-size: 18px;
            font-weight: bold;
            text-align: center;
        }

        .timer.temps-urgent {
            color: #c62828;
        }

        .timer-barre-fond {
            width: 100%;
            height: 12px;
            margin-top: 10px;
            overflow: hidden;
            background-color: #d9d9d9;
            border-radius: 10px;
        }

        .timer-barre {
            width: 100%;
            height: 100%;
            background-color: #5100b9;
            border-radius: 10px;
            transition: width 1s linear, background-color 0.3s;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .interface {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 18px;
            width: min(100%, 370px);
        }

        .interface-resultat {
            width: min(100%, 460px);
        }

        .interface-interactive {
            width: min(100%, 680px);
            gap: 20px;
            padding: 24px;
        }

        .carte {
            background: white;
            border: 1px solid #222;
            padding: 10px;
            flex: 1;
            min-width: 0;
            min-height: 320px;
        }

        .interface-interactive .carte {
            min-height: 420px;
            padding: 22px;
        }

        .interface-resultat .carte {
            min-height: 0;
            width: 100%;
            padding: 22px;
        }

        .interface-resultat .resultat {
            font-size: 17px;
        }

        .interface-interactive h1 {
            font-size: 32px;
            margin-bottom: 8px;
        }

        .interface-interactive .progression {
            font-size: 15px;
            margin-bottom: 24px;
        }

        .interface-interactive .question {
            font-size: 20px;
            margin-bottom: 24px;
        }

        h1 {
            color: #5100b9;
            font-size: 22px;
            font-weight: normal;
            margin: 0 0 3px;
        }

        .progression {
            color: #5100b9;
            font-size: 11px;
            margin-bottom: 12px;
        }

        .question {
            font-size: 14px;
            margin-bottom: 12px;
            line-height: 1.4;
        }

        .reponses {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .interface-interactive .reponses {
            gap: 14px;
        }

        .reponse {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 41px;
            padding: 8px 5px;
            background: #4b00ad;
            color: white;
            border: 2px solid #4b00ad;
            border-radius: 11px;
            font-size: 10px;
            cursor: pointer;
            text-align: center;
            transition: background-color 1s ease, border-color 1s ease;
        }

        .interface-interactive .reponse {
            min-height: 72px;
            padding: 12px;
            font-size: 15px;
        }

        .reponse:hover {
            background: #6b20d0;
        }

        .reponse:has(input:checked) {
            background: #7c35d5;
            border-color: #b18aff;
        }

        .reponse-correcte,
        .reponse-correcte:hover,
        .reponse-correcte:has(input:checked) {
            background: #218838;
            border-color: #176b2b;
            color: white;
        }

        .reponse-incorrecte,
        .reponse-incorrecte:hover,
        .reponse-incorrecte:has(input:checked) {
            background: #c62828;
            border-color: #9f1f1f;
            color: white;
        }

        .reponse-verrouillee {
            cursor: default;
        }

        .reponse input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .navigation {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
        }

        .fleche {
            width: 33px;
            height: 33px;
            background: #4100f5;
            border: 1px solid #111;
            border-radius: 9px;
            color: white;
            font-size: 20px;
            cursor: pointer;
        }

        .interface-interactive .fleche {
            width: 48px;
            height: 48px;
            font-size: 28px;
        }

        .fleche:hover {
            background: #6b39ff;
        }

        .fleche:disabled,
        .fleche:disabled:hover {
            background: #4100f5;
            cursor: not-allowed;
            opacity: 0.5;
        }

        .aide {
            font-size: 9px;
            color: #333;
            text-align: center;
            max-width: 48px;
        }

        .interface-interactive .aide {
            font-size: 12px;
            max-width: 64px;
        }

        .resultat {
            color: #111;
            font-size: 13px;
        }

        .resultat-titre {
            margin: 0 0 7px;
            color: #6200ee;
            font-size: 30px;
            font-weight: normal;
            line-height: 1.15;
            overflow-wrap: anywhere;
        }

        .resultat-progression {
            margin: 0 0 22px;
            color: #6200ee;
            font-size: 15px;
        }

        .resultat-resume {
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .resultat-resume p {
            margin: 0;
        }

        .classement {
            max-height: 220px;
            overflow-y: auto;
            padding: 10px;
            border-radius: 10px;
            background: #6200aa;
        }

        .classement-ligne {
            margin: 0 0 7px;
            padding: 9px 10px;
            border-radius: 7px;
            background: #9575cd;
            color: white;
            font-size: 14px;
            overflow-wrap: anywhere;
        }

        .classement-ligne:last-child {
            margin-bottom: 0;
        }

        .classement-ligne-vous {
            background: #7e57c2;
            font-weight: bold;
        }

        .classement-indisponible {
            margin: 0;
            color: white;
            font-size: 14px;
            line-height: 1.5;
        }

        .bouton-recommencer {
            background: #4b00ad;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 18px;
            font-size: 15px;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .bouton-recommencer:hover {
            background: #35007a;
        }

        .resultat-actions {
            display: flex;
            align-items: stretch;
            justify-content: space-between;
            gap: 16px;
            margin-top: 14px;
        }

        .resultat-actions form,
        .resultat-actions .bouton-menu {
            flex: 1 1 0;
        }

        .resultat-actions form {
            display: flex;
        }

        .bouton-menu {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            padding: 12px 18px;
            background: #2577d9;
            color: white;
            font-size: 15px;
            text-decoration: none;
            text-align: center;
        }

        .bouton-recommencer {
            width: 100%;
        }

        .bouton-menu:hover {
            background: #1d5fae;
        }

        @media (max-width: 350px) {
            .interface {
                padding: 8px;
                gap: 8px;
            }

            .interface-interactive {
                width: 100%;
            }

            .interface-interactive .carte {
                min-height: 340px;
                padding: 12px;
            }

            .interface-interactive h1 {
                font-size: 23px;
            }

            .interface-interactive .question {
                font-size: 15px;
            }

            .interface-interactive .reponse {
                min-height: 52px;
                padding: 6px;
                font-size: 11px;
            }

            .interface-interactive .fleche {
                width: 40px;
                height: 40px;
                font-size: 23px;
            }

            .interface-resultat {
                width: 100%;
            }

            .interface-resultat .carte {
                padding: 14px;
            }

            .resultat-titre {
                font-size: 25px;
            }

            .resultat-progression {
                margin-bottom: 16px;
                font-size: 13px;
            }

            .interface-resultat .resultat {
                font-size: 15px;
            }

            .resultat-actions {
                flex-direction: column;
                gap: 10px;
            }

            .carte {
                padding: 8px;
            }

            h1 {
                font-size: 19px;
            }
        }

        @media (min-width: 351px) and (max-width: 600px) {
            .interface-interactive {
                width: 100%;
                gap: 12px;
                padding: 16px;
            }

            .interface-interactive .carte {
                min-height: 360px;
                padding: 16px;
            }

            .interface-interactive h1 {
                font-size: 26px;
            }

            .interface-interactive .question {
                font-size: 17px;
            }

            .interface-interactive .reponse {
                min-height: 58px;
                padding: 8px;
                font-size: 13px;
            }
        }
    </style>
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
                            $classeReponse .= $reponseCorrecte
                                ? " reponse-correcte"
                                : " reponse-incorrecte";
                            $classeReponse .= " reponse-verrouillee";
                        }
                        ?>

                        <label
                            class="<?= $classeReponse ?>"
                            data-correct="<?= $reponseCorrecte ? 'true' : 'false' ?>"
                        >
                            <input
                                type="radio"
                                name="reponse"
                                value="<?= $reponseIndex ?>"
                                <?= $reponseSelectionnee !== null && $reponseSelectionnee["option_id"] !== null && (int) $reponseSelectionnee["option_id"] === $reponseIndex ? "checked" : "" ?>
                                <?= $reponseVerrouillee ? "disabled" : "required" ?>
                                onchange="verifierReponse(this)"
                            >
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
                            $nomJoueur = $estJoueurActuel
                                ? "vous"
                                : htmlspecialchars((string) $joueur["nom"], ENT_QUOTES, 'UTF-8');
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
                        <button
                            type="submit"
                            name="restart"
                            class="bouton-recommencer"
                        >
                            Recommencer
                        </button>
                    </form>
                    <a href="index.php" class="bouton-menu">
                        Retour au menu des quiz
                    </a>
                </div>
            </div>

        <?php endif; ?>

    </div>

    <?php if (!$termine): ?>

        <div class="navigation">
            <button
                type="submit"
                name="suivant"
                value="1"
                class="fleche"
                form="quizForm"
                aria-label="Question suivante"
                <?= $reponseSelectionnee === null ? "disabled" : "" ?>
            >
                ▶
            </button>

            <div class="aide"><?= $reponseSelectionnee === null ? "Choisis une réponse" : "Suivant" ?></div>
        </div>

    <?php endif; ?>

</div>
<script>

    
    let intervalleTimer = null;

    function afficherCorrection(formulaire) {
        formulaire.dataset.reponseVerrouillee = "true";

        const cases = Array.from(formulaire.querySelectorAll(".reponse"));

        cases.forEach((caseReponse) => {
            caseReponse.classList.add(
                caseReponse.dataset.correct === "true"
                    ? "reponse-correcte"
                    : "reponse-incorrecte",
                "reponse-verrouillee"
            );
        });

        formulaire.querySelectorAll('input[type="radio"]').forEach((radio) => {
            radio.disabled = true;
        });
    }

    function lancerTimer() {
        const affichage = document.getElementById("tempsRestant");
        const timer = document.getElementById("timer");
        const barre = document.getElementById("timerBarre");
        const formulaire = document.getElementById("quizForm");

        if (!affichage || !timer || !barre || !formulaire) {
            return;
        }

        let temps = 30;

        timer.style.color = "#5100b9";
        barre.style.width = "100%";
        barre.style.backgroundColor = "#5100b9";

        intervalleTimer = window.setInterval(() => {
            temps--;

            affichage.textContent = temps;
            barre.style.width = (temps / 30 * 100) + "%";

            if (temps <= 5) {
                timer.style.color = "#c62828";
                barre.style.backgroundColor = "#c62828";
            }

            if (temps <= 0) {
                window.clearInterval(intervalleTimer);
                intervalleTimer = null;

                afficherCorrection(formulaire);

                const tempsEcoule = document.createElement("input");
                tempsEcoule.type = "hidden";
                tempsEcoule.name = "temps_ecoule";
                tempsEcoule.value = "1";
                formulaire.appendChild(tempsEcoule);

                // Envoie le formulaire sans exiger une réponse radio.
                window.setTimeout(() => formulaire.submit(), 1000);
            }
        }, 1000);
    }

    lancerTimer();

    function verifierReponse(reponse) {
        const formulaire = reponse.form;

        if (
            !formulaire ||
            formulaire.dataset.reponseVerrouillee === "true"
        ) {
            return;
        }

        if (intervalleTimer !== null) {
            window.clearInterval(intervalleTimer);
            intervalleTimer = null;
        }

        afficherCorrection(formulaire);

        const valeurReponse = document.createElement("input");
        valeurReponse.type = "hidden";
        valeurReponse.name = "reponse";
        valeurReponse.value = reponse.value;
        formulaire.appendChild(valeurReponse);

        window.setTimeout(() => formulaire.submit(), 1000);
    }

</script>
</body>
</html>
