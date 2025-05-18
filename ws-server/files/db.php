<?php

$dsn = 'mysql:host=db;port=3306;dbname=chat_app;charset=utf8mb4';
$username = 'chatuser';
$password = 'chatpass';

$maxRetries = 30; // Tentativi di connessione al db
$retryInterval = 7; // Ogni quando provare una nuova connessione
$connected = false;

for ($i = 0; $i < $maxRetries; $i++) {
    try {
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $connected = true;
        // Se la connessione è riuscita, esco dal ciclo
        break;
    } catch (PDOException $e) {
        if ($i < $maxRetries - 1) {
            sleep($retryInterval);
        } else {
            die('Connessione al database fallita dopo molti tentativi: ' . $e->getMessage());
        }
    }
}


echo "Database connection successful.\n";
