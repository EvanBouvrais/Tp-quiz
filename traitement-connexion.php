<?php

session_start();

require_once("connexion.php");

// Vérifier que le formulaire a été envoyé
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

// Récupérer et nettoyer le pseudonyme
$username = trim($_POST["username"] ?? "");

// Vérifier que le pseudonyme est valide
if ($username === "" || strlen($username) > 50) {
    header("Location: index.php");
    exit;
}

try {
    // Rechercher un utilisateur ayant ce pseudonyme
    $query = $mysqlClient->prepare(
        "SELECT user_id, user_name
         FROM users
         WHERE user_name = :username"
    );

    $query->execute([
        "username" => $username
    ]);

    $user = $query->fetch(PDO::FETCH_ASSOC);

    // Si le pseudonyme n'existe pas, créer un utilisateur
    if (!$user) {
        $query = $mysqlClient->prepare(
            "INSERT INTO users (user_name)
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
    }

    // Renouveler l'identifiant de session
    session_regenerate_id(true);

    // Enregistrer l'identité de l'utilisateur
    $_SESSION["user_id"] = (int) $userId;
    $_SESSION["username"] = $username;

    // Retourner à la page des quiz
    header("Location: index.php");
    exit;

} catch (PDOException $e) {
    http_response_code(500);
    exit("Une erreur est survenue lors de la connexion.");
}
