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

/*
|--------------------------------------------------------------------------
| FORGOT PASSWORD -> request a reset from the administrator
|--------------------------------------------------------------------------
| No email/OTP: the request is stored on the user's row and shown to the
| admin on Users & Faculty Logins. The reply is always the same, so this
| form cannot be used to find out which usernames exist.
*/
$showForgot   = isset($_GET['forgot']) || (($_POST['form'] ?? '') === 'forgot');
$forgotSent   = false;
$forgotError  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'forgot') {

    $reqUser = trim($_POST['req_username'] ?? '');

    // note arrives as chunked Base64 (same as the other forms)
    $reqNote = '';
    $noteChunks = (int)($_POST['req_note_chunk_count'] ?? 0);
    if ($noteChunks > 0 && $noteChunks <= 20) {
        $b64 = '';
        for ($i = 0; $i < $noteChunks; $i++) {
            $b64 .= (string)($_POST['req_note_chunk_' . $i] ?? '');
        }
        $decoded = base64_decode($b64, true);
        $reqNote = $decoded === false ? '' : trim($decoded);
    }
    $reqNote = mb_substr(preg_replace('/\s+/u', ' ', strip_tags($reqNote)), 0, 250);

    // simple per-session throttle: 5 requests per hour
    $now = time();
    $_SESSION['reset_req_times'] = array_values(array_filter(
        $_SESSION['reset_req_times'] ?? [],
        function ($t) use ($now) { return $t > $now - 3600; }
    ));

    if ($reqUser === '') {
        $forgotError = 'Please enter your username.';
    } elseif (count($_SESSION['reset_req_times']) >= 5) {
        $forgotError = 'Too many requests. Please try again later or contact the SIDIS office.';
    } else {
        $_SESSION['reset_req_times'][] = $now;
        try {
            // only active accounts; ignore repeats within 10 minutes
            $stmt = $pdo->prepare("
                UPDATE users
                SET reset_requested_at = NOW(),
                    reset_request_note = ?,
                    updated_at = updated_at
                WHERE username = ?
                  AND status = 1
                  AND (reset_requested_at IS NULL OR reset_requested_at < NOW() - INTERVAL 10 MINUTE)
            ");
            $stmt->execute([$reqNote, $reqUser]);
        } catch (PDOException $e) {
            error_log('Reset request error: ' . $e->getMessage());
        }
        $forgotSent = true;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') !== 'forgot') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $loginType = $_POST['login_type'] ?? 'faculty';

    if ($username === '' || $password === '') {
        $error = 'Please enter both your username and password.';
    } else {
        $stmt = $pdo->prepare("
            SELECT id, username, password, role, faculty_id, status, must_change_password
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

            try {
                $pdo->prepare("UPDATE users SET last_login_at = NOW(), updated_at = updated_at WHERE id = ?")
                    ->execute([(int)$user['id']]);
            } catch (PDOException $e) {
                error_log('Last login update error: ' . $e->getMessage());
            }

            $mustChange = (int)($user['must_change_password'] ?? 0) === 1;

            if ($user['role'] === 'faculty') {
                $_SESSION['faculty_user_id'] = $user['id'];
                $_SESSION['faculty_username'] = $user['username'];
                $_SESSION['faculty_id'] = $user['faculty_id'];

                if ($mustChange) {
                    $_SESSION['faculty_must_change'] = 1;
                    header('Location: faculty-portal/settings.php?do=password');
                    exit;
                }

                header('Location: faculty-portal/index.php');
                exit;
            } else {
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];

                if ($mustChange) {
                    $_SESSION['admin_must_change'] = 1;
                    header('Location: settings.php?do=password');
                    exit;
                }

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

        .forgot-row {
            display: flex;
            justify-content: flex-end;
            margin: -10px 0 18px;
        }

        .forgot-link {
            font-size: 13px;
            color: #184C74;
            font-weight: 600;
            text-decoration: none;
        }

        .forgot-link:hover {
            text-decoration: underline;
        }

        .info-alert {
            background-color: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
            padding: 14px 16px;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 22px;
            display: flex;
            gap: 10px;
            line-height: 1.5;
        }

        .forgot-intro {
            font-size: 14px;
            color: #475569;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .form-group textarea {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            resize: vertical;
            min-height: 80px;
        }

        .form-group textarea:focus {
            outline: none;
            border-color: #184C74;
            box-shadow: 0 0 0 3px rgba(24, 76, 116, 0.12);
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

            <?php if ($showForgot): ?>

            <!-- FORGOT PASSWORD: request a reset from the administrator -->
            <div class="tab-title-line">
                <div class="tab-title-bar"></div>
                <div class="tab-title-text">Forgot password?</div>
            </div>

            <?php if ($forgotSent): ?>
                <div class="info-alert">
                    <i class="fa-solid fa-circle-check" style="margin-top:3px;"></i>
                    <span>If that username exists, your request has been sent to the SIDIS administrator. They will contact you with a temporary password, which you will change after logging in.</span>
                </div>
                <a href="login.php" class="btn-submit" style="display:block; text-align:center; text-decoration:none;">
                    <i class="fa-solid fa-arrow-left" style="margin-right: 6px;"></i> Back to Sign In
                </a>
            <?php else: ?>
                <p class="forgot-intro">Enter your username and we'll notify the SIDIS administrator. They will reset your password and share a temporary one with you.</p>

                <?php if ($forgotError !== ''): ?>
                    <div class="error-alert">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <span><?= htmlspecialchars($forgotError) ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" action="login.php?forgot=1" autocomplete="off" onsubmit="return prepareForgot()">
                    <input type="hidden" name="form" value="forgot">

                    <div class="form-group">
                        <label for="req_username">Username</label>
                        <div class="input-group">
                            <i class="fa-solid fa-user"></i>
                            <input type="text" name="req_username" id="req_username" placeholder="Your portal username" maxlength="100" required autofocus>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="req_note">Message to admin <span style="font-weight:400; color:#94a3b8;">(optional)</span></label>
                        <textarea id="req_note" maxlength="250" placeholder="e.g. I forgot my password. Please call me on extension 1234."></textarea>
                        <input type="hidden" name="req_note_chunk_count" id="req_note_chunk_count" value="0">
                        <div id="req_note_chunks"></div>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fa-solid fa-paper-plane" style="margin-right: 6px;"></i> Send Request
                    </button>
                </form>

                <a href="login.php" class="back-link">&larr; Back to Sign In</a>
            <?php endif; ?>

            <?php else: ?>

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

                <div class="forgot-row">
                    <a href="login.php?forgot=1" class="forgot-link">Forgot password?</a>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fa-solid fa-right-to-bracket" style="margin-right: 6px;"></i> Sign In
                </button>
            </form>

            <a href="../index.php" class="back-link">
                &larr; Return to SIDIS Main Website
            </a>

            <?php endif; ?>

        </div>
    </div>

</div>

<script>
function prepareForgot() {
    var note = document.getElementById('req_note');
    var box = document.getElementById('req_note_chunks');
    var b64 = note && note.value.trim() !== '' ? btoa(unescape(encodeURIComponent(note.value.trim()))) : '';
    var count = Math.ceil(b64.length / 200);
    document.getElementById('req_note_chunk_count').value = count;
    box.innerHTML = '';
    for (var i = 0; i < count; i++) {
        var inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = 'req_note_chunk_' + i;
        inp.value = b64.substring(i * 200, (i + 1) * 200);
        box.appendChild(inp);
    }
    return true;
}

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