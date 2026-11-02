<?php
/*
|--------------------------------------------------------------------------
| Faculty Portal > Settings (own username & password)
|--------------------------------------------------------------------------
| The form is handled BEFORE header.php is included: the portal header
| prints a lot of CSS, which would otherwise be sent before the redirect.
*/

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../account_helpers.php';

$user = accountFetchUser($pdo, $_SESSION['faculty_user_id'], 'faculty');

if (!$user) {
    header('Location: logout.php');
    exit;
}

// Handles the POST and redirects (faculty messages kept separate from admin)
accountRun($pdo, $user, 'account_flash_faculty', 'faculty_username', 'faculty_must_change');

$forced = !empty($_SESSION['faculty_must_change']);

$flash = accountTakeFlash('account_flash_faculty');

include 'header.php';

?>

<div class="content-header">
    <div class="content-header-title">
        <h1>Settings</h1>
        <p>Your faculty portal login.</p>
    </div>
</div>

<?php accountRenderPage($user, $flash, $forced); ?>

<?php include 'footer.php'; ?>
