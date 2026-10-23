<?php

$databaseHost = getenv("DB_HOST") ?: "127.0.0.1";
$databaseName = getenv("DB_NAME") ?: "mini-quizz";
$databaseUser = getenv("DB_USER") ?: "root";
$databasePassword = getenv("DB_PASSWORD");

try {
    $mysqlClient = new PDO(
        "mysql:host={$databaseHost};dbname={$databaseName};charset=utf8mb4",
        $databaseUser,
        $databasePassword === false ? "" : $databasePassword,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $exception) {
    error_log("Connexion à la base de données impossible : " . $exception->getMessage());
    http_response_code(500);
    exit("Impossible de se connecter à la base de données. Vérifie la configuration DB_HOST, DB_NAME, DB_USER et DB_PASSWORD.");
}
