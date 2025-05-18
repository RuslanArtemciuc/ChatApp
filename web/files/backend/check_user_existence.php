<?php

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Richiesta non valida! Solo metodo GET supportato.'
    ]);
    exit;
}

include_once "db.php";

$user_name = isset($_GET['user_name']) ? $_GET['user_name'] : null;

if ($user_name === null) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Richiesta non valida! Variabile user_name non presente.'
    ]);
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
$stmt->bindParam(':username', $user_name, PDO::PARAM_STR);
$stmt->execute();

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo json_encode([
        'status' => 'success',
        'message' => $user['id']
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'L\'utente non esiste.'
    ]);
}
