<?php
/*
|--------------------------------------------------------------------------
| Admin > Users & Faculty Logins
|--------------------------------------------------------------------------
| - Create / edit a faculty login (username + status)
| - Reset password: admin gets a temporary password (shown ONCE) to share
|   offline; the faculty member must change it at next login
| - Password reset requests sent from the login page ("Forgot password?")
| - Switch profile page type (inner page / external link)
| All text fields are sent as chunked Base64; every action has a CSRF token.
*/

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/account_helpers.php';

$csrf = accountCsrfToken();

function usersFlash($type, $message, array $extra = [])
{
    $_SESSION['users_flash'] = array_merge(['type' => $type, 'message' => $message], $extra);
    header('Location: users.php');
    exit;
}

function usersFetchFacultyUser(PDO $pdo, $userId)
{
    $stmt = $pdo->prepare("
        SELECT u.id, u.username, u.role, u.faculty_id, f.name, f.lname
        FROM users u
        LEFT JOIN faculty f ON f.id = u.faculty_id
        WHERE u.id = ? AND u.role = 'faculty'
        LIMIT 1
    ");
    $stmt->execute([(int)$userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}


/*
|--------------------------------------------------------------------------
| ACTIONS (POST -> redirect)
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!accountCsrfValid()) {
        usersFlash('error', 'Your session expired. Please try again.');
    }

    $action = $_POST['action'] ?? '';

    try {

        /* ---------- Create or edit a faculty login ---------- */
        if ($action === 'save_user') {

            $facultyId = (int)reconstructChunks('faculty_id');
            $username  = reconstructChunks('username');
            $status    = reconstructChunks('status') === '0' ? 0 : 1;
            $tempPass  = accountReadSecret('temp_password');

            if ($facultyId <= 0) {
                usersFlash('error', 'Please select a faculty member.');
            }
            if (!preg_match('/^[A-Za-z0-9._-]{3,50}$/', $username)) {
                usersFlash('error', 'Username must be 3–50 characters: letters, numbers, dot, dash or underscore.');
            }

            $check = $pdo->prepare("SELECT id FROM users WHERE username = ? AND (faculty_id <> ? OR faculty_id IS NULL) LIMIT 1");
            $check->execute([$username, $facultyId]);
            if ($check->fetch()) {
                usersFlash('error', 'The username "' . $username . '" is already taken.');
            }

            $existing = $pdo->prepare("SELECT id FROM users WHERE faculty_id = ? AND role = 'faculty' LIMIT 1");
            $existing->execute([$facultyId]);
            $existingId = $existing->fetchColumn();

            if ($existingId) {
                $pdo->prepare("UPDATE users SET username = ?, status = ? WHERE id = ?")
                    ->execute([$username, $status, (int)$existingId]);
                usersFlash('success', 'Login updated for "' . $username . '".');
            }

            // New login: temporary password (given or generated), must be changed at first login
            if ($tempPass !== '' && (strlen($tempPass) < 8 || !preg_match('/[A-Za-z]/', $tempPass) || !preg_match('/\d/', $tempPass))) {
                usersFlash('error', 'Temporary password must be at least 8 characters with a letter and a number.');
            }
            $tempPass = $tempPass !== '' ? $tempPass : accountTempPassword();

            $pdo->prepare("
                INSERT INTO users (username, password, role, faculty_id, status, must_change_password, created_at, updated_at)
                VALUES (?, ?, 'faculty', ?, ?, 1, NOW(), NOW())
            ")->execute([$username, password_hash($tempPass, PASSWORD_DEFAULT), $facultyId, $status]);

            $name = $pdo->prepare("SELECT TRIM(CONCAT(name, ' ', COALESCE(lname, ''))) FROM faculty WHERE id = ?");
            $name->execute([$facultyId]);

            usersFlash('success', 'Login created.', [
                'temp' => ['name' => $name->fetchColumn(), 'username' => $username, 'password' => $tempPass],
            ]);
        }

        /* ---------- Reset password (forced change at next login) ---------- */
        if ($action === 'reset_password') {

            $user = usersFetchFacultyUser($pdo, reconstructChunks('user_id'));
            if (!$user) {
                usersFlash('error', 'Login not found.');
            }

            $custom = accountReadSecret('custom_password');
            if ($custom !== '' && (strlen($custom) < 8 || !preg_match('/[A-Za-z]/', $custom) || !preg_match('/\d/', $custom))) {
                usersFlash('error', 'Temporary password must be at least 8 characters with a letter and a number.');
            }
            $tempPass = $custom !== '' ? $custom : accountTempPassword();

            $pdo->prepare("
                UPDATE users
                SET password = ?,
                    must_change_password = 1,
                    reset_requested_at = NULL,
                    reset_request_note = ''
                WHERE id = ?
            ")->execute([password_hash($tempPass, PASSWORD_DEFAULT), (int)$user['id']]);

            usersFlash('success', 'Password reset.', [
                'temp' => [
                    'name'     => trim($user['name'] . ' ' . $user['lname']),
                    'username' => $user['username'],
                    'password' => $tempPass,
                ],
            ]);
        }

        /* ---------- Dismiss a reset request ---------- */
        if ($action === 'dismiss_request') {

            $user = usersFetchFacultyUser($pdo, reconstructChunks('user_id'));
            if ($user) {
                $pdo->prepare("UPDATE users SET reset_requested_at = NULL, reset_request_note = '' WHERE id = ?")
                    ->execute([(int)$user['id']]);
            }
            usersFlash('success', 'Request dismissed.');
        }

        /* ---------- Profile page type ---------- */
        if ($action === 'toggle_page_type') {

            $facultyId  = (int)reconstructChunks('faculty_id');
            $targetType = reconstructChunks('target_type');

            if ($facultyId > 0 && in_array($targetType, ['inner', 'personal'], true)) {
                $pdo->prepare("UPDATE faculty SET page_type = ? WHERE id = ? AND top_order > 0")
                    ->execute([$targetType, $facultyId]);
                usersFlash('success', 'Profile type changed to ' . ($targetType === 'inner' ? 'Inner Faculty Page' : 'External Link') . '.');
            }
            usersFlash('error', 'Invalid profile type request.');
        }

    } catch (PDOException $e) {
        error_log('Users page error: ' . $e->getMessage());
        usersFlash('error', 'Database error. Please try again.');
    }

    usersFlash('error', 'Unknown action.');
}

$flash = $_SESSION['users_flash'] ?? null;
unset($_SESSION['users_flash']);


/*
|--------------------------------------------------------------------------
| DATA
|--------------------------------------------------------------------------
*/

$topFaculty = $pdo->query("
    SELECT
        f.id AS faculty_id, f.name, f.lname, f.designation, f.department, f.email, f.office,
        f.image, f.top_order, COALESCE(f.page_type, 'personal') AS page_type, f.personal_page,
        u.id AS user_id, u.username, u.status AS user_status, u.must_change_password,
        u.reset_requested_at, u.reset_request_note, u.password_changed_at, u.last_login_at,
        TIMESTAMPDIFF(SECOND, u.reset_requested_at, NOW())  AS req_secs,
        TIMESTAMPDIFF(SECOND, u.password_changed_at, NOW()) AS pwd_secs,
        TIMESTAMPDIFF(SECOND, u.last_login_at, NOW())       AS login_secs
    FROM faculty f
    LEFT JOIN users u ON u.faculty_id = f.id AND u.role = 'faculty'
    WHERE f.top_order > 0 AND f.status = 1
    ORDER BY (u.reset_requested_at IS NULL), f.top_order ASC, f.id ASC
")->fetchAll(PDO::FETCH_ASSOC);

/* All pending requests (also faculty not in the list above) */
$requests = $pdo->query("
    SELECT u.id AS user_id, u.username, u.reset_requested_at, u.reset_request_note,
           TIMESTAMPDIFF(SECOND, u.reset_requested_at, NOW()) AS req_secs,
           TRIM(CONCAT(COALESCE(f.name, ''), ' ', COALESCE(f.lname, ''))) AS full_name
    FROM users u
    LEFT JOIN faculty f ON f.id = u.faculty_id
    WHERE u.role = 'faculty' AND u.reset_requested_at IS NOT NULL
    ORDER BY u.reset_requested_at ASC
")->fetchAll(PDO::FETCH_ASSOC);

$totalFaculty = count($topFaculty);
$withLogin    = count(array_filter($topFaculty, fn($f) => !empty($f['user_id'])));
$pendingFirst = count(array_filter($topFaculty, fn($f) => !empty($f['user_id']) && (int)$f['must_change_password'] === 1));

/* Photo URL, or '' when there is no usable photo (an initials avatar is shown instead) */
function resolveFacultyThumb($image)
{
    $clean = trim((string)$image);
    if ($clean !== '' && file_exists(__DIR__ . '/../images/faculty/' . $clean)) {
        return '../images/faculty/' . $clean;
    }
    return '';
}

function usersInitials($name)
{
    $parts = preg_split('/\s+/', trim((string)$name));
    $out = '';
    foreach (array_slice($parts, 0, 2) as $p) {
        $out .= mb_strtoupper(mb_substr($p, 0, 1));
    }
    return $out !== '' ? $out : '?';
}

/* $secs comes from MySQL TIMESTAMPDIFF(SECOND, col, NOW()), so PHP/MySQL time zones never matter */
function usersAgo($secs, $datetime = '')
{
    if ($secs === null || $secs === '') return '';
    $d = max(0, (int)$secs);
    if ($d < 60)     return 'just now';
    if ($d < 3600)   return floor($d / 60) . ' min ago';
    if ($d < 86400)  return floor($d / 3600) . ' h ago';
    if ($d < 604800) return floor($d / 86400) . ' d ago';
    $t = strtotime((string)$datetime);
    return $t ? 'on ' . date('d M Y', $t) : floor($d / 86400) . ' d ago';
}

$h = function ($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
};
$jsonFlags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;

include 'header.php';
?>

<style>
    .uq { --navy: #184C74; --ink: #0f172a; --ink-2: #475569; --ink-3: #94a3b8; --line: #e5eaf0; --soft: #f6f8fb; }

    .uq-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 20px;
    }

    .uq-stat {
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 14px;
        padding: 16px 18px;
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .uq-stat i {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #eaf2f9;
        color: var(--navy);
        font-size: 16px;
        flex-shrink: 0;
    }

    .uq-stat.alert i { background: #fee2e2; color: #b91c1c; }
    .uq-stat.amber i { background: #fef3c7; color: #92400e; }

    .uq-stat strong { display: block; font-size: 24px; line-height: 1; color: var(--ink); }
    .uq-stat span { font-size: 12.5px; color: var(--ink-2); }

    /* Pending requests */
    .uq-requests {
        background: #fff7ed;
        border: 1px solid #fed7aa;
        border-radius: 14px;
        padding: 16px 18px;
        margin-bottom: 20px;
    }

    .uq-requests h3 {
        margin: 0 0 10px;
        font-size: 15px;
        color: #9a3412;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .uq-req {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 10px 0;
        border-top: 1px dashed #fdba74;
    }

    .uq-req:first-of-type { border-top: none; }

    .uq-req-body { flex: 1; min-width: 0; }
    .uq-req-body strong { color: var(--ink); font-size: 14px; }
    .uq-req-body small { display: block; color: #9a3412; font-size: 12px; margin-top: 2px; }
    .uq-req-note { display: block; color: var(--ink-2); font-size: 13px; margin-top: 4px; font-style: italic; overflow-wrap: anywhere; }

    /* Toolbar */
    .uq-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 14px;
    }

    .uq-search {
        position: relative;
        flex: 1;
        max-width: 420px;
    }

    .uq-search input {
        width: 100%;
        padding: 10px 12px 10px 38px;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        font-size: 14px;
    }

    .uq-search input:focus { outline: none; border-color: var(--navy); box-shadow: 0 0 0 3px rgba(24, 76, 116, .12); }
    .uq-search i { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: var(--ink-3); }

    .uq-count { font-size: 13px; color: var(--ink-2); }

    /* Account cards */
    .uq-list {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .uq-card {
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 16px;
        padding: 18px;
        display: flex;
        flex-direction: column;
        gap: 14px;
        transition: box-shadow .2s ease, border-color .2s ease;
    }

    .uq-card:hover { border-color: #cfdbe7; box-shadow: 0 12px 26px -18px rgba(15, 23, 42, .3); }
    .uq-card.has-request { border-color: #fdba74; box-shadow: 0 0 0 3px #ffedd5; }

    .uq-top { display: flex; gap: 14px; align-items: flex-start; }

    .uq-top img {
        width: 58px;
        height: 58px;
        border-radius: 14px;
        object-fit: cover;
        border: 1px solid var(--line);
        flex-shrink: 0;
    }

    .uq-avatar {
        width: 58px;
        height: 58px;
        border-radius: 14px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #eaf2f9;
        color: var(--navy);
        font-weight: 800;
        font-size: 19px;
    }

    .uq-id { flex: 1; min-width: 0; }
    .uq-name { font-size: 16px; font-weight: 700; color: var(--navy); }
    .uq-role { font-size: 13px; color: var(--ink-2); margin-top: 2px; }
    .uq-meta { font-size: 12px; color: var(--ink-3); margin-top: 4px; overflow-wrap: anywhere; }
    .uq-meta i { margin-right: 4px; }

    .uq-chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 11px;
        font-weight: 700;
        padding: 3px 9px;
        border-radius: 999px;
        white-space: nowrap;
    }

    .chip-core     { background: #dcfce7; color: #166534; }
    .chip-adjunct  { background: #e0e7ff; color: #3730a3; }
    .chip-pop      { background: #fef3c7; color: #92400e; }
    .chip-inner    { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
    .chip-ext      { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
    .chip-active   { background: #dcfce7; color: #166534; }
    .chip-off      { background: #f1f5f9; color: #64748b; }
    .chip-none     { background: #f8fafc; color: #94a3b8; border: 1px dashed #cbd5e1; }
    .chip-must     { background: #fef3c7; color: #92400e; }
    .chip-req      { background: #ffedd5; color: #9a3412; }

    .uq-login {
        background: var(--soft);
        border: 1px solid var(--line);
        border-radius: 12px;
        padding: 12px 14px;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .uq-login-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; }

    .uq-user {
        font-family: ui-monospace, SFMono-Regular, Consolas, monospace;
        font-size: 14px;
        font-weight: 700;
        color: var(--navy);
        display: inline-flex;
        align-items: center;
        gap: 7px;
    }

    .uq-chips { display: flex; gap: 6px; flex-wrap: wrap; }
    .uq-sub { font-size: 12px; color: var(--ink-2); }
    .uq-note { font-size: 12.5px; color: #9a3412; font-style: italic; overflow-wrap: anywhere; }

    .uq-actions { display: flex; gap: 8px; flex-wrap: wrap; }

    .uq-btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 8px 13px;
        border-radius: 9px;
        font-size: 13px;
        font-weight: 600;
        border: 1px solid #cbd5e1;
        background: #fff;
        color: #334155;
        cursor: pointer;
        text-decoration: none;
        font-family: inherit;
        transition: background .15s ease, color .15s ease, border-color .15s ease;
    }

    .uq-btn:hover { border-color: var(--navy); color: var(--navy); }
    .uq-btn.primary { background: var(--navy); border-color: var(--navy); color: #fff; }
    .uq-btn.primary:hover { background: #123957; color: #fff; }
    .uq-btn.warn { background: #fff7ed; border-color: #fdba74; color: #9a3412; }
    .uq-btn.warn:hover { background: #ea580c; border-color: #ea580c; color: #fff; }
    .uq-btn.ghost { border-color: transparent; background: transparent; color: var(--ink-2); padding-left: 6px; padding-right: 6px; }
    .uq-btn.ghost:hover { color: var(--navy); }

    .uq-empty { text-align: center; padding: 40px; color: var(--ink-3); grid-column: 1 / -1; }

    /* Alerts */
    .uq-alert {
        display: flex;
        gap: 10px;
        align-items: flex-start;
        padding: 12px 16px;
        border-radius: 12px;
        margin-bottom: 18px;
        font-size: 14px;
    }

    .uq-alert.ok  { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .uq-alert.err { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

    /* One-time temporary password panel */
    .uq-temp {
        background: linear-gradient(135deg, #184C74 0%, #1F3C44 100%);
        color: #fff;
        border-radius: 16px;
        padding: 20px 22px;
        margin-bottom: 20px;
    }

    .uq-temp h3 { margin: 0 0 4px; font-size: 17px; color: #fff; }
    .uq-temp p { margin: 0; font-size: 13px; color: rgba(255, 255, 255, .8); line-height: 1.55; }

    .uq-temp-box {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin: 14px 0 10px;
    }

    .uq-temp-field {
        background: rgba(255, 255, 255, .1);
        border: 1px solid rgba(255, 255, 255, .2);
        border-radius: 10px;
        padding: 10px 14px;
        min-width: 200px;
    }

    .uq-temp-field small { display: block; font-size: 11px; text-transform: uppercase; letter-spacing: .06em; color: rgba(255, 255, 255, .65); }
    .uq-temp-field code { font-size: 19px; font-weight: 700; color: #fff; letter-spacing: .04em; }

    .uq-copy {
        align-self: center;
        background: #A7E747;
        color: #14310a;
        border: none;
        border-radius: 9px;
        padding: 10px 16px;
        font-weight: 700;
        cursor: pointer;
        font-family: inherit;
    }

    /* Modals */
    .uq-modal .delete-modal-content { max-width: 480px; text-align: left; padding: 24px 26px; }
    .uq-modal h3 { margin: 0 0 4px; color: var(--navy); font-size: 19px; }
    .uq-modal .lead { color: #64748b; font-size: 13px; margin: 0 0 16px; }
    .uq-field { margin-bottom: 14px; }
    .uq-field label { display: block; font-weight: 600; margin-bottom: 6px; font-size: 13.5px; color: #334155; }

    .uq-field input,
    .uq-field select {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 14px;
        font-family: inherit;
        box-sizing: border-box;
    }

    .uq-field input:focus, .uq-field select:focus { outline: none; border-color: var(--navy); box-shadow: 0 0 0 3px rgba(24, 76, 116, .12); }
    .uq-hint { font-size: 12px; color: #64748b; margin-top: 5px; }

    .uq-radio { display: flex; flex-direction: column; gap: 8px; margin-bottom: 12px; }

    .uq-radio label {
        display: flex;
        gap: 10px;
        align-items: flex-start;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 10px 12px;
        cursor: pointer;
        font-size: 13.5px;
    }

    .uq-radio label:has(input:checked) { border-color: var(--navy); background: #f0f6fb; }
    .uq-radio small { display: block; color: #64748b; font-size: 12px; margin-top: 2px; }

    @media (max-width: 1200px) {
        .uq-list  { grid-template-columns: 1fr; }
        .uq-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
</style>


<div class="dashboard-content uq">

    <div class="page-header">
        <div>
            <h2>Users &amp; Faculty Logins</h2>
            <p>Create faculty portal logins, reset passwords and handle password requests.</p>
        </div>
        <div>
            <button type="button" class="btn-add" onclick="openLoginModal(0)">+ Create Faculty Login</button>
        </div>
    </div>


    <!-- FLASH -->
    <?php if ($flash && !empty($flash['temp'])): ?>
        <div class="uq-temp">
            <h3><i class="fa-solid fa-key"></i> <?= $h($flash['message']) ?> Temporary password for <?= $h($flash['temp']['name']) ?></h3>
            <p>Share this with the faculty member by phone or in person. They must set their own password the first time they log in.
               <strong>This password will not be shown again.</strong></p>
            <div class="uq-temp-box">
                <div class="uq-temp-field"><small>Username</small><code><?= $h($flash['temp']['username']) ?></code></div>
                <div class="uq-temp-field"><small>Temporary password</small><code id="tempPass"><?= $h($flash['temp']['password']) ?></code></div>
                <button type="button" class="uq-copy" onclick="copyTemp(this)"><i class="fa-regular fa-copy"></i> Copy</button>
            </div>
        </div>
    <?php elseif ($flash): ?>
        <div class="uq-alert <?= $flash['type'] === 'success' ? 'ok' : 'err' ?>">
            <i class="fa-solid <?= $flash['type'] === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>" style="margin-top:2px;"></i>
            <span><?= $h($flash['message']) ?></span>
        </div>
    <?php endif; ?>


    <!-- STATS -->
    <div class="uq-stats">
        <div class="uq-stat"><i class="fa-solid fa-chalkboard-user"></i><div><strong><?= $totalFaculty ?></strong><span>Faculty</span></div></div>
        <div class="uq-stat"><i class="fa-solid fa-user-check"></i><div><strong><?= $withLogin ?></strong><span>Have a portal login</span></div></div>
        <div class="uq-stat amber"><i class="fa-solid fa-hourglass-half"></i><div><strong><?= $pendingFirst ?></strong><span>Waiting to set own password</span></div></div>
        <div class="uq-stat <?= count($requests) ? 'alert' : '' ?>"><i class="fa-solid fa-life-ring"></i><div><strong><?= count($requests) ?></strong><span>Password requests</span></div></div>
    </div>


    <!-- PENDING REQUESTS -->
    <?php if (!empty($requests)): ?>
        <div class="uq-requests">
            <h3><i class="fa-solid fa-bell"></i> Password reset requests</h3>
            <?php foreach ($requests as $r): ?>
                <div class="uq-req">
                    <div class="uq-req-body">
                        <strong><?= $h($r['full_name'] !== '' ? $r['full_name'] : $r['username']) ?></strong>
                        <small>@<?= $h($r['username']) ?> · requested <?= $h(usersAgo($r['req_secs'], $r['reset_requested_at'])) ?></small>
                        <?php if ($r['reset_request_note'] !== ''): ?>
                            <span class="uq-req-note">"<?= $h($r['reset_request_note']) ?>"</span>
                        <?php endif; ?>
                    </div>
                    <button type="button" class="uq-btn warn" onclick="openResetModal(<?= (int)$r['user_id'] ?>, <?= $h(json_encode($r['full_name'] !== '' ? $r['full_name'] : $r['username'], $jsonFlags)) ?>)">
                        <i class="fa-solid fa-key"></i> Reset
                    </button>
                    <button type="button" class="uq-btn ghost" title="Dismiss request" onclick="postAction('dismiss_request', {user_id: <?= (int)$r['user_id'] ?>})">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>


    <!-- LIST -->
    <div class="uq-toolbar">
        <div class="uq-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="uqSearch" placeholder="Search name, email, department, username..." autocomplete="off">
        </div>
        <div class="uq-count" id="uqCount"><?= $totalFaculty ?> faculty</div>
    </div>

    <div class="uq-list" id="uqList">
        <?php if (empty($topFaculty)): ?>
            <div class="uq-empty">No core / adjunct / PoP faculty found. <a href="faculty.php">View all faculty</a></div>
        <?php endif; ?>

        <?php foreach ($topFaculty as $fac): ?>
            <?php
            $fullName  = trim($fac['name'] . (!empty($fac['lname']) ? ' ' . $fac['lname'] : ''));
            $hasLogin  = !empty($fac['user_id']);
            $isInner   = $fac['page_type'] === 'inner';
            $hasReq    = $hasLogin && !empty($fac['reset_requested_at']);
            $mustChange= $hasLogin && (int)$fac['must_change_password'] === 1;
            $cat       = (int)$fac['top_order'];
            ?>
            <div class="uq-card <?= $hasReq ? 'has-request' : '' ?>">

                <div class="uq-top">
                    <?php $thumb = resolveFacultyThumb($fac['image']); ?>
                    <?php if ($thumb !== ''): ?>
                        <img src="<?= $h($thumb) ?>" alt="">
                    <?php else: ?>
                        <span class="uq-avatar"><?= $h(usersInitials($fullName)) ?></span>
                    <?php endif; ?>
                    <div class="uq-id">
                        <div class="uq-name"><?= $h($fullName) ?></div>
                        <div class="uq-role"><?= $h($fac['designation']) ?><?= $fac['department'] ? ' · ' . $h($fac['department']) : '' ?></div>
                        <div class="uq-meta">
                            <?php if ($fac['email']): ?><span><i class="fa-regular fa-envelope"></i><?= $h($fac['email']) ?></span><?php endif; ?>
                        </div>
                    </div>
                    <div style="display:flex; flex-direction:column; gap:6px; align-items:flex-end;">
                        <?php if ($cat === 1): ?><span class="uq-chip chip-core">Core</span>
                        <?php elseif ($cat === 2): ?><span class="uq-chip chip-adjunct">Adjunct / Visiting</span>
                        <?php elseif ($cat === 3): ?><span class="uq-chip chip-pop">PoP</span><?php endif; ?>

                        <?php if ($isInner): ?>
                            <a class="uq-chip chip-inner" href="../innerfaculty.php?id=<?= (int)$fac['faculty_id'] ?>" target="_blank" style="text-decoration:none;" title="View live page">Inner page &#8599;</a>
                        <?php else: ?>
                            <span class="uq-chip chip-ext" title="<?= $h($fac['personal_page'] ?: 'No link set') ?>">External link</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="uq-login">
                    <?php if ($hasLogin): ?>
                        <div class="uq-login-row">
                            <span class="uq-user"><i class="fa-solid fa-user"></i><?= $h($fac['username']) ?></span>
                            <div class="uq-chips">
                                <?php if ($hasReq): ?><span class="uq-chip chip-req"><i class="fa-solid fa-bell"></i> Reset requested</span><?php endif; ?>
                                <?php if ($mustChange): ?><span class="uq-chip chip-must"><i class="fa-solid fa-hourglass-half"></i> Temp password</span><?php endif; ?>
                                <?php if ((int)$fac['user_status'] === 1): ?>
                                    <span class="uq-chip chip-active"><i class="fa-solid fa-circle-check"></i> Active</span>
                                <?php else: ?>
                                    <span class="uq-chip chip-off"><i class="fa-solid fa-ban"></i> Disabled</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="uq-sub">
                            <?= $fac['last_login_at'] ? 'Last login ' . $h(usersAgo($fac['login_secs'], $fac['last_login_at'])) : 'Never logged in' ?>
                            <?php if ($fac['password_changed_at']): ?> · Password changed <?= $h(usersAgo($fac['pwd_secs'], $fac['password_changed_at'])) ?><?php endif; ?>
                        </div>
                        <?php if ($hasReq && $fac['reset_request_note'] !== ''): ?>
                            <div class="uq-note">"<?= $h($fac['reset_request_note']) ?>"</div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="uq-login-row">
                            <span class="uq-chip chip-none"><i class="fa-regular fa-circle-xmark"></i> No portal login yet</span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="uq-actions">
                    <?php if ($hasLogin): ?>
                        <button type="button" class="uq-btn <?= $hasReq ? 'warn' : '' ?>" onclick="openResetModal(<?= (int)$fac['user_id'] ?>, <?= $h(json_encode($fullName, $jsonFlags)) ?>)">
                            <i class="fa-solid fa-key"></i> Reset password
                        </button>
                        <button type="button" class="uq-btn" onclick="openLoginModal(<?= (int)$fac['faculty_id'] ?>, <?= $h(json_encode($fullName, $jsonFlags)) ?>, <?= $h(json_encode($fac['username'], $jsonFlags)) ?>, <?= (int)$fac['user_status'] ?>)">
                            <i class="fa-solid fa-user-pen"></i> Edit login
                        </button>
                    <?php else: ?>
                        <button type="button" class="uq-btn primary" onclick="openLoginModal(<?= (int)$fac['faculty_id'] ?>, <?= $h(json_encode($fullName, $jsonFlags)) ?>, '', 1, true)">
                            <i class="fa-solid fa-plus"></i> Set up login
                        </button>
                    <?php endif; ?>

                    <a href="editfaculty.php?id=<?= (int)$fac['faculty_id'] ?>" class="uq-btn ghost"><i class="fa-solid fa-pen-to-square"></i> Edit info</a>
                    <button type="button" class="uq-btn ghost" onclick="toggleProfileType(<?= (int)$fac['faculty_id'] ?>, '<?= $isInner ? 'personal' : 'inner' ?>', <?= $h(json_encode($fullName, $jsonFlags)) ?>)">
                        <i class="fa-solid fa-right-left"></i> Use <?= $isInner ? 'external link' : 'inner page' ?>
                    </button>
                </div>

            </div>
        <?php endforeach; ?>

        <div class="uq-empty" id="uqNoResults" style="display:none;">No matching faculty or usernames.</div>
    </div>

</div>


<!-- CREATE / EDIT LOGIN MODAL -->
<div id="loginModal" class="delete-modal uq-modal">
    <div class="delete-modal-content">
        <h3 id="loginTitle">Faculty login</h3>
        <p class="lead" id="loginLead"></p>

        <form method="POST" action="users.php" onsubmit="return submitChunked(this)" autocomplete="off">
            <input type="hidden" name="csrf" value="<?= $h($csrf) ?>">
            <input type="hidden" name="action" value="save_user">
            <input type="hidden" id="lm_faculty_id" data-chunk="faculty_id" value="">

            <div class="uq-field" id="lmSelectWrap">
                <label for="lmSelect">Faculty member</label>
                <select id="lmSelect" onchange="document.getElementById('lm_faculty_id').value = this.value">
                    <option value="">Choose faculty without a login</option>
                    <?php foreach ($topFaculty as $f): ?>
                        <?php if (empty($f['user_id'])): ?>
                            <option value="<?= (int)$f['faculty_id'] ?>"><?= $h(trim($f['name'] . ' ' . $f['lname'])) ?><?= $f['department'] ? ' — ' . $h($f['department']) : '' ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="uq-field">
                <label for="lm_username">Username</label>
                <input type="text" id="lm_username" data-chunk="username" maxlength="50" pattern="[A-Za-z0-9._\-]{3,50}" required placeholder="e.g. ravindra or r.naik">
                <div class="uq-hint">3–50 characters: letters, numbers, dot, dash or underscore.</div>
            </div>

            <div class="uq-field" id="lmPassWrap">
                <label for="lm_temp">Temporary password <span style="font-weight:400; color:#94a3b8;">(optional)</span></label>
                <input type="text" id="lm_temp" data-chunk="temp_password" data-secret="1" maxlength="72" placeholder="Leave empty to generate one">
                <div class="uq-hint">You'll see it once after saving. The faculty member must change it at first login.</div>
            </div>

            <div class="uq-field">
                <label for="lm_status">Status</label>
                <select id="lm_status" data-chunk="status">
                    <option value="1">Active: can log in</option>
                    <option value="0">Disabled: login blocked</option>
                </select>
            </div>

            <div class="uq-hint" id="lmEditHint" style="margin:-4px 0 14px;">To change the password, use <strong>Reset password</strong> on the card.</div>

            <div class="chunks"></div>

            <div class="delete-modal-actions" style="justify-content:flex-end;">
                <button type="button" class="btn-cancel" onclick="closeModal('loginModal')">Cancel</button>
                <button type="submit" class="btn-confirm-delete" style="background:#184C74;">Save</button>
            </div>
        </form>
    </div>
</div>


<!-- RESET PASSWORD MODAL -->
<div id="resetModal" class="delete-modal uq-modal">
    <div class="delete-modal-content">
        <h3>Reset password</h3>
        <p class="lead">For <strong id="resetName" style="color:#0f172a;"></strong>. Their current password stops working immediately.</p>

        <form method="POST" action="users.php" onsubmit="return submitChunked(this)" autocomplete="off">
            <input type="hidden" name="csrf" value="<?= $h($csrf) ?>">
            <input type="hidden" name="action" value="reset_password">
            <input type="hidden" id="rm_user_id" data-chunk="user_id" value="">

            <div class="uq-radio">
                <label>
                    <input type="radio" name="rm_mode" value="auto" checked onchange="toggleCustom(false)">
                    <span><strong>Generate a temporary password</strong><small>Recommended. Shown to you once after resetting.</small></span>
                </label>
                <label>
                    <input type="radio" name="rm_mode" value="custom" onchange="toggleCustom(true)">
                    <span><strong>Type a temporary password</strong><small>At least 8 characters with a letter and a number.</small></span>
                </label>
            </div>

            <div class="uq-field" id="rmCustomWrap" style="display:none;">
                <input type="text" id="rm_custom" data-chunk="custom_password" data-secret="1" maxlength="72" placeholder="Temporary password">
            </div>

            <div class="uq-hint" style="margin-bottom:14px;"><i class="fa-solid fa-circle-info"></i> At their next login they'll be asked to set their own password before using the portal.</div>

            <div class="chunks"></div>

            <div class="delete-modal-actions" style="justify-content:flex-end;">
                <button type="button" class="btn-cancel" onclick="closeModal('resetModal')">Cancel</button>
                <button type="submit" class="btn-confirm-delete" style="background:#ea580c;"><i class="fa-solid fa-key"></i> Reset password</button>
            </div>
        </form>
    </div>
</div>


<!-- Generic hidden form for small actions -->
<form method="POST" action="users.php" id="actionForm" style="display:none;">
    <input type="hidden" name="csrf" value="<?= $h($csrf) ?>">
    <input type="hidden" name="action" id="actionName" value="">
    <div class="chunks"></div>
</form>


<script>
/* ---------- Chunked Base64 (same contract as the rest of the admin) ---------- */
function b64(str) { return btoa(unescape(encodeURIComponent(str))); }

function addChunks(box, name, value) {
    var enc = value === '' ? '' : b64(value);
    var count = Math.ceil(enc.length / 200);
    var c = document.createElement('input');
    c.type = 'hidden'; c.name = name + '_chunk_count'; c.value = count;
    box.appendChild(c);
    for (var i = 0; i < count; i++) {
        var inp = document.createElement('input');
        inp.type = 'hidden'; inp.name = name + '_chunk_' + i; inp.value = enc.substring(i * 200, (i + 1) * 200);
        box.appendChild(inp);
    }
}

function submitChunked(form) {
    if (form.querySelector('#lm_faculty_id') && !document.getElementById('lm_faculty_id').value) {
        alert('Please choose a faculty member.');
        return false;
    }
    var box = form.querySelector('.chunks');
    box.innerHTML = '';
    form.querySelectorAll('[data-chunk]').forEach(function (el) {
        addChunks(box, el.dataset.chunk, el.dataset.secret ? el.value : String(el.value).trim());
    });
    return true;
}

function postAction(action, fields) {
    var form = document.getElementById('actionForm');
    document.getElementById('actionName').value = action;
    var box = form.querySelector('.chunks');
    box.innerHTML = '';
    Object.keys(fields).forEach(function (k) { addChunks(box, k, String(fields[k])); });
    form.submit();
}

/* ---------- Modals ---------- */
function openLoginModal(facultyId, name, username, status, isNew) {
    var creating = !facultyId || isNew;
    document.getElementById('lm_faculty_id').value = facultyId || '';
    document.getElementById('lmSelectWrap').style.display = facultyId ? 'none' : 'block';
    document.getElementById('lmSelect').value = '';
    document.getElementById('lm_username').value = username || '';
    document.getElementById('lm_temp').value = '';
    document.getElementById('lm_status').value = String(status === 0 ? 0 : 1);
    document.getElementById('lmPassWrap').style.display = creating ? 'block' : 'none';
    document.getElementById('lmEditHint').style.display = creating ? 'none' : 'block';
    document.getElementById('loginTitle').textContent = creating ? 'Set up portal login' : 'Edit login';
    document.getElementById('loginLead').textContent = name ? ('For ' + name + '.') : 'Choose a faculty member who does not have a login yet.';
    document.getElementById('loginModal').classList.add('show');
}

function openResetModal(userId, name) {
    document.getElementById('rm_user_id').value = userId;
    document.getElementById('resetName').textContent = name;
    document.querySelector('input[name="rm_mode"][value="auto"]').checked = true;
    toggleCustom(false);
    document.getElementById('resetModal').classList.add('show');
}

function toggleCustom(on) {
    var input = document.getElementById('rm_custom');
    document.getElementById('rmCustomWrap').style.display = on ? 'block' : 'none';
    input.required = on;
    if (!on) input.value = '';
}

function closeModal(id) { document.getElementById(id).classList.remove('show'); }

document.querySelectorAll('.uq-modal').forEach(function (m) {
    m.addEventListener('click', function (e) { if (e.target === m) m.classList.remove('show'); });
});
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') document.querySelectorAll('.uq-modal.show').forEach(function (m) { m.classList.remove('show'); });
});

function toggleProfileType(facultyId, target, name) {
    var msg = target === 'inner'
        ? 'Switch ' + name + ' to the Inner Faculty Page?'
        : 'Switch ' + name + ' to an External Link?';
    if (confirm(msg)) postAction('toggle_page_type', { faculty_id: facultyId, target_type: target });
}

function copyTemp(btn) {
    var text = document.getElementById('tempPass').textContent.trim();
    var done = function () { btn.innerHTML = '<i class="fa-solid fa-check"></i> Copied'; };
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(done, function () { window.prompt('Copy the password:', text); });
    } else {
        window.prompt('Copy the password:', text);
    }
}

/* ---------- Search ---------- */
(function () {
    var input = document.getElementById('uqSearch');
    var cards = Array.from(document.querySelectorAll('#uqList .uq-card'));
    var count = document.getElementById('uqCount');
    var none  = document.getElementById('uqNoResults');
    input.addEventListener('input', function () {
        var q = this.value.toLowerCase().trim();
        var shown = 0;
        cards.forEach(function (c) {
            var ok = !q || c.textContent.toLowerCase().indexOf(q) !== -1;
            c.style.display = ok ? '' : 'none';
            if (ok) shown++;
        });
        count.textContent = shown + ' faculty';
        none.style.display = cards.length && shown === 0 ? 'block' : 'none';
    });
})();
</script>

<?php include 'footer.php'; ?>
