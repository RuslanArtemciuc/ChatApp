# ChatApp

A lightweight, database‑backed, two-user private chat application with real-time messaging, built using PHP, MySQL, WebSockets, and Docker.

> **Disclaimer:** This academic prototype is not production‑ready. Messages are stored in plaintext in MySQL; only passwords are hashed via PHP’s `password_hash`.

## Table of Contents

- [ChatApp](#chatapp)
  - [Table of Contents](#table-of-contents)
  - [Prerequisites](#prerequisites)
  - [Setup \& Run](#setup--run)
  - [Project Structure](#project-structure)
  - [Database Schema](#database-schema)
  - [REST API Endpoints](#rest-api-endpoints)
  - [Frontend](#frontend)
  - [WebSocket Server](#websocket-server)
  - [Stopping \& Teardown](#stopping--teardown)
  - [License](#license)

---

## Prerequisites

* Docker (Engine or Desktop)
* Docker Compose
* Familiarity with PHP & MySQL

---

## Setup & Run

1. **Clone repository**

   ```bash
   git clone https://github.com/your-username/ChatApp.git
   cd ChatApp/ChatApp - Applicazione Finita
   ```
2. **Build and launch**

   ```bash
   docker-compose up --build
   ```
3. **Access the services**

   * **Web UI (static pages)**: `http://localhost` (port 80)
   * **WebSocket endpoint**: `ws://localhost:8080`
   * **MySQL**: `localhost:3306` (credentials defined in `db/init.sql`)

---

## Project Structure

```
ChatApp - Applicazione Finita/
├── docker-compose.yml           # defines db, web, ws-server services
├── db/
│   ├── Dockerfile              # MySQL 8.0 setup, timezone Europe/Rome
│   └── init.sql                # schema: users, chats, messages, tokens
├── web/
│   ├── Dockerfile              # PHP 8.2 + Apache configuration
│   ├── apache.conf             # vhost & error pages
│   └── files/
│       ├── index.php           # SPA entry point
│       ├── login.php           # login UI
│       ├── register.php        # register UI
│       ├── logout.php          # logout handler
│       ├── errors/             # HTTP error pages
│       │   ├── 403.html
│       │   └── 404.html
│       └── backend/            # REST API handlers
│           ├── db.php
│           ├── e_register.php
│           ├── e_login.php
│           ├── check_user_existence.php
│           └── get_chats.php
└── ws-server/
    ├── Dockerfile              # PHP 8.1 CLI + sockets extension
    └── files/
        ├── db.php              # PDO MySQL connection
        └── ws.php              # WebSocket server logic
```

* **db/**: Initializes `chat_app` database in MySQL container.
* **web/**: Serves REST API and frontend via Apache+PHP.
* **ws-server/**: Handles WebSocket connections for real-time messaging.

---

## Database Schema

**Database:** `chat_app`

| Table      | Columns                                                                   | Notes                                    |
| ---------- | ------------------------------------------------------------------------- | ---------------------------------------- |
| `users`    | `id` PK, `username` UNIQUE, `password`                                    | Password hashed via `password_hash`      |
| `chats`    | `id` PK, `user1_id` FK, `user2_id` FK,`participant_pair` GENERATED UNIQUE | `participant_pair` enforces unique pairs |
| `messages` | `id` PK, `chat_id` FK, `sender_id` FK, `content`, `sent_at` TIMESTAMP     |                                          |
| `tokens`   | `id` PK, `user_id` FK, `token`                                            | WS authentication token                  |

---

## REST API Endpoints

All under `web/files/backend/`:

* **POST** `/e_register.php`
  Request: `{ username, password }` → Creates new user.

* **POST** `/e_login.php`
  Request: `{ username, password }` → Starts PHP session, issues WS token.

* **GET** `/check_user_existence.php?username=<name>`
  Response: `{ status: success|error }`.

* **GET** `/get_chats.php`
  Response: JSON array of chats for user. The `user_id` is taken from the $_SESSION

The other `PHP` pages perform calls to these endpoints.

---

## Frontend

Located in `web/files`:

* `register.php` and `login.php` handle user onboarding.
* `index.php` provides the live chat interface, displays all users' chats and manages the connection to the WebSocket server.
* Static assets (`favicon.ico`, `default-avatar.jpg`, imgs) are embedded or referenced.

The frontend uses JavaScript in `index.php` to:

1. Call REST API for login/registration and fetching chats.
2. Establish a WebSocket connection:

   ```js
   let ws = new WebSocket('ws://' + location.hostname + ':8082');
   ```
3. Send autentication request:

  ```js
  // Instruction for autentication
        ws.onopen = () => {
            // Trying autentication on opened connection
            setTimeout(() => {
                ws.send(JSON.stringify({
                    type: 'auth',
                    user_id: user_id,
                    token: token
                }));
            }, 100);
            // Some other things
        };
  ```
   
4. Send/receive messages in JSON frames.

---

## WebSocket Server

In `ws-server/files/ws.php`:

1. **Setup:** Binds TCP socket on `0.0.0.0:8080`.
2. **Handshake:** Validates `user_id` and `token` against `tokens` table.
3. **Loop:** Uses `stream_select()` to multiplex and broadcast messages to both chat participants.
4. **Frame Handling:** RFC6455 helpers `readFrame()`/`sendFrame()`.

---

## Stopping & Teardown

```bash
docker-compose down -v
```

This stops all containers and removes associated volumes.

---

## License

Released under the [MIT License](LICENSE). Feel free to fork, modify, and learn from this project.
