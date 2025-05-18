<?php
session_start();

$user_id = $_SESSION['user']['id'] ?? null;
if (!$user_id) {
    header('Location: /login.php?error=Utente non loggato');
    exit();
}
session_reset();
session_destroy();
header('Location: /');
exit();