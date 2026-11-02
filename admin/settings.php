<?php
/*
|--------------------------------------------------------------------------
| Admin > Settings (own username & password)
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/account_helpers.php';

$user = accountFetchUser($pdo, $_SESSION['admin_id'], 'admin');

if (!$user) {
    header('Location: logout.php');
    exit;
}

// Handles the POST and redirects (admin messages kept separate from faculty)
accountRun($pdo, $user, 'account_flash_admin', 'admin_username', 'admin_must_change');

$forced = !empty($_SESSION['admin_must_change']);

$flash = accountTakeFlash('account_flash_admin');

include 'header.php';

?>

<div class="dashboard-content">

    <div class="page-header">
        <div>
            <h2>Settings</h2>
            <p>Your administrator login.</p>
        </div>
    </div>

    <?php accountRenderPage($user, $flash, $forced); ?>

</div>

<?php include 'footer.php'; ?>
