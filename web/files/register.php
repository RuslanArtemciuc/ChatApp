<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #121212;
            color: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .register-container {
            background-color: #1e1e1e;
            padding: 20px 30px;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 350px;
        }

        .register-container h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 24px;
            color: #ffffff;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-size: 13px;
            color: #bbbbbb;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #333333;
            border-radius: 4px;
            background-color: #2a2a2a;
            color: #ffffff;
            font-size: 14px;
        }

        .form-group input:focus {
            outline: none;
            border-color: #6200ea;
        }

        .register-btn {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 4px;
            background-color: #6200ea;
            color: #ffffff;
            font-size: 16px;
            cursor: pointer;
        }

        .register-btn:hover {
            background-color: #3700b3;
        }

        .footer {
            text-align: center;
            margin-top: 15px;
            font-size: 12px;
            color: #888888;
        }

        .footer a {
            color: #6200ea;
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        .error {
            color: #ff5252;
            font-size: 12px;
            margin-top: -10px;
            margin-bottom: 10px;
            min-height: 13px;
        }
    </style>
    <script>
        function validateForm(event) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const errorElement = document.getElementById('error-message');

            if (password !== confirmPassword) {
                errorElement.textContent = "Passwords do not match!";
                event.preventDefault(); // Prevent form submission
            } else {
                errorElement.textContent = ""; // Clear error message
            }
        }
    </script>
</head>

<body>
    <div class="register-container">
        <h1>Crea un account!</h1>
        <form action="backend/e_register.php" method="POST" onsubmit="validateForm(event)">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Conferma Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <div id="error-message" class="error">
                <?php
                if (isset($_GET['error'])) {
                    echo htmlspecialchars($_GET['error']);
                }
                ?>
            </div>
            <div class="form-group">
                <button type="submit" class="register-btn">Registrati</button>
            </div>
        </form>
        <div class="footer">
            <p>Hai già un account? <a href="login.php">Accedi</a></p>
        </div>
    </div>
</body>

</html>