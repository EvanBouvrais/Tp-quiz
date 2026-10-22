<?php

session_start();

// Vérifier que le formulaire a été envoyé
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

require_once("connexion.php");

// Récupérer et nettoyer le pseudonyme
$usernameInput = $_POST["username"] ?? "";
$username = is_string($usernameInput) ? trim($usernameInput) : "";
$quizIdInput = $_POST["quiz_id"] ?? null;
$quizId = is_scalar($quizIdInput)
    ? filter_var($quizIdInput, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]])
    : false;

// Vérifier que le pseudonyme est valide
if ($username === "" || mb_strlen($username, "UTF-8") > 30) {
    header("Location: index.php");
    exit;
}

try {
    // Rechercher un utilisateur ayant ce pseudonyme
    $query = $mysqlClient->prepare(
        "SELECT user_id, username
         FROM users
         WHERE username = :username"
    );

    $query->execute([
        "username" => $username
    ]);

    $user = $query->fetch(PDO::FETCH_ASSOC);

    // Si le pseudonyme n'existe pas, créer un utilisateur
    if (!$user) {
        $query = $mysqlClient->prepare(
            "INSERT INTO users (username)
             VALUES (:username)"
        );

        $query->execute([
            "username" => $username
        ]);

        // Récupérer l'identifiant du nouvel utilisateur
        $userId = $mysqlClient->lastInsertId();

    } else {
        // L'utilisateur existe déjà
        $userId = $user["user_id"];
        $username = $user["username"];
    }

    $ancienneIdentite = $_SESSION["user_id"] ?? null;

    // Renouveler l'identifiant de session
    session_regenerate_id(true);

    if ($ancienneIdentite !== null && (int) $ancienneIdentite !== (int) $userId) {
        unset(
            $_SESSION["quiz_id"],
            $_SESSION["question"],
            $_SESSION["score"],
            $_SESSION["ordre_questions"],
            $_SESSION["ordre_reponses"],
            $_SESSION["score_saved"]
        );
    }

    // Enregistrer l'identité de l'utilisateur
    $_SESSION["user_id"] = (int) $userId;
    $_SESSION["username"] = $username;

    if ($quizId !== false && $quizId !== null) {
        $query = $mysqlClient->prepare(
            "SELECT quiz_id
             FROM quizzes
             WHERE quiz_id = :quiz_id"
        );
        $query->execute(["quiz_id" => $quizId]);
        $selectedQuizId = $query->fetchColumn();

        if ($selectedQuizId !== false) {
            header("Location: quiz.php?id=" . (int) $selectedQuizId . "&new=1");
            exit;
        }
    }

    header("Location: index.php");
    exit;

} catch (PDOException $e) {
    error_log("Erreur pendant la connexion utilisateur : " . $e->getMessage());
    http_response_code(500);
    exit("Une erreur est survenue lors de la connexion.");
}
