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