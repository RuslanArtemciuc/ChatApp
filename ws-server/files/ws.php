<?php
// Script per connessione al database
require_once 'db.php';

$query = "DELETE FROM tokens";
$stmt = $pdo->prepare($query);
$stmt->execute();

// Imposta il fuso orario di Roma
date_default_timezone_set('Europe/Rome');

// Impostazioni di errore e di timeout
error_reporting(E_ALL);
set_time_limit(0);
ob_implicit_flush();

// IP e porta del server WebSocket
$address = '0.0.0.0';
$port = 8080;

// Creazionedel socket
$master = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

// Setup del socket
socket_set_option($master, SOL_SOCKET, SO_REUSEADDR, 1);
socket_bind($master, $address, $port);
socket_listen($master);

// Variabili per lo script
$clients = [$master];
$socketUsers = [];    // socket object ID => user_id
$userSockets = [];    // user_id => socket


// Funzioni per la gestione dei frame WebSocket

/**
 * Legge un frame WebSocket da un socket
 */
function readFrame($sock)
{
    if (socket_recv($sock, $hdr, 2, MSG_WAITALL) !== 2) return false;
    $b1 = ord($hdr[0]);
    $b2 = ord($hdr[1]);
    $len = $b2 & 127;

    if ($len === 126) {
        socket_recv($sock, $ext, 2, MSG_WAITALL);
        $len = unpack('n', $ext)[1];
    } elseif ($len === 127) {
        socket_recv($sock, $ext, 8, MSG_WAITALL);
        $len = unpack('J', $ext)[1];
    }

    socket_recv($sock, $mask, 4, MSG_WAITALL);
    socket_recv($sock, $data, $len, MSG_WAITALL);

    for ($i = 0; $i < $len; ++$i) {
        $data[$i] = $data[$i] ^ $mask[$i % 4];
    }

    return ['data' => $data, 'opcode' => $b1 & 0x0F];
}

/**
 * Invia un frame WebSocket a un socket
 * @param Socket $sock Socket a cui inviare il frame
 * @param string $payload Contenuto del frame
 */
function sendFrame($sock, $payload)
{
    $frame = chr(0x81);
    $len = strlen($payload);

    if ($len <= 125) {
        $frame .= chr($len);
    } elseif ($len <= 65535) {
        $frame .= chr(126) . pack('n', $len);
    } else {
        $frame .= chr(127) . pack('J', $len);
    }

    return @socket_write($sock, $frame . $payload);
}

/**
 * Genera l'header di risposta per il WebSocket
 * @param string $acceptKey Chiave di accettazione
 * @return string Header di risposta
 */
function getHeader($acceptKey): string
{
    return
        "HTTP/1.1 101 Switching Protocols\r\n" .
        "Upgrade: websocket\r\n" .
        "Connection: Upgrade\r\n" .
        "Sec-WebSocket-Accept: $acceptKey\r\n\r\n";
}

/**
 * Verifica il token dell'utente
 * @param PDO $pdo Connessione al database
 * @param int $user_id ID dell'utente
 * @param string $token Token da verificare
 * @return bool True se il token è valido, false altrimenti
 */
function verifyToken($pdo, $user_id, $token)
{
    $stmt = $pdo->prepare("SELECT 1 FROM tokens WHERE user_id = :user_id AND token = :token");
    $stmt->execute(['user_id' => $user_id, 'token' => $token]);
    return $stmt->fetch() !== false;
}

/**
 * Disconnette un client dal server
 * @param Socket $sock Socket del client
 * @param array $clients Array dei socket dei client
 * @param array $socketUsers Array dei socket e degli ID utente
 * @param array $userSockets Array degli ID utente e dei socket
 */
function disconnectClient($sock, &$clients, &$socketUsers, &$userSockets)
{
    $id = spl_object_id($sock);
    echo "Client disconnected: $id\n";

    if (isset($socketUsers[$id])) {
        $user_id = $socketUsers[$id];
        unset($userSockets[$user_id]);
    }

    unset($socketUsers[$id]);

    $idx = array_search($sock, $clients, true);
    if ($idx !== false) unset($clients[$idx]);

    socket_close($sock);
}



// Se lo script non è stato terminato con errori, mostro un messaggio di avvio
echo "Server WebSocket partito sulla porta: $port\n";


// Loop principale del server
while (true) {
    $read = $clients;
    $null = null;

    // No timeout on select: 0 = block until input
    socket_select($read, $null, $null, null);

    foreach ($read as $sock) {
        if ($sock === $master) {
            $client = socket_accept($master);
            $handshake = socket_read($client, 1024);

            if (preg_match('#Sec-WebSocket-Key: (.*)\r\n#', $handshake, $matches)) {
                $key = trim($matches[1]);
                $acceptKey = base64_encode(sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
                $headers = getHeader($acceptKey);
                socket_write($client, $headers);
                $clients[] = $client;
            }
        } else {
            $frame = readFrame($sock);
            $sockId = spl_object_id($sock);

            if ($frame === false || $frame['opcode'] === 0x8) {
                disconnectClient($sock, $clients, $socketUsers, $userSockets);
                continue;
            }

            $msg = json_decode($frame['data'], true);
            if (!$msg) continue;

            echo "Received message: " . json_encode($msg) . "\n";

            if (!isset($socketUsers[$sockId])) {
                if ($msg['type'] === 'auth' && isset($msg['user_id'], $msg['token'])) {
                    if (verifyToken($pdo, $msg['user_id'], $msg['token'])) {
                        $socketUsers[$sockId] = $msg['user_id'];
                        $userSockets[$msg['user_id']] = $sock;

                        echo "User {$msg['user_id']} authenticated\n";

                        sendFrame($sock, json_encode([
                            'type' => 'auth',
                            'status' => 'success',
                            'message' => 'Autorizzato!'
                        ]));
                    } else {
                        
                        sendFrame($sock, json_encode([
                            'type' => 'auth',
                            'status' => 'error',
                            'message' => 'Token invalido.'
                        ]));

                        disconnectClient($sock, $clients, $socketUsers, $userSockets);
                    }
                }
            } else {
                $fromUser = $socketUsers[$sockId];

                if ($msg['type'] === 'send_message' && isset($msg['chat_id'], $msg['message'], $msg['receiver_id'], $msg['sender_id'])) {
                    if ($msg['receiver_id'] == $fromUser) {
                        sendFrame($sock, json_encode([
                            'type' => 'error',
                            'message' => 'Non puoi inviare un messaggio a te stesso'
                        ]));
                        continue;
                    }

                    $stmt = $pdo->prepare("INSERT INTO messages (chat_id, sender_id, content) VALUES (:chat_id, :sender_id, :content)");
                    try {
                        if (!$stmt->execute([
                            'chat_id' => $msg['chat_id'],
                            'sender_id' => $msg['sender_id'],
                            'content' => $msg['message']
                        ])) {
                            throw new Exception($stmt->errorInfo()[2]);
                        }
                    } catch (Exception $e) {
                        echo "Error: " . $e->getMessage() . "\n";
                        sendFrame($sock, json_encode([
                            'type' => 'error',
                            'message' => 'Errore durante l\'invio del messaggio'
                        ]));
                        continue;
                    }

                    $otherUserSock = $userSockets[$msg['receiver_id']] ?? null;
                    if ($otherUserSock && @socket_write($otherUserSock, '') !== false) {
                        sendFrame($otherUserSock, json_encode([
                            'type' => 'message',
                            'message_id' => $pdo->lastInsertId(),
                            'sent_at' => date('Y-m-d H:i:s'),
                            'from' => $fromUser,
                            'chat_id' => $msg['chat_id'],
                            'message' => $msg['message']
                        ]));
                    }
                }

                if ($msg['type'] === 'add_chat' && isset($msg['other_user_id'])) {
                    if ($msg['other_user_id'] == $socketUsers[$sockId]) {
                        sendFrame($sock, json_encode([
                            'type' => 'error',
                            'message' => 'Non puoi creare una chat con te stesso'
                        ]));
                        continue;
                    }
                    $stmt = $pdo->prepare("INSERT INTO chats (user1_id, user2_id) VALUES (:user_id_1, :user_id_2)");

                    try {
                        if ($stmt->execute([
                            'user_id_1' => $socketUsers[$sockId],
                            'user_id_2' => $msg['other_user_id']
                        ])) {
                            $chatId = $pdo->lastInsertId();

                            $otherUserSock = $userSockets[$msg['other_user_id']] ?? null;
                            if ($otherUserSock && @socket_write($otherUserSock, '') !== false) {
                                sendFrame($otherUserSock, json_encode([
                                    'type' => 'chat_added',
                                    'from' => $socketUsers[$sockId],
                                    'message' => 'Chat added'
                                ]));
                            }

                            sendFrame($sock, json_encode([
                                'type' => 'chat_added',
                                'from' => $socketUsers[$sockId],
                                'message' => 'Chat added'
                            ]));
                        } else {
                            throw new Exception($stmt->errorInfo()[2]);
                        }
                    } catch (Exception $e) {
                        echo "Error: " . $e->getMessage() . "\n";
                        sendFrame($sock, json_encode([
                            'type' => 'error',
                            'message' => 'Errore nella creazione della chat'
                        ]));
                        continue;
                    }
                }
            }
        }
    }
}
