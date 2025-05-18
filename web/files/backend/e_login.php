<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /errors/403.html');
    exit();
}

session_start();

require_once 'db.php';

if (isset($_SESSION['user'])) {
    header('Location: /');
    exit();
}


if (!isset($_POST['username'])) {
    header('Location: /login.php?error=Username non ricevuto');
    exit();
}


if (!isset($_POST['password'])) {
    header('Location: /login.php?error=Password non ricevuta');
    exit();
}


$stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
$stmt->bindParam(':username', $_POST['username']);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) {
    header('Location: /login.php?error=Questo utente non esiste');
    exit();
}

if (!password_verify($_POST['password'], $user['password'])) {
    header('Location: /login.php?error=Password errata');
    exit();
}

$token = bin2hex(random_bytes(16));

$stmt = $pdo->prepare("SELECT * FROM tokens WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $user['id']);
$stmt->execute();
$tokenExists = $stmt->fetch(PDO::FETCH_ASSOC);
if ($tokenExists) {
    $stmt = $pdo->prepare("UPDATE tokens SET token = :token WHERE user_id = :user_id");
    $stmt->bindParam(':token', $token);
    $stmt->bindParam(':user_id', $user['id']);
    $stmt->execute();
} else {
    $stmt = $pdo->prepare("INSERT INTO tokens (user_id, token) VALUES (:user_id, :token)");
    $stmt->bindParam(':user_id', $user['id']);
    $stmt->bindParam(':token', $token);
    $stmt->execute();
}

$_SESSION['user'] =
    [
        'id' => $user['id'],
        'username' => $user['username'],
        'token' => $token
    ];

header('Location: /');
exit();
