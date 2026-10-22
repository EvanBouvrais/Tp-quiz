
<?php
session_start();

// Questions du quiz
$questions = [
    [
        "question" => "Dans la partie 3 de JoJo's bizare adventure, les noms des stands sont lié a quoi ?",
        "reponses" => ["Au cartes de tarots", "A différent titre de musique", "Au manieur qui décide du nom", "A la force mental du manieur"],
        "correcte" => 0
    ],
    [
        "question" => "Qui est l'ennemi intemporel de la famille Joestar ?",
        "reponses" => ["Yoshikage Kira", "Dio Brando", "La fondation Speedwagon", "Diavolo"],
        "correcte" => 1
    ],
    [
        "question" => "Quels est le nom de la capacité utiliser dans les premières parties ?",
        "reponses" => ["La respiration", "Les stands", "L'intelligence", "L'onde"],
        "correcte" => 3
    ],
    [
        "question" => "Qui est le célébre mangaka vivant a Morioh ?",
        "reponses" => ["Jean-Pierre Polnareff", "Rohan Kishibe", "kakyoin Noriaki", "César Zeppeli"],
        "correcte" => 1
    ],
    [
        "question" => "Comment s'appel le stand de Jean-Pierre Polnareff ?",
        "reponses" => ["Silver Chariot", "Star Platinum", "Killer Queen", "Golden Experience"],
        "correcte" => 0
    ]
];

function creerOrdresAleatoires(array $questions): array
{
    $ordreQuestions = array_keys($questions);
    shuffle($ordreQuestions);

    $ordreReponses = [];

    foreach ($questions as $questionId => $question) {
        $ordreReponses[$questionId] = array_keys($question["reponses"]);
        shuffle($ordreReponses[$questionId]);
    }

    return [$ordreQuestions, $ordreReponses];
}

// Initialisation du quiz
if (
    !isset(
        $_SESSION["question"],
        $_SESSION["score"],
        $_SESSION["ordre_questions"],
        $_SESSION["ordre_reponses"]
    )
    || count($_SESSION["ordre_questions"]) !== count($questions)
) {
    $_SESSION["question"] = 0;
    $_SESSION["score"] = 0;
    [$_SESSION["ordre_questions"], $_SESSION["ordre_reponses"]] = creerOrdresAleatoires($questions);
}

// Recommencer le quiz
if (isset($_POST["restart"])) {
    $_SESSION["question"] = 0;
    $_SESSION["score"] = 0;
    [$_SESSION["ordre_questions"], $_SESSION["ordre_reponses"]] = creerOrdresAleatoires($questions);
}

// Vérifier la réponse et passer à la question suivante
if (isset($_POST["suivant"])) {
    $index = $_SESSION["question"];
    $questionId = $_SESSION["ordre_questions"][$index] ?? null;
    $reponse = filter_var($_POST["reponse"] ?? null, FILTER_VALIDATE_INT);

    if (
        $reponse !== false
        && isset($questions[$questionId]["reponses"][$reponse])
    ) {
        if ($reponse === $questions[$questionId]["correcte"]) {
            $_SESSION["score"]++;
        }
    }

    $_SESSION["question"]++;
}

$index = $_SESSION["question"];
$total = count($questions);
$termine = $index >= $total;
$questionId = $_SESSION["ordre_questions"][$index] ?? null;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz JoJo's bizare adventure</title>
    <link href="style.css" rel="stylesheet">

    <style>
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

        .carte {
            background: white;
            border: 1px solid #222;
            padding: 10px;
            flex: 1;
            min-width: 0;
            min-height: 320px;
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
            transition: background 0.2s;
        }

        .reponse:hover {
            background: #6b20d0;
        }

        .reponse:has(input:checked) {
            background: #7c35d5;
            border-color: #b18aff;
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

        .fleche:hover {
            background: #6b39ff;
        }

        .aide {
            font-size: 9px;
            color: #333;
            text-align: center;
            max-width: 48px;
        }

        .resultat {
            text-align: center;
            padding: 25px 5px;
        }

        .resultat h2 {
            color: #5100b9;
        }

        .bouton-recommencer {
            background: #4b00ad;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 15px;
            cursor: pointer;
        }

        @media (max-width: 350px) {
            .interface {
                padding: 8px;
                gap: 8px;
            }

            .carte {
                padding: 8px;
            }

            h1 {
                font-size: 19px;
            }
        }
    </style>
</head>

<body class="index-main-bg">

<div class="interface">

    <div class="carte">

        <?php if (!$termine): ?>

            <h1>Mon quiz</h1>

            <div class="progression">
                Question n°<?= $index + 1 ?>/<?= $total ?>
            </div>

            <div class="question">
                <?= htmlspecialchars($questions[$questionId]["question"]) ?>
            </div>

            <form method="POST" id="quizForm">

                <div class="reponses">

                    <?php foreach ($_SESSION["ordre_reponses"][$questionId] as $reponseIndex): ?>

                        <label class="reponse">
                            <input
                                type="radio"
                                name="reponse"
                                value="<?= $reponseIndex ?>"
                                required
                            >
                            <span>
                                <?= htmlspecialchars($questions[$questionId]["reponses"][$reponseIndex]) ?>
                            </span>
                        </label>

                    <?php endforeach; ?>

                </div>

                <input type="hidden" name="suivant" value="1">

            </form>

        <?php else: ?>

            <div class="resultat">
                <h2>Quiz terminé !</h2>

                <p>
                    Ton score :
                    <strong>
                        <?= $_SESSION["score"] ?> / <?= $total ?>
                    </strong>
                </p>

                <p>
                    <?php
                    $pourcentage = round(
                        ($_SESSION["score"] / $total) * 100
                    );
                    ?>
                    Résultat : <?= $pourcentage ?> %
                </p>

                <form method="POST">
                    <button
                        type="submit"
                        name="restart"
                        class="bouton-recommencer"
                    >
                        Recommencer
                    </button>
                </form>
            </div>

        <?php endif; ?>

    </div>

    <?php if (!$termine): ?>

        <div class="navigation">
            <button
                type="button"
                class="fleche"
                onclick="document.getElementById('quizForm').requestSubmit()"
                aria-label="Question suivante"
            >
                ▶
            </button>

            <div class="aide">Suivant</div>
        </div>

    <?php endif; ?>

</div>
</body>
</html>
<?php
session_start();
require_once __DIR__ . "/db.php";

// Vérifier que l'identifiant reçu est un entier valide
$quizId = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if ($quizId === false || $quizId === null || $quizId <= 0) {
    http_response_code(400);
    exit("Identifiant de quiz invalide.");
}

// Récupérer les informations du quiz correspondant
$query = $mysqlClient->prepare(
    "SELECT quiz_id, title, description
     FROM quizzes
     WHERE quiz_id = :quiz_id"
);

$query->execute([
    "quiz_id" => $quizId
]);

$quiz = $query->fetch();

if (!$quiz) {
    http_response_code(404);
    exit("Ce quiz n'existe pas.");
}
?>
