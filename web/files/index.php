<?php
date_default_timezone_set('Europe/Rome');
//if session is not started, start it
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

//if user is logged in, get his id
if (isset($_SESSION['user'])) {
    $user_id = $_SESSION['user']['id'];
    $user_name = $_SESSION['user']['username'];
    $token = $_SESSION['user']['token'];
} else {
    //if user is not logged in, redirect to login page
    header('Location: login.php');
    exit();
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChatApp</title>

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #121212;
            color: #ffffff;
            height: 100vh;
            font-size: 13px;
        }

        .sidebar-container {
            width: 25%;
            height: 100%;
            float: left;
            padding: 10px;
            box-sizing: border-box;
        }

        .main-container {
            width: 75%;
            height: 100%;
            float: left;
            padding: 10px;
            box-sizing: border-box;
        }

        .sidebar,
        .main {
            height: 100%;
            width: 100%;
            border-radius: 15px;
            background-color: #2a2a2a;
            border: 1px solid #333333;
            position: relative;
        }

        .def-message {
            text-align: center;
            position: absolute;
            top: 45%;
            left: 50%;
            transform: translate(-50%, 50%);
            color: #777777;
        }

        .header {
            padding: 20px 0;
            height: 50px;
        }

        .header * {
            margin: 0;
            padding: 10px;
            text-align: center;
        }

        .header h5 {
            margin: -10px 0 10px
        }

        .chat-preview {
            border-top: 1px solid #393939;
            border-bottom: 1px solid #393939;
            padding: 0 10px
        }

        .chat-preview:hover {
            background-color: #393939;
            cursor: pointer;
        }

        .chat-preview .pfp {
            aspect-ratio: 1/1;
            height: 50px;
            margin-top: 10px;
            border-radius: 50%;
            display: inline-block
        }

        .pfp img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
        }

        .chat-preview .content {
            display: inline-block;
            vertical-align: top;
            margin-left: 10px;
        }

        .chat-preview .content p {
            margin-top: -10px;
            font-size: 1em;
        }

        .chat-carousell {
            height: calc(100% - 110px);
            overflow-y: auto;
            padding: 10px 0;
        }

        .add-chat-button:hover,
        .logout-button:hover {
            background-color: #4a4a4a;
        }

        .add-chat-button {
            position: absolute;
            font-size: 25px;
            bottom: 10px;
            right: 10px;
            cursor: pointer;
            border-radius: 50%;
            aspect-ratio: 1/1;
            height: 30px;
            width: 30px;
            text-align: center;
            background-color: #393939;

            -webkit-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }


        .logout-button {
            position: absolute;
            font-size: 25px;
            bottom: 10px;
            left: 10px;
            cursor: pointer;
            border-radius: 50%;
            aspect-ratio: 1/1;
            height: 30px;
            width: 30px;
            text-align: center;
            background-color: #393939;

            -webkit-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }

        .chat-preview.selected {
            background-color: #505050;
        }

        .hidden {
            display: none;
        }

        .chat-input {
            position: absolute;
            display: flex;
            justify-content: center;
            border-radius: 15px;

            bottom: 0;
            left: 0;
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
            background-color: #2a2a2a;
        }

        .chat-input textarea {
            width: 95%;
            padding: 3px;
            border-radius: 5px;
            border: 1px solid #333333;
            background-color: #393939;
            color: #ffffff;

            resize: vertical;

            min-height: 40px;
            overflow-y: auto;
            word-wrap: break-word
        }

        .chat-header {
            display: flex;
            align-items: center;
            padding: 5px 30px 10px;
            border-bottom: 1px solid #393939;
        }

        .chat-header .username {
            font-size: 1em;
            margin-left: 10px;
            color: #ffffff;
            font-weight: bold;
            width: 100%;
            box-sizing: border-box;
            margin-top: 10px;
        }

        .chat-header .pfp {
            aspect-ratio: 1/1;
            height: 30px;
            margin-top: 10px;
            border-radius: 50%;
            display: inline-block
        }

        .chat-messages {
            height: calc(100vh - 140px);
            overflow-y: auto;
            box-sizing: border-box;
        }

        .msg-self,
        .msg-other {
            display: flex;
            margin: 20px 0;
            padding: 5px;
            border-radius: 10px;
            max-width: 70%;
            min-width: 20%;
            width: fit-content;
            position: relative;
        }

        .msg-self {
            justify-content: flex-end;
            background-color: #4a4a4a;
            margin-left: auto;
            margin-right: 10px;
        }

        .msg-other {
            justify-content: flex-start;
            background-color: #393939;
            margin-right: auto;
            margin-left: 10px;
        }

        .msg-content {
            padding: 10px;
            color: #ffffff;
            font-size: 1em;
            word-wrap: break-word;
            overflow-wrap: break-word;
            word-break: break-all;
        }

        .msg-time {
            font-size: 0.8em;
            color: #777777;
            position: absolute;
            bottom: -15px;
            right: 10px;
        }
    </style>
</head>

<body>

    <div class="sidebar-container">

        <div class="sidebar">

            <div class="header">
                <h1>ChatApp</h1>
                <h5><?php echo $user_name; ?></h5>
            </div>

            <div class="chat-carousell">


            </div>

            <div class="logout-button" onclick="logout()">
                <svg fill="none" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M17 16L21 12M21 12L17 8M21 12L7 12M13 16V17C13 18.6569 11.6569 20 10 20H6C4.34315 20 3 18.6569 3 17V7C3 5.34315 4.34315 4 6 4H10C11.6569 4 13 5.34315 13 7V8" stroke="white" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                </svg>
            </div>

            <div class="add-chat-button" onclick="addChat()">
                +
            </div>
        </div>

    </div>

    <div class="main-container">

        <div class="main">

            <div class="chat hidden">
                <div class="chat-header">
                    <div class="pfp"><img src="img/default-avatar.jpg"></div>
                    <div class="username">User</div>
                </div>

                <div class="chat-messages">
                </div>

                <div class="chat-input">
                    <textarea placeholder="Scrivi un messaggio..."
                        onkeydown="textareaResize(this)" onkeyup="textareaResize(this)"></textarea>
                </div>
            </div>

            <p class="def-message">Select a chat.</p>

        </div>

    </div>

    <script>
        // ********** Variabili globali ********** //
        // Url ricavato da nome della pagina con modifica della porta
        const serverUrl = window.location.origin.replace(/:\d+$/, ':8081');

        // Prendo i riferimenti dei principali elementi della pagina
        const chatInput = document.querySelector('.chat-input textarea');
        const sideBar = document.querySelector('.sidebar');
        const chatMessagesContainer = document.querySelector('.chat-messages');

        // Definisco le variabili dell'utente
        const user_id = <?php echo json_encode($user_id); ?>;
        const user_name = <?php echo json_encode($user_name); ?>;
        const token = <?php echo json_encode($token); ?>;
        let chats = [];

        // ********** Funzioni per i pulsanti ********** //
        // Pulsante per aggiungere una chat
        function addChat() {
            // Chiedo all'utente di inserire il nome di un altro utente per creare una chat
            const otherUser = prompt('Con chi vuoi chattare?', 'Inserisci il nome utente');
            if (otherUser) {
                // Controllo se l'utente esiste
                fetch(serverUrl + `/backend/check_user_existence.php?user_name=${encodeURIComponent(otherUser)}`)
                    .then(response => response.json())
                    .then(data => {
                        // Se l'utente esiste, invio il comando di creazion della chat al WebSocket serveer
                        if (data.status === 'success') {
                            ws.send(JSON.stringify({
                                type: 'add_chat',
                                user_id: user_id,
                                other_user_id: data.message
                            }));
                        } else {
                            alert(data.message);
                        }
                    })
                    // Se c'è un errore nella richiesta, avviso l'utente
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while checking the user.');
                    });
            }
        }

        // Pulsante per il logout
        function logout() {
            window.location.href = 'logout.php';
        }


        // Funzione chiamata al click di una chat per la visualizzazione di questa a schermo
        function selectChat(chat) {

            const chatElements = document.querySelectorAll('.chat-preview');
            const chatMessages = document.querySelector('.chat');

            // Rimuovo l'attributo selected da ogni anteprima delle caht
            chatElements.forEach(c => c.classList.remove('selected'));

            // Aggiungo selected alla chat cliccata
            chat.classList.add('selected');
            chatMessages.classList.remove('hidden');
            document.querySelector('.def-message').classList.add('hidden');

            const chatId = chat.getAttribute('chat-id');

            // Avendo l'id della chat selezionata provo a cercarne i dati nell'array locale
            const chatData = chats.find(c => c.id === chatId);

            // Se non la trovo non faccio nulla
            if (!chatData) {
                return;
            }

            // Carico i dati nella chat a schermo
            const otherUserName = document.querySelector('.chat-header .username');
            const otherPfp = document.querySelector('.chat-header .pfp img');
            const chatMessagesContainer = document.querySelector('.chat-messages');

            otherUserName.innerText = chatData.other_user.username;
            otherPfp.src = 'img/default-avatar.jpg';

            chatMessagesContainer.innerHTML = '';

            // Genero ogni messaggio della chat
            chatData.messages.forEach(msg => {
                const msgDiv = document.createElement('div');
                msgDiv.classList.add(msg.sender_id == user_id ? 'msg-self' : 'msg-other');
                const msgContent = document.createElement('div');
                msgContent.classList.add('msg-content');
                msgContent.innerText = msg.content;

                const msgTime = document.createElement('div');
                msgTime.classList.add('msg-time');
                const timestamp = new Date(msg.sent_at.replace(' ', 'T')); // Convert to ISO format

                const day = timestamp.getDate();
                const month = timestamp.toLocaleString('default', {
                    month: 'short'
                });
                const hours = timestamp.getHours().toString().padStart(2, '0');
                const minutes = timestamp.getMinutes().toString().padStart(2, '0');

                const suffix = (day % 10 === 1 && day !== 11) ? 'st' :
                    (day % 10 === 2 && day !== 12) ? 'nd' :
                    (day % 10 === 3 && day !== 13) ? 'rd' : 'th';

                msgTime.innerText = `${month} ${day}${suffix}, ${hours}:${minutes}`;


                msgDiv.appendChild(msgContent);
                msgDiv.appendChild(msgTime);
                chatMessagesContainer.appendChild(msgDiv);
            });

            setTimeout(() => {
                chatMessagesContainer.scrollTop = chatMessagesContainer.scrollHeight;
            }, 0);
            // Focus sull'input per consentire senza ulteriori interazioni lo scrivere un messaggio
            setTimeout(() => chatInput.focus(), 10);
        }
        
        function textareaResize(el) {
            el.style.height = 'auto';
            const newHeight = Math.min(el.scrollHeight, 150);
            el.style.height = Math.max(30, newHeight) + 'px';
        }

        // ********** Event Listeners ********** //
        // Al resize della pagina cambio la larghezza dell'ultimo messaggio nell'anteprima delle chat
        window.addEventListener('resize', () => {
            refreshChat();
            
            let inputHeight = document.querySelector('.chat-input').clientHeight;
            let chatHeaderHeight = document.querySelector(".chat-header").clientHeight;
            let mainContainerHeight = document.querySelector(".main-container").clientHeight;

            chatMessagesContainer.setAttribute('style', 'height: calc(' + mainContainerHeight + 'px - ' + inputHeight + 'px - ' + chatHeaderHeight + 'px - 30px)');
            chatMessagesContainer.scrollTop = chatMessagesContainer.scrollHeight;
        });

        const resizeObserver = new ResizeObserver(() => {
            let inputHeight = document.querySelector('.chat-input').clientHeight;
            let chatHeaderHeight = document.querySelector(".chat-header").clientHeight;
            let mainContainerHeight = document.querySelector(".main-container").clientHeight;

            chatMessagesContainer.setAttribute('style', 'height: calc(' + mainContainerHeight + 'px - ' + inputHeight + 'px - ' + chatHeaderHeight + 'px - 30px)');
            chatMessagesContainer.scrollTop = chatMessagesContainer.scrollHeight;
        });

        resizeObserver.observe(document.querySelector('.chat-input textarea'));


        // Se durante la scrittura nell'input del messaggio viene premuto il tasto invio, invio il messaggio
        chatInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {

                // Fermo l'evento per prevenire uno spam nel caso di click continuo
                e.preventDefault();

                // Invio il messaggio al WS server
                sendMessage();

                // Faccio focus sull'input per continuare la scrittura
                chatInput.focus();
            }
        });


        // ********** Websocket ********** //
        // Creo la connessione Websocket
        let ws = new WebSocket('ws://' + location.hostname + ':8082');

        // Istruzioni da eseguire alla connessione
        ws.onopen = () => {
            // Provo ad autentificarmi dopo pochi istanti dal caricamento della pagina
            setTimeout(() => {
                ws.send(JSON.stringify({
                    type: 'auth',
                    user_id: user_id,
                    token: token
                }));
            }, 100);
            
            // Richiedo la lista delle chat
            callGetChats();

            // Genero le anteprime delle chat
            refreshChat();
        };

        // Istruzioni da eseguire alla ricezione di un messaggio
        ws.onmessage = (event) => {
            const msg = JSON.parse(event.data);

            // console.log("Messaggio ricevuto: ", msg);

            // Gestisco il messaggio per tipo
            switch (msg.type) {
                // Controllo se l'autenticazione è avvenuta con successo
                case 'auth':
                    if (msg.status === 'success') {
                        console.log('Authenticated successfully');
                    } else {
                        // Se l'autenticazione fallisce, avviso l'utente e lo reindirizzo al logout
                        console.error('Authentication failed:', msg.message);
                        alert(msg.message);
                        window.location.href = 'logout.php';
                    }
                    break;
                    // Se una chat è stata creata, aggiorno la lista dell'anteprima delle chat
                case 'chat_added':
                    callGetChats();
                    refreshChat();
                    break;
                    // Se un messaggio è stato ricevuto, lo gestisco con la funzione di ricezione
                case 'message':
                    receiveMessage(msg);
                    break;
                case 'error':
                    alert(msg.message);
                    break;
                default:
                    console.warn('Tipo di messaggio sconosciuto:', msg.type);
            }

        };
        // Errori della connessione WebSocket
        ws.onerror = e => console.log('Error:', e);

        // Alla chiusura della connessione Websocket, ritento la connessione
        ws.onclose = () => {
            console.log('Retrying connection...');
            ws = new WebSocket('ws://' + location.hostname + ':8082');
        };

        // ********** Funzioni per la gestione dei messaggi ********** //
        function sendMessage() {
            // Prelevo il messaggio dall'input e lo pulisco
            const message = chatInput.value.trim();

            // Svuoto il contenuto dell'input
            chatInput.value = '';

            // Se il messaggio è vuoto non faccio nulla
            if (!message) return;

            // Seleziono l'id guardando qual è la chat selezionata
            const chatId = document.querySelector('.chat-preview.selected')?.getAttribute('chat-id');

            // Se non c'è una chat selezionata non faccio nulla
            if (!chatId) return;

            let chat = chats.find(c => c.id === chatId);

            if (!chat) {
                return;
            }

            // Seleziono l'id dell'utente a cui si sta inviando il messaggio
            const receiver_id = chat.other_user.id;

            // Inserico nell'array locale delle chat il messaggio
            chat.messages.push({
                chat_id: chatId,
                sender_id: user_id,
                content: message,
                sent_at: new Date().toLocaleString('sv-SE', {
                    timeZone: 'Europe/Rome'
                }).replace('T', ' '),
                username: chat.other_user.username
            });
            // Invio il messaggio all'altro utente tramite server WebSocket
            ws.send(JSON.stringify({
                type: 'send_message',
                chat_id: chatId,
                sender_id: user_id,
                receiver_id: receiver_id,
                message: message
            }));

            // Aggiungo il messaggio a schermo
            messaggioAVideo({
                type: 'send_message',
                chat_id: chatId,
                sender_id: user_id,
                receiver_id: receiver_id,
                message: message,
                sent_at: new Date().toLocaleString('sv-SE', {
                    timeZone: 'Europe/Rome'
                }).replace('T', ' ')
            }, true);
        }

        // Funzione per ricevere un messaggio
        function receiveMessage(msg) {

            // Prelevo i dati del messaggio
            const chatId = msg.chat_id;
            const messageContent = msg.message;

            // Cerco la chat nell'array locale selezionando l'id della chat nel messaggio ricevuto
            let chat = chats.find(c => c.id === chatId);

            // Se non trovo la chat non faccio nulla
            if (!chat) {
                return;
            }

            // Inserisco il messaggio nell'array locale
            chat.messages.push({
                chat_id: chatId,
                id: msg.message_id,
                sender_id: msg.from,
                content: messageContent,
                sent_at: msg.sent_at,
                username: chat.other_user.username
            });

            // Seleziono la chat aperta a schermo
            const selectedChat = document.querySelector('.chat-preview.selected');


            // Faccio l'update dell'anteprima delle chat per mostrare l'ultimo messaggio
            refreshChat();

            // Se nessuna chat è selezionata o se la chat selezionata non è quella nella quale il messaggio
            // deve arrivare non faccio nulla
            if (!selectedChat || !selectedChat.getAttribute('chat-id') === chatId) {
                return;
            }

            // Se la chat aperta a schermo e quella interessata nel ricevere il messaggio, inserisco il messaggio a schermo
            messaggioAVideo({
                type: 'send_message',
                chat_id: chatId,
                sender_id: msg.from,
                receiver_id: chat.other_user.id,
                message: messageContent,
                sent_at: msg.sent_at
            }, false);

        }


        /**
         * Funzione per mostrare il messaggio a video
         * @param msg Il messaggio da mostrare
         * @param isSelf Specifica se il messaggio è inviato o ricevuto
         */
        function messaggioAVideo(msg, isSelf) {
            // Creo gli elementi html per la visualizzazione del messaggio
            const msgDiv = document.createElement('div');
            msgDiv.classList.add(isSelf ? 'msg-self' : 'msg-other');

            const msgContent = document.createElement('div');
            msgContent.classList.add('msg-content');
            msgContent.innerText = msg.message;

            const msgTime = document.createElement('div');
            msgTime.classList.add('msg-time');

            // Preparo la data da visualizzare
            const timestamp = new Date(msg.sent_at.replace(' ', 'T'));
            const day = timestamp.getDate();
            const month = timestamp.toLocaleString('default', {
                month: 'short'
            });
            const hours = timestamp.getHours().toString().padStart(2, '0');
            const minutes = timestamp.getMinutes().toString().padStart(2, '0');

            const suffix = (day % 10 === 1 && day !== 11) ? 'st' :
                (day % 10 === 2 && day !== 12) ? 'nd' :
                (day % 10 === 3 && day !== 13) ? 'rd' : 'th';

            // Data e ora pronti e inseriti nel div
            msgTime.innerText = `${month} ${day}${suffix}, ${hours}:${minutes}`;

            msgDiv.appendChild(msgContent);
            msgDiv.appendChild(msgTime);
            chatMessagesContainer.appendChild(msgDiv);

            // Fix trovato online per andare in fondo alla chat dopo l'aggiunta di un nuovo elemento
            // Senza timeout quando il documento HTML non ha ancora registrato la creazione del nuovo messaggio a schermo
            // non riesce a restituire allo script l'altezza giusta del carosello delle chat. Quindi ho messo un timeout
            // anche se minimo che risolve il problema.
            setTimeout(() => {
                chatMessagesContainer.scrollTop = chatMessagesContainer.scrollHeight;
            }, 0);

            refreshChat();
        }

        function callGetChats() {
            // Svuoto l'array locale delle chat
            chats = [];

            // Richiedo allo script get_chats.php le chat. La $_SESSION php specifica l'user id
            fetch(serverUrl + '/backend/get_chats.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'error') {
                        alert(data.message);
                    } else if (data.chats !== "") {
                        // Se sono presenti chat le agigungo all'array locale
                        data.chats.forEach(chat => {
                            chats.push(chat);
                        });
                        refreshChat();
                    }
                }).catch(error => {
                    console.error('Error:', error);
                    alert('Si è verificato un errore nella richiesta delle tue chat al server Web.');
                });
        }


        function refreshChat() {
            // Ricordo qual è la chat selezionata
            const selectedChat = document.querySelector('.chat-preview.selected');
            const selectedChatId = selectedChat ? selectedChat.getAttribute('chat-id') : null;

            const chatCarousell = document.querySelector('.chat-carousell');
            chatCarousell.innerHTML = ''; // Clear existing chats

            // Genero le chat
            chats.forEach(chat => {
                const chatPreview = document.createElement('div');
                chatPreview.classList.add('chat-preview');
                chatPreview.setAttribute('chat-id', chat.id);

                if (selectedChatId && chat.id === selectedChatId) {
                    chatPreview.classList.add('selected');
                }

                chatPreview.onclick = () => selectChat(chatPreview);

                const pfp = document.createElement('div');
                pfp.classList.add('pfp');
                const img = document.createElement('img');
                img.src = 'img/default-avatar.jpg';
                pfp.appendChild(img);

                const content = document.createElement('div');
                content.classList.add('content');

                const h4 = document.createElement('h4');
                h4.innerText = chat.other_user.username;
                const p = document.createElement('p');
                let pContent = "";

                if (chat.messages.length >= 1) {
                    let lastMsgObj = chat.messages[chat.messages.length - 1];
                    let lastMsg = lastMsgObj.sender_id == user_id ?
                        `Tu: ${lastMsgObj.content}` :
                        lastMsgObj.content;

                    const newlineIndex = lastMsg.indexOf('\n');
                    if (newlineIndex !== -1) {
                        lastMsg = lastMsg.substring(0, newlineIndex);
                    }

                    pContent = lastMsg;
                } else {
                    pContent = "Ancora nessun messaggio.";
                }

                const sidebarWidth = document.querySelector('.sidebar-container').offsetWidth;
                const maxTextWidth = sidebarWidth - 105;
                const approxCharWidth = 8; 
                const maxChars = Math.floor(maxTextWidth / approxCharWidth);

                p.innerText = pContent.length > maxChars ? pContent.substring(0, maxChars - 3) + '...' : pContent;

                content.appendChild(h4);
                content.appendChild(p);
                chatPreview.appendChild(pfp);
                chatPreview.appendChild(content);
                chatCarousell.appendChild(chatPreview);
            });
        }
    </script>

</body>

</html>