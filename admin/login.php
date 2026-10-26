<?php
session_start();

require_once __DIR__ . '/../config.php';

// If already logged in
if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}
if (isset($_SESSION['faculty_user_id'])) {
    header('Location: faculty-portal/index.php');
    exit;
}

$error = '';
$activeTab = $_POST['login_type'] ?? 'faculty';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $loginType = $_POST['login_type'] ?? 'faculty';

    if ($username === '' || $password === '') {
        $error = 'Please enter both your username and password.';
    } else {
        $stmt = $pdo->prepare("
            SELECT id, username, password, role, faculty_id, status
            FROM users
            WHERE username = :username
            LIMIT 1
        ");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify role strictly matches the selected tab to avoid role or account enumeration
        $roleMatches = false;
        if ($user) {
            if ($loginType === 'faculty' && $user['role'] === 'faculty') {
                $roleMatches = true;
            } elseif ($loginType === 'admin' && $user['role'] === 'admin') {
                $roleMatches = true;
            }
        }

        if ($user && $roleMatches && (int)$user['status'] === 1 && password_verify($password, $user['password'])) {
            session_regenerate_id(true);

            if ($user['role'] === 'faculty') {
                $_SESSION['faculty_user_id'] = $user['id'];
                $_SESSION['faculty_username'] = $user['username'];
                $_SESSION['faculty_id'] = $user['faculty_id'];

                header('Location: faculty-portal/index.php');
                exit;
            } else {
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];

                header('Location: index.php');
                exit;
            }
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIDiS Portal Login</title>
    <link rel="icon" type="image/png" href="../images/iitm-logo.png">
    <link href="../fonts/lato.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            font-family: 'Lato', sans-serif;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-wrapper {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        /* Left Branding Panel */
        .brand-panel {
            flex: 1.1;
            background: linear-gradient(135deg, #0f2b38 0%, #184C74 100%);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 60px;
            color: #ffffff;
            position: relative;
            overflow: hidden;
        }

        .brand-panel::before {
            content: '';
            position: absolute;
            top: -100px;
            right: -100px;
            width: 350px;
            height: 350px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.04);
            pointer-events: none;
        }

        .brand-header img {
            height: 70px;
            margin-bottom: 25px;
        }

        .brand-header h1 {
            font-size: 32px;
            font-weight: 700;
            line-height: 1.3;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
        }

        .brand-header p {
            font-size: 17px;
            opacity: 0.85;
            line-height: 1.6;
        }

        .brand-footer {
            font-size: 14px;
            opacity: 0.7;
        }

        .brand-footer a {
            color: #ffffff;
            text-decoration: underline;
        }

        /* Right Form Panel */
        .form-panel {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            background: #ffffff;
        }

        .form-card {
            width: 100%;
            max-width: 440px;
        }

        .tabs-header {
            display: flex;
            border-bottom: 2px solid #e2e8f0;
            margin-bottom: 30px;
        }

        .tab-btn {
            flex: 1;
            background: transparent;
            border: none;
            padding: 14px 10px;
            font-family: 'Lato', sans-serif;
            font-size: 18px;
            font-weight: 700;
            color: #64748b;
            cursor: pointer;
            position: relative;
            transition: all 0.2s ease;
            text-align: center;
        }

        .tab-btn.active {
            color: #184C74;
        }

        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: #184C74;
        }

        .tab-title-line {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 24px;
        }

        .tab-title-bar {
            width: 4px;
            height: 24px;
            background-color: #184C74;
            border-radius: 2px;
        }

        .tab-title-text {
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
        }

        .error-alert {
            background-color: #fef2f2;
            color: #dc2626;
            border: 1px solid #fee2e2;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 22px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 22px;
        }

        .form-group label {
            display: block;
            font-size: 15px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 8px;
        }

        .input-group {
            position: relative;
        }

        .input-group i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 16px;
        }

        .input-group input {
            width: 100%;
            padding: 12px 14px 12px 42px;
            border: 1.5px solid #cbd5e1;
            border-radius: 8px;
            font-size: 15px;
            font-family: inherit;
            color: #0f172a;
            transition: all 0.2s ease;
        }

        .input-group input:focus {
            outline: none;
            border-color: #184C74;
            box-shadow: 0 0 0 3px rgba(24, 76, 116, 0.12);
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background-color: #184C74;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            transition: background 0.2s ease;
            box-shadow: 0 4px 12px rgba(24, 76, 116, 0.2);
            margin-top: 8px;
        }

        .btn-submit:hover {
            background-color: #123957;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 24px;
            font-size: 14px;
            color: #64748b;
            text-decoration: none;
        }

        .back-link:hover {
            color: #184C74;
            text-decoration: underline;
        }

        @media (max-width: 900px) {
            .brand-panel {
                display: none;
            }
            .form-panel {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>

<div class="login-wrapper">

    <!-- Left Branding (matches IITM department login style) -->
    <div class="brand-panel">
        <div class="brand-header">
            <img src="../images/iitm-logo.png" alt="IIT Madras">
            <h1>School of Interdisciplinary Studies</h1>
            <p>Indian Institute of Technology Madras</p>
            <p style="margin-top: 15px; font-size: 15px; opacity: 0.75;">
                Welcome to the SIDIS Portal. Faculty members can manage their personal profiles, publications, and inner pages, while administrators oversee department operations.
            </p>
        </div>

        <div class="brand-footer">
            <p>&copy; <?= date('Y') ?> IIT Madras &bull; <a href="../index.php">Back to SIDIS Website</a></p>
        </div>
    </div>

    <!-- Right Login Card -->
    <div class="form-panel">
        <div class="form-card">

            <!-- Tabs: Faculty Login / Admin Login -->
            <div class="tabs-header">
                <button type="button" class="tab-btn <?= $activeTab === 'faculty' ? 'active' : '' ?>" onclick="switchTab('faculty')">
                    Faculty Login
                </button>
                <button type="button" class="tab-btn <?= $activeTab === 'admin' ? 'active' : '' ?>" onclick="switchTab('admin')">
                    Administration Login
                </button>
            </div>

            <!-- Header Indicator -->
            <div class="tab-title-line">
                <div class="tab-title-bar"></div>
                <div class="tab-title-text" id="activeTitleText">
                    <?= $activeTab === 'faculty' ? 'Faculty Portal Login' : 'Superadmin Login' ?>
                </div>
            </div>

            <?php if (!empty($error)): ?>
                <div class="error-alert">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php" autocomplete="off">
                <input type="hidden" name="login_type" id="loginTypeInput" value="<?= htmlspecialchars($activeTab) ?>">

                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-group">
                        <i class="fa-solid fa-user"></i>
                        <input type="text" name="username" id="username" placeholder="Enter your username" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-group">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" name="password" id="password" placeholder="Enter your password" required>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fa-solid fa-right-to-bracket" style="margin-right: 6px;"></i> Sign In
                </button>
            </form>

            <a href="../index.php" class="back-link">
                &larr; Return to SIDIS Main Website
            </a>

        </div>
    </div>

</div>

<script>
function switchTab(type) {
    document.getElementById('loginTypeInput').value = type;
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));

    if (type === 'faculty') {
        event.target.classList.add('active');
        document.getElementById('activeTitleText').textContent = 'Faculty Portal Login';
    } else {
        event.target.classList.add('active');
        document.getElementById('activeTitleText').textContent = 'Superadmin Login';
    }
}
</script>

</body>
</html>