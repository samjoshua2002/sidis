<?php

session_start();

require_once __DIR__ . '/../config.php';

if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

/*
|--------------------------------------------------------------------------
| Login
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    /*
    |--------------------------------------------------------------------------
    | Validate input
    |--------------------------------------------------------------------------
    */
    if ($username === '' || $password === '') {

        $error = 'Please enter your username and password.';

    } else {

        /*
        |--------------------------------------------------------------------------
        | Get user
        |--------------------------------------------------------------------------
        */
        $stmt = $pdo->prepare("
            SELECT id, username, password
            FROM users
            WHERE username = :username
            AND status = 1
            LIMIT 1
        ");

        $stmt->execute([
            'username' => $username
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        /*
        |--------------------------------------------------------------------------
        | Verify password
        |--------------------------------------------------------------------------
        */
        if ($user && password_verify($password, $user['password'])) {

            session_regenerate_id(true);

            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];

            header('Location: index.php');
            exit;

        } else {

            $error = 'Invalid username or password.';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Admin Login - SIDiS</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, Helvetica, sans-serif;

            background: linear-gradient(
                to bottom,
                #1F3C44 0%,
                #184C74 100%
            );

            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }

        .login-box {
            background: #ffffff;
            padding: 40px;
            border-radius: 12px;

            box-shadow:
                0 15px 35px rgba(0, 0, 0, 0.25),
                0 5px 15px rgba(0, 0, 0, 0.15);
        }

        .login-logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-logo h1 {
            margin: 0;
            color: #184C74;
            font-size: 28px;
        }

        .login-logo p {
            margin-top: 8px;
            color: #666;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
        }

        .form-group input {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #d5d5d5;
            border-radius: 6px;
            font-size: 15px;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-group input:focus {
            border-color: #184C74;

            box-shadow:
                0 0 0 3px rgba(24, 76, 116, 0.12);
        }

        .login-button {
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: 6px;

            background: linear-gradient(
                to right,
                #1F3C44,
                #184C74
            );

            color: #fff;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;

            box-shadow:
                0 5px 12px rgba(24, 76, 116, 0.25);

            transition: transform 0.2s, box-shadow 0.2s;
        }

        .login-button:hover {
            transform: translateY(-1px);

            box-shadow:
                0 7px 16px rgba(24, 76, 116, 0.3);
        }

        .error-message {
            margin-bottom: 20px;
            padding: 12px;

            background: #fff0f0;
            border: 1px solid #e2b5b5;
            border-radius: 6px;

            color: #a40000;
            font-size: 14px;
        }

        .login-logo {
    text-align: center;
    margin-bottom: 30px;
}

.login-brand {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
}

.login-brand img {
    width: 65px;
    height: 65px;
    object-fit: contain;
}

.login-brand h1 {
    margin: 0;
    color: #184C74;
    font-size: 28px;
}

.login-logo p {
    margin: 8px 0 0;
    color: #666;
    font-size: 14px;
}
.login-brand img {
    width: 58px;
    height: 58px;
    object-fit: contain;
}

    </style>

</head>

<body>

    <div class="login-container">

        <div class="login-box">

            <div class="login-logo">

    <div class="login-brand">
        <img
            src="../images/iitm-logo.png"
            alt="IITM Logo"
        >

        <h1>
            SIDiS
        </h1>
    </div>

    <p>
        Administration Panel
    </p>

</div>
            <?php if ($error): ?>

                <div class="error-message">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>

            <?php endif; ?>

            <form method="POST" autocomplete="off">

                <div class="form-group">

                    <label for="username">
                        Username
                    </label>

                    <input
                        type="text"
                        id="username"
                        name="username"
                        required
                        autocomplete="username"
                    >

                </div>

                <div class="form-group">

                    <label for="password">
                        Password
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        autocomplete="current-password"
                    >

                </div>

                <button
                    type="submit"
                    class="login-button"
                >
                    Login
                </button>

            </form>

        </div>

    </div>

</body>

</html>