<?php

// Non consento l'accesso diretto a questo file
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    header('Location: /errors/403.html');
    exit();
}


try {
    $dsn = 'mysql:host=db;port=3306;dbname=chat_app;charset=utf8mb4';
    $username = 'chatuser';
    $password = 'chatpass';

    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}
