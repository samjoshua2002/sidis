<?php
/*
|--------------------------------------------------------------------------
| Account settings — shared by Admin (settings.php) and Faculty Portal
|--------------------------------------------------------------------------
| - Change username (needs current password)
| - Change password (needs current password)
| All text inputs arrive as chunked Base64 (same contract as the other forms).
| Every POST carries a per-session CSRF token.
*/

if (!function_exists('decodeInput')) {
    function decodeInput($value)
    {
        if ($value === null || $value === '') return '';
        $decoded = base64_decode($value, true);
        if ($decoded !== false && $decoded !== '') {
            return trim($decoded);
        }
        return trim($value);
    }
}

if (!function_exists('reconstructChunks')) {
    function reconstructChunks($fieldName)
    {
        $countField = $fieldName . '_chunk_count';

        if (!isset($_POST[$countField]) || (int)$_POST[$countField] === 0) {
            if (isset($_POST[$fieldName])) {
                return trim($_POST[$fieldName]);
            }
            return '';
        }

        $chunkCount = (int)$_POST[$countField];
        $base64 = '';
        for ($i = 0; $i < $chunkCount; $i++) {
            $chunkName = $fieldName . '_chunk_' . $i;
            if (!isset($_POST[$chunkName])) {
                return '';
            }
            $base64 .= $_POST[$chunkName];
        }
        return decodeInput($base64);
    }
}

/* Passwords must not be trimmed: decode without trim */
function accountReadSecret($fieldName)
{
    $countField = $fieldName . '_chunk_count';
    $chunkCount = (int)($_POST[$countField] ?? 0);

    if ($chunkCount <= 0) {
        return '';
    }

    $base64 = '';
    for ($i = 0; $i < $chunkCount; $i++) {
        $chunkName = $fieldName . '_chunk_' . $i;
        if (!isset($_POST[$chunkName])) {
            return '';
        }
        $base64 .= $_POST[$chunkName];
    }

    $decoded = base64_decode($base64, true);
    return $decoded === false ? '' : $decoded;
}

function accountCsrfToken()
{
    if (empty($_SESSION['account_csrf'])) {
        $_SESSION['account_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['account_csrf'];
}

function accountCsrfValid()
{
    return isset($_POST['csrf'], $_SESSION['account_csrf'])
        && hash_equals($_SESSION['account_csrf'], (string)$_POST['csrf']);
}

function accountFetchUser(PDO $pdo, $userId, $role)
{
    $stmt = $pdo->prepare("
        SELECT id, username, password, role, faculty_id, status, must_change_password, created_at, updated_at
        FROM users
        WHERE id = ? AND role = ?
        LIMIT 1
    ");
    $stmt->execute([(int)$userId, $role]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/*
| Handles one POST (action = username | password).
| Returns ['ok' => bool, 'message' => string, 'username' => new username or null]
*/
function accountHandlePost(PDO $pdo, array $user)
{
    if (!accountCsrfValid()) {
        return ['ok' => false, 'message' => 'Your session expired. Please reload the page and try again.'];
    }

    $action  = $_POST['action'] ?? '';
    $current = accountReadSecret('current_password');

    if ($current === '' || !password_verify($current, $user['password'])) {
        return ['ok' => false, 'message' => 'Current password is incorrect.'];
    }

    /* ---------------- Change username ---------------- */
    if ($action === 'username') {

        $newUsername = reconstructChunks('new_username');

        if ($newUsername === '') {
            return ['ok' => false, 'message' => 'Please enter a new username.'];
        }
        if (!preg_match('/^[A-Za-z0-9._-]{3,50}$/', $newUsername)) {
            return ['ok' => false, 'message' => 'Username must be 3–50 characters: letters, numbers, dot, dash or underscore only.'];
        }
        if ($newUsername === $user['username']) {
            return ['ok' => false, 'message' => 'That is already your username.'];
        }

        $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND id <> ?");
        $check->execute([$newUsername, (int)$user['id']]);
        if ((int)$check->fetchColumn() > 0) {
            return ['ok' => false, 'message' => 'That username is already taken.'];
        }

        try {
            $upd = $pdo->prepare("UPDATE users SET username = ? WHERE id = ?");
            $upd->execute([$newUsername, (int)$user['id']]);
        } catch (PDOException $e) {
            error_log('Account username update error: ' . $e->getMessage());
            return ['ok' => false, 'message' => 'Unable to update the username. Please try again.'];
        }

        return ['ok' => true, 'message' => 'Username updated. Use "' . $newUsername . '" next time you log in.', 'username' => $newUsername];
    }

    /* ---------------- Change password ---------------- */
    if ($action === 'password') {

        $new     = accountReadSecret('new_password');
        $confirm = accountReadSecret('confirm_password');

        if (strlen($new) < 8) {
            return ['ok' => false, 'message' => 'New password must be at least 8 characters.'];
        }
        if (strlen($new) > 72) {
            return ['ok' => false, 'message' => 'New password must be 72 characters or fewer.'];
        }
        if (!preg_match('/[A-Za-z]/', $new) || !preg_match('/\d/', $new)) {
            return ['ok' => false, 'message' => 'New password must contain at least one letter and one number.'];
        }
        if ($new !== $confirm) {
            return ['ok' => false, 'message' => 'New password and confirmation do not match.'];
        }
        if (password_verify($new, $user['password'])) {
            return ['ok' => false, 'message' => 'New password must be different from the current one.'];
        }

        try {
            $upd = $pdo->prepare("
                UPDATE users
                SET password = ?,
                    must_change_password = 0,
                    password_changed_at = NOW(),
                    reset_requested_at = NULL,
                    reset_request_note = ''
                WHERE id = ?
            ");
            $upd->execute([password_hash($new, PASSWORD_DEFAULT), (int)$user['id']]);
        } catch (PDOException $e) {
            error_log('Account password update error: ' . $e->getMessage());
            return ['ok' => false, 'message' => 'Unable to update the password. Please try again.'];
        }

        // New session id after a credential change
        session_regenerate_id(true);
        $_SESSION['account_csrf'] = bin2hex(random_bytes(32));

        return ['ok' => true, 'message' => 'Password changed successfully.'];
    }

    return ['ok' => false, 'message' => 'Unknown action.'];
}

/*
|--------------------------------------------------------------------------
| Settings page (simple)
|--------------------------------------------------------------------------
| settings.php              -> list: Update Username / Update Password
| settings.php?do=username  -> only the username form
| settings.php?do=password  -> only the password form
|
| $flashKey keeps admin and faculty messages apart (they share one session).
*/

function accountView()
{
    $do = $_GET['do'] ?? '';
    return in_array($do, ['username', 'password'], true) ? $do : '';
}

function accountRun(PDO $pdo, array $user, $flashKey, $sessionUserKey, $mustChangeKey = '')
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    $result = accountHandlePost($pdo, $user);

    if (!empty($result['username'])) {
        $_SESSION[$sessionUserKey] = $result['username'];
    }

    if ($result['ok'] && ($_POST['action'] ?? '') === 'password' && $mustChangeKey !== '') {
        unset($_SESSION[$mustChangeKey]);
    }

    $_SESSION[$flashKey] = $result;

    // Success -> back to the list; error -> stay on the same form
    $view = ($_POST['action'] ?? '') === 'password' ? 'password' : 'username';
    $target = $result['ok'] ? 'settings.php' : 'settings.php?do=' . $view;

    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    header('Location: ' . $target);
    exit;
}

function accountTakeFlash($flashKey)
{
    $flash = $_SESSION[$flashKey] ?? null;
    unset($_SESSION[$flashKey]);
    return $flash;
}

function accountRenderPage(array $user, $flash, $forced = false)
{
    $h = function ($v) {
        return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    };
    $view = $forced ? 'password' : accountView();
    $csrf = accountCsrfToken();
    ?>

    <div class="acct-wrap">

        <?php if ($flash): ?>
            <div class="acct-alert <?= $flash['ok'] ? 'ok' : 'err' ?>">
                <i class="fa-solid <?= $flash['ok'] ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i>
                <span><?= $h($flash['message']) ?></span>
            </div>
        <?php endif; ?>

        <?php if ($forced): ?>
            <div class="acct-alert warn">
                <i class="fa-solid fa-shield-halved"></i>
                <span>Your password was set by the administrator. Please choose your own new password to continue. Use the temporary password you were given as the current password.</span>
            </div>
        <?php endif; ?>

        <?php if ($view === ''): ?>

            <!-- LIST -->
            <div class="acct-list">
                <a href="settings.php?do=username" class="acct-item">
                    <span class="acct-icon"><i class="fa-solid fa-user-pen"></i></span>
                    <span class="acct-item-text">
                        <strong>Update Username</strong>
                        <small>Current: <?= $h($user['username']) ?></small>
                    </span>
                    <i class="fa-solid fa-chevron-right acct-chev"></i>
                </a>

                <a href="settings.php?do=password" class="acct-item">
                    <span class="acct-icon"><i class="fa-solid fa-key"></i></span>
                    <span class="acct-item-text">
                        <strong>Update Password</strong>
                        <small>Change your login password</small>
                    </span>
                    <i class="fa-solid fa-chevron-right acct-chev"></i>
                </a>
            </div>

        <?php else: ?>

            <?php if (!$forced): ?>
                <a href="settings.php" class="acct-back"><i class="fa-solid fa-arrow-left"></i> Back to Settings</a>
            <?php endif; ?>

            <form class="acct-card" method="POST" action="settings.php?do=<?= $h($view) ?>" onsubmit="return acctPrepare(this)" autocomplete="off">
                <input type="hidden" name="csrf" value="<?= $h($csrf) ?>">
                <input type="hidden" name="action" value="<?= $h($view) ?>">

                <?php if ($view === 'username'): ?>

                    <h3><i class="fa-solid fa-user-pen"></i> Update Username</h3>

                    <label class="acct-label" for="f_new_username">New username</label>
                    <input type="text" id="f_new_username" data-chunk="new_username" class="acct-input"
                        maxlength="50" pattern="[A-Za-z0-9._\-]{3,50}" required autocomplete="off"
                        placeholder="Current: <?= $h($user['username']) ?>">
                    <div class="acct-hint">3–50 characters: letters, numbers, dot, dash or underscore.</div>

                    <label class="acct-label" for="f_current_password">Current password</label>
                    <div class="acct-pass">
                        <input type="password" id="f_current_password" data-chunk="current_password" data-secret="1"
                            class="acct-input" required autocomplete="current-password">
                        <button type="button" class="acct-eye" onclick="acctToggle(this)" aria-label="Show password"><i class="fa-solid fa-eye"></i></button>
                    </div>

                    <div class="acct-chunks"></div>
                    <button type="submit" class="acct-btn">Update Username</button>

                <?php else: ?>

                    <h3><i class="fa-solid fa-key"></i> Update Password</h3>

                    <label class="acct-label" for="f_current_password">Current password</label>
                    <div class="acct-pass">
                        <input type="password" id="f_current_password" data-chunk="current_password" data-secret="1"
                            class="acct-input" required autocomplete="current-password">
                        <button type="button" class="acct-eye" onclick="acctToggle(this)" aria-label="Show password"><i class="fa-solid fa-eye"></i></button>
                    </div>

                    <label class="acct-label" for="f_new_password">New password</label>
                    <div class="acct-pass">
                        <input type="password" id="f_new_password" data-chunk="new_password" data-secret="1"
                            class="acct-input" required minlength="8" maxlength="72" autocomplete="new-password">
                        <button type="button" class="acct-eye" onclick="acctToggle(this)" aria-label="Show password"><i class="fa-solid fa-eye"></i></button>
                    </div>
                    <div class="acct-hint">At least 8 characters, with a letter and a number.</div>

                    <label class="acct-label" for="f_confirm_password">Confirm new password</label>
                    <div class="acct-pass">
                        <input type="password" id="f_confirm_password" data-chunk="confirm_password" data-secret="1"
                            class="acct-input" required minlength="8" maxlength="72" autocomplete="new-password">
                        <button type="button" class="acct-eye" onclick="acctToggle(this)" aria-label="Show password"><i class="fa-solid fa-eye"></i></button>
                    </div>

                    <div class="acct-chunks"></div>
                    <button type="submit" class="acct-btn">Update Password</button>

                <?php endif; ?>
            </form>

        <?php endif; ?>

    </div>

    <style>
        .acct-wrap {
            max-width: 560px;
        }

        .acct-list {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
        }

        .acct-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 18px 20px;
            text-decoration: none;
            color: #0f172a;
            transition: background .15s ease;
        }

        .acct-item + .acct-item {
            border-top: 1px solid #eef2f6;
        }

        .acct-item:hover {
            background: #f8fafc;
        }

        .acct-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: #eaf2f9;
            color: #184C74;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .acct-item-text {
            flex: 1;
            min-width: 0;
        }

        .acct-item-text strong {
            display: block;
            font-size: 15px;
        }

        .acct-item-text small {
            display: block;
            font-size: 12.5px;
            color: #64748b;
            margin-top: 2px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .acct-chev {
            color: #94a3b8;
            font-size: 13px;
        }

        .acct-item:hover .acct-chev {
            color: #184C74;
        }

        .acct-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 600;
            color: #184C74;
            text-decoration: none;
            margin-bottom: 14px;
        }

        .acct-back:hover {
            text-decoration: underline;
        }

        .acct-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 24px;
            display: flex;
            flex-direction: column;
        }

        .acct-card h3 {
            margin: 0 0 8px;
            padding: 0;
            border: none;
            font-size: 17px;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .acct-card h3 i {
            color: #184C74;
        }

        .acct-label {
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            margin: 14px 0 6px;
        }

        .acct-input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 14px;
            color: #0f172a;
            background: #fff;
            font-family: inherit;
            box-sizing: border-box;
        }

        .acct-input:focus {
            outline: none;
            border-color: #184C74;
            box-shadow: 0 0 0 3px rgba(24, 76, 116, .12);
        }

        .acct-hint {
            font-size: 12px;
            color: #64748b;
            margin-top: 5px;
        }

        .acct-pass {
            position: relative;
        }

        .acct-pass .acct-input {
            padding-right: 42px;
        }

        .acct-eye {
            position: absolute;
            right: 6px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            color: #94a3b8;
            cursor: pointer;
            padding: 6px 8px;
        }

        .acct-eye:hover {
            color: #184C74;
        }

        .acct-btn {
            margin-top: 22px;
            align-self: flex-start;
            background: #184C74;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 11px 22px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
        }

        .acct-btn:hover {
            background: #123957;
        }

        .acct-alert {
            display: flex;
            gap: 10px;
            align-items: center;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 16px;
            font-size: 14px;
        }

        .acct-alert.ok {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .acct-alert.warn {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
            align-items: flex-start;
        }

        .acct-alert.err {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
    </style>

    <script>
        /* Encode every field of the form as chunked Base64 before submit */
        function acctB64(str) {
            return btoa(unescape(encodeURIComponent(str)));
        }

        function acctPrepare(form) {
            var np = form.querySelector('[data-chunk="new_password"]');
            var cp = form.querySelector('[data-chunk="confirm_password"]');
            if (np && cp && np.value !== cp.value) {
                cp.setCustomValidity('Passwords do not match');
                cp.reportValidity();
                cp.addEventListener('input', function () { cp.setCustomValidity(''); }, { once: true });
                return false;
            }

            var box = form.querySelector('.acct-chunks');
            box.innerHTML = '';

            form.querySelectorAll('[data-chunk]').forEach(function (el) {
                var value = el.dataset.secret ? el.value : el.value.trim();
                var b64   = value === '' ? '' : acctB64(value);
                var count = Math.ceil(b64.length / 200);

                var c = document.createElement('input');
                c.type = 'hidden';
                c.name = el.dataset.chunk + '_chunk_count';
                c.value = count;
                box.appendChild(c);

                for (var i = 0; i < count; i++) {
                    var inp = document.createElement('input');
                    inp.type = 'hidden';
                    inp.name = el.dataset.chunk + '_chunk_' + i;
                    inp.value = b64.substring(i * 200, (i + 1) * 200);
                    box.appendChild(inp);
                }
            });
            return true;
        }

        function acctToggle(btn) {
            var input = btn.parentNode.querySelector('input');
            var show  = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.innerHTML = show ? '<i class="fa-solid fa-eye-slash"></i>' : '<i class="fa-solid fa-eye"></i>';
            btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        }
    </script>
    <?php
}


/*
| Readable temporary password, e.g. "Kp7m-Qx4r-9Hwt" (no 0/O/1/l/I)
*/
function accountTempPassword()
{
    $lower = 'abcdefghjkmnpqrstuvwxyz';
    $upper = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
    $digit = '23456789';
    $all   = $lower . $upper . $digit;

    do {
        $chars = '';
        for ($i = 0; $i < 12; $i++) {
            $chars .= $all[random_int(0, strlen($all) - 1)];
        }
    } while (!preg_match('/[a-z]/', $chars) || !preg_match('/[A-Z]/', $chars) || !preg_match('/\d/', $chars));

    return substr($chars, 0, 4) . '-' . substr($chars, 4, 4) . '-' . substr($chars, 8, 4);
}
