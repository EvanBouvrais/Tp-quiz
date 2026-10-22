<?php
// session_start();
// header('Content-Type: application/json; charset=utf-8');

// function repondre(int $code, array $donnees): void
// {
//     http_response_code($code);
//     echo json_encode($donnees, JSON_UNESCAPED_UNICODE);
//     exit;
// }

// try {
//     require __DIR__ . '/db.php';

//     if ($_SERVER['REQUEST_METHOD'] === 'GET') {
//         $stmt = $mysqlClient->query(
//             "SELECT messages.id, messages.content,
//                     DATE_FORMAT(messages.created_at, '%H:%i') AS time,
//                     users.username
//              FROM messages
//              INNER JOIN users ON users.id = messages.user_id
//              ORDER BY messages.id DESC
//              LIMIT 100"
//         );
//         $messages = array_reverse($stmt->fetchAll());

//         repondre(200, ['success' => true, 'messages' => $messages]);
//     }

//     if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
//         repondre(405, ['success' => false, 'message' => 'Méthode non autorisée.']);
//     }

//     if (!isset($_SESSION['user_id'])) {
//         repondre(401, ['success' => false, 'message' => 'Connectez-vous pour envoyer un message.']);
//     }

//     $data = json_decode(file_get_contents('php://input'), true);
//     if (!is_array($data) || !isset($data['content']) || !is_string($data['content'])) {
//         repondre(400, ['success' => false, 'message' => 'Message invalide.']);
//     }

//     $content = trim($data['content']);
//     if ($content === '' || mb_strlen($content) > 5000) {
//         repondre(400, ['success' => false, 'message' => 'Le message doit contenir entre 1 et 5000 caractères.']);
//     }

//     $stmt = $mysqlClient->prepare(
//         'INSERT INTO messages (user_id, content, ip_adress)
//          VALUES (:user_id, :content, :ip_adress)'
//     );
//     $stmt->execute([
//         'user_id' => $_SESSION['user_id'],
//         'content' => $content,
//         'ip_adress' => $_SERVER['REMOTE_ADDR'] ?? '',
//     ]);

//     $messageId = $mysqlClient->lastInsertId();
//     $timeStmt = $mysqlClient->prepare(
//         "SELECT DATE_FORMAT(created_at, '%H:%i')
//          FROM messages
//          WHERE id = :id"
//     );
//     $timeStmt->execute(['id' => $messageId]);

//     repondre(201, [
//         'success' => true,
//         'message' => [
//             'id' => $messageId,
//             'content' => $content,
//             'username' => $_SESSION['username'],
//             'time' => $timeStmt->fetchColumn(),
//         ],
//     ]);
// } catch (PDOException $e) {
//     error_log($e->getMessage());
//     repondre(500, ['success' => false, 'message' => 'Erreur lors de l’accès à la base de données.']);
// }
?>