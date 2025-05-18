<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /errors/403.html');
    exit();
}

require_once 'db.php';

if (!isset($_POST['username'])) {
    header('Location: /register.php?error=Username non ricevuto');
    exit();
}

if (!isset($_POST['password'])) {
    header('Location: /register.php?error=Password non ricevuta');
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
$stmt->bindParam(':username', $_POST['username']);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if ($user) {
    header('Location: /register.php?error=Questo utente esiste già');
    exit();
}

if (strlen($_POST['password']) < 4) {
    header('Location: /register.php?error=Password troppo corta. Minimo 4 caratteri');
    exit();
}

$password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
$stmt->bindParam(':username', $_POST['username']);
$stmt->bindParam(':password', $password_hash);
$stmt->execute();

header('Location: /login.php');
exit();