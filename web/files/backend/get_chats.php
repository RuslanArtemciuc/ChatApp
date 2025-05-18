<?php

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Richiesta non valida! Solo metodo GET supportato.'
    ]);
    exit;
}


session_start();

if ($_SESSION['user'] == null) {
    header('Location: /errors/403.html');
    exit();
}

include_once "db.php";

$user_id = $_SESSION['user']['id'];

// get user's chats
$stmt = $pdo->prepare("SELECT * FROM chats WHERE user1_id = :user_id OR user2_id = :user_id");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$chats = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (!$chats) {
    echo json_encode([
        'status' => 'success',
        'chats' => ''
    ]);
    exit();
}

$chats = array_map(function ($chat) use ($pdo) {
    if ($chat['user1_id'] == $_SESSION['user']['id']) {
        $other_user_id = $chat['user2_id'];
    } else {
        $other_user_id = $chat['user1_id'];
    }
    // get other user info
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $other_user_id);
    $stmt->execute();
    $other_user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$other_user) {
        return null;
    }

    // delete from array user1_id and user2_id and keep object other_user
    unset($chat['user1_id']);
    unset($chat['user2_id']);

    $stmt = $pdo->prepare("SELECT messages.*, username FROM messages JOIN users ON sender_id=users.id WHERE chat_id = :chat_id ORDER BY sent_at");
    $stmt->bindParam(':chat_id', $chat['id']);
    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return [
        'id' => $chat['id'],
        'other_user' => $other_user,

        'messages' => $messages
    ];
}, $chats);

// echo json response with chats and messages per chat
echo json_encode([
    'status' => 'success',
    'chats' => $chats
]);
