<?php include 'header.php'; ?>

<?php

require_once '../config.php';

$success = '';
$error = '';

/*
|--------------------------------------------------------------------------
| DECODE HELPER — Base64 or plain fallback
|--------------------------------------------------------------------------
*/

function decodeInput($value) {
    if (empty($value)) return '';

    // Try Base64 decode (strict)
    $decoded = base64_decode($value, true);

    if ($decoded !== false && $decoded !== '') {
        return trim($decoded);
    }

    // Fallback: return as plain text
    return trim($value);
}


/*
|--------------------------------------------------------------------------
| HANDLE FORM SUBMISSION
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id        = $_POST['id'] ?? '';
    $title     = decodeInput($_POST['title_encoded']      ?? '');
    $iframeUrl = decodeInput($_POST['iframe_url_encoded'] ?? '');

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if (empty($id) || empty($title) || empty($iframeUrl)) {

        $error = "Please fill in all the fields.";

    } else {

        // Validate URL format (must be http/https)
        if (!filter_var($iframeUrl, FILTER_VALIDATE_URL) ||
            !preg_match('/^https?:\/\//i', $iframeUrl)) {

            $error = "Please enter a valid URL starting with http:// or https://";

        } else {

            try {

                $stmt = $pdo->prepare("
                    UPDATE message_from_director
                    SET title = ?, iframe_url = ?
                    WHERE id = ?
                ");

                $stmt->execute([
                    $title,
                    $iframeUrl,
                    $id
                ]);

                $success = "Director message updated successfully.";

            } catch (PDOException $e) {

                error_log("DB Error: " . $e->getMessage());
                $error = "Database error. Please try again.";
            }
        }
    }
}


/*
|--------------------------------------------------------------------------
| FETCH LATEST DIRECTOR MESSAGE
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT id, title, iframe_url
    FROM message_from_director
    ORDER BY id DESC
    LIMIT 1
");

$stmt->execute();

$directorMessage = $stmt->fetch(PDO::FETCH_ASSOC);

?>


<div class="dashboard-content">

    <!-- PAGE HEADER -->

    <div class="page-header">

        <div>

            <h2>Message from Director</h2>

            <p>
                Manage the Director's message displayed on the SIDiS website.
            </p>

        </div>

    </div>


    <!-- SUCCESS MESSAGE -->

    <?php if (!empty($success)): ?>

        <div class="alert alert-success">
            <?= htmlspecialchars($success) ?>
        </div>

    <?php endif; ?>


    <!-- ERROR MESSAGE -->

    <?php if (!empty($error)): ?>

        <div class="alert alert-danger">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>


    <!-- DIRECTOR MESSAGE -->

    <div class="dashboard-card">

        <div class="card-header">

            <div>

                <h3>Director's Message</h3>

                <p>
                    Update the title and embedded message displayed on the website.
                </p>

            </div>

        </div>


        <?php if ($directorMessage): ?>

            <form method="POST" id="directorForm" onsubmit="return prepareSubmit()">

                <input
                    type="hidden"
                    name="id"
                    value="<?= htmlspecialchars($directorMessage['id']) ?>"
                >

                <!-- Base64-encoded hidden fields -->
                <input type="hidden" name="title_encoded"      id="title_encoded"      value="">
                <input type="hidden" name="iframe_url_encoded" id="iframe_url_encoded" value="">


                <!-- TITLE -->

                <div class="form-group">

                    <label for="director_title">
                        Title
                    </label>

                    <input
                        type="text"
                        id="director_title"
                        class="form-control"
                        value="<?= htmlspecialchars($directorMessage['title']) ?>"
                        oninput="syncField('director_title', 'title_encoded')"
                        required
                    >

                </div>


                <!-- IFRAME URL -->

                <div class="form-group">

                    <label for="iframe_url">
                        Iframe URL
                    </label>

                    <input
                        type="text"
                        id="iframe_url"
                        class="form-control"
                        value="<?= htmlspecialchars($directorMessage['iframe_url']) ?>"
                        oninput="syncField('iframe_url', 'iframe_url_encoded')"
                        placeholder="https://www.youtube.com/embed/..."
                        required
                    >

                    <small class="form-help">
                        Enter the URL of the video or embedded content.
                    </small>

                </div>


                <!-- PREVIEW -->

                <?php if (!empty($directorMessage['iframe_url'])): ?>

                    <div class="preview-box">

                        <h4>Current Preview</h4>

                        <iframe
                            src="<?= htmlspecialchars($directorMessage['iframe_url']) ?>"
                            width="100%"
                            height="400"
                            style="border:0;"
                            allow="autoplay">
                        </iframe>

                    </div>

                <?php endif; ?>


                <!-- UPDATE -->

                <div class="form-actions">

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Update Director Message
                    </button>

                </div>

            </form>

        <?php else: ?>

            <p>No Director message found.</p>

        <?php endif; ?>

    </div>

</div>


<script>
// UTF-8-safe Base64 encoder
function b64EncodeUtf8(str) {
    return btoa(unescape(encodeURIComponent(str)));
}

// Sync a visible input into its Base64 hidden field
function syncField(visibleId, hiddenId) {
    const visible = document.getElementById(visibleId);
    const hidden  = document.getElementById(hiddenId);

    if (visible && hidden) {
        hidden.value = b64EncodeUtf8(visible.value);
    }
}

// Before submit: ensure hidden fields are up-to-date
function prepareSubmit() {
    syncField('director_title', 'title_encoded');
    syncField('iframe_url',     'iframe_url_encoded');
    return true;
}

// On page load: populate hidden fields with existing values
document.addEventListener('DOMContentLoaded', function() {
    syncField('director_title', 'title_encoded');
    syncField('iframe_url',     'iframe_url_encoded');
});
</script>


<?php include 'footer.php'; ?>