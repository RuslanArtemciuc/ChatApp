<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accedi</title>
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

        .login-container {
            background-color: #1e1e1e;
            padding: 20px 30px;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 350px;
        }

        .login-container h1 {
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

        .login-btn {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 4px;
            background-color: #6200ea;
            color: #ffffff;
            font-size: 16px;
            cursor: pointer;
        }

        .login-btn:hover {
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
</head>

<body>
    <div class="login-container">
        <h1>Benvenuto!</h1>
        <form action="backend/e_login.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <?php if (isset($_GET['error'])) : ?>
                <div class="error">
                    <?php
                    echo $_GET['error'];
                    ?>
                </div>
            <?php endif; ?>

            <button type="submit" class="login-btn">Accedi</button>
        </form>
        <div class="footer">
            <p>Non hai un account? <a href="register.php">Registrati</a></p>
        </div>
    </div>
</body>

</html>