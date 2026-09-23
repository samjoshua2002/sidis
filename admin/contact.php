<?php
/*
|--------------------------------------------------------------------------
| BOOTSTRAP — process BEFORE any output
|--------------------------------------------------------------------------
*/

require_once '../config.php';

$success = '';
$error   = '';

/*
|--------------------------------------------------------------------------
| DECODE HELPERS
|--------------------------------------------------------------------------
*/

function decodeInput($value) {
    if (empty($value)) return '';

    $decoded = base64_decode($value, true);

    if ($decoded !== false && $decoded !== '') {
        return trim($decoded);
    }

    return trim($value);
}

function reconstructChunks($fieldName) {
    $countField = $fieldName . '_chunk_count';

    if (!isset($_POST[$countField]) || (int)$_POST[$countField] === 0) {
        if (isset($_POST[$fieldName . '_encoded'])) {
            return decodeInput($_POST[$fieldName . '_encoded']);
        }
        if (isset($_POST[$fieldName])) {
            return trim($_POST[$fieldName]);
        }
        return '';
    }

    $chunkCount = (int)$_POST[$countField];
    $base64 = '';

    for ($i = 0; $i < $chunkCount; $i++) {
        $chunkName = $fieldName . '_chunk_' . $i;
        if (isset($_POST[$chunkName])) {
            $base64 .= $_POST[$chunkName];
        }
    }

    return decodeInput($base64);
}


/*
|--------------------------------------------------------------------------
| HANDLE FORM SUBMISSION
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id              = $_POST['id'] ?? '';
    $schoolName      = reconstructChunks('school_name');
    $institutionName = reconstructChunks('institution_name');
    $address         = reconstructChunks('address');
    $email           = reconstructChunks('email');
    $phone           = reconstructChunks('phone');
    $mapEmbedUrl     = reconstructChunks('map_embed_url');


    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if (
        empty($id) ||
        empty($schoolName) ||
        empty($institutionName) ||
        empty($address) ||
        empty($email) ||
        empty($phone) ||
        empty($mapEmbedUrl)
    ) {

        $error = "Please fill in all the fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } elseif (!filter_var($mapEmbedUrl, FILTER_VALIDATE_URL)) {

        $error = "Please enter a valid Google Maps embed URL.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | UPDATE CONTACT
        |--------------------------------------------------------------------------
        */

        try {

            $stmt = $pdo->prepare("
                UPDATE contact
                SET
                    school_name = ?,
                    institution_name = ?,
                    address = ?,
                    email = ?,
                    phone = ?,
                    map_embed_url = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $schoolName,
                $institutionName,
                $address,
                $email,
                $phone,
                $mapEmbedUrl,
                $id
            ]);

            $success = "Contact details updated successfully.";

        } catch (PDOException $e) {

            error_log("DB Error: " . $e->getMessage());
            $error = "Database error. Please try again.";

        }

    }

}


/*
|--------------------------------------------------------------------------
| FETCH CONTACT DETAILS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        id,
        school_name,
        institution_name,
        address,
        email,
        phone,
        map_embed_url
    FROM contact
    ORDER BY id DESC
    LIMIT 1
");

$stmt->execute();

$contact = $stmt->fetch(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| HEADER
|--------------------------------------------------------------------------
*/

include 'header.php';

?>


<div class="dashboard-content">


    <!-- PAGE HEADER -->

    <div class="page-header">

        <div>

            <h2>Contact</h2>

            <p>
                Manage the contact information displayed on the SIDiS website.
            </p>

        </div>

    </div>


    <!-- SUCCESS MESSAGE -->

    <?php if (!empty($success)): ?>

        <div class="alert alert-success">
            <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
        </div>

    <?php endif; ?>


    <!-- ERROR MESSAGE -->

    <?php if (!empty($error)): ?>

        <div class="alert alert-danger">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>

    <?php endif; ?>


    <!-- CONTACT DETAILS -->

    <div class="dashboard-card">

        <div class="card-header">

            <div>

                <h3>Contact Information</h3>

                <p>
                    Update the address, email, phone number and Google Maps location.
                </p>

            </div>

        </div>


        <?php if ($contact): ?>

            <form method="POST" id="contactForm" onsubmit="return prepareSubmit()">


                <input
                    type="hidden"
                    name="id"
                    value="<?= htmlspecialchars($contact['id']) ?>"
                >


                <!-- SCHOOL NAME (chunked Base64) -->

                <div class="form-group">

                    <label for="school_name">School Name</label>

                    <input
                        type="text"
                        id="school_name"
                        class="form-control"
                        value="<?= htmlspecialchars($contact['school_name'], ENT_QUOTES, 'UTF-8') ?>"
                        oninput="syncChunked('school_name')"
                        required
                    >

                    <input type="hidden" id="school_name_chunk_count" name="school_name_chunk_count" value="0">
                    <div id="school_name_chunks_container"></div>

                </div>


                <!-- INSTITUTION NAME (chunked Base64) -->

                <div class="form-group">

                    <label for="institution_name">Institution Name</label>

                    <input
                        type="text"
                        id="institution_name"
                        class="form-control"
                        value="<?= htmlspecialchars($contact['institution_name'], ENT_QUOTES, 'UTF-8') ?>"
                        oninput="syncChunked('institution_name')"
                        required
                    >

                    <input type="hidden" id="institution_name_chunk_count" name="institution_name_chunk_count" value="0">
                    <div id="institution_name_chunks_container"></div>

                </div>


                <!-- ADDRESS (chunked Base64) -->

                <div class="form-group">

                    <label for="address">Address</label>

                    <textarea
                        id="address"
                        class="form-control"
                        rows="5"
                        oninput="syncChunked('address')"
                        required
                    ><?= htmlspecialchars($contact['address'], ENT_QUOTES, 'UTF-8') ?></textarea>

                    <small class="form-help">
                        Enter each address line on a separate line.
                    </small>

                    <input type="hidden" id="address_chunk_count" name="address_chunk_count" value="0">
                    <div id="address_chunks_container"></div>

                </div>


                <!-- EMAIL (chunked Base64) -->

                <div class="form-group">

                    <label for="email">E-Mail</label>

                    <input
                        type="email"
                        id="email"
                        class="form-control"
                        value="<?= htmlspecialchars($contact['email'], ENT_QUOTES, 'UTF-8') ?>"
                        oninput="syncChunked('email')"
                        required
                    >

                    <input type="hidden" id="email_chunk_count" name="email_chunk_count" value="0">
                    <div id="email_chunks_container"></div>

                </div>


                <!-- PHONE (chunked Base64) -->

                <div class="form-group">

                    <label for="phone">Phone</label>

                    <input
                        type="text"
                        id="phone"
                        class="form-control"
                        value="<?= htmlspecialchars($contact['phone'], ENT_QUOTES, 'UTF-8') ?>"
                        oninput="syncChunked('phone')"
                        required
                    >

                    <input type="hidden" id="phone_chunk_count" name="phone_chunk_count" value="0">
                    <div id="phone_chunks_container"></div>

                </div>


                <!-- GOOGLE MAPS EMBED URL (chunked Base64) -->

                <div class="form-group">

                    <label for="map_embed_url">Google Maps Embed URL</label>

                    <textarea
                        id="map_embed_url"
                        class="form-control"
                        rows="4"
                        oninput="syncChunked('map_embed_url')"
                        required
                    ><?= htmlspecialchars($contact['map_embed_url'], ENT_QUOTES, 'UTF-8') ?></textarea>

                    <small class="form-help">
                        Paste only the Google Maps iframe "src" URL.
                    </small>

                    <input type="hidden" id="map_embed_url_chunk_count" name="map_embed_url_chunk_count" value="0">
                    <div id="map_embed_url_chunks_container"></div>

                </div>


                <!-- MAP PREVIEW -->

                <div class="preview-box">

                    <h4>Current Map Preview</h4>

                    <iframe
                        src="<?= htmlspecialchars($contact['map_embed_url'], ENT_QUOTES, 'UTF-8') ?>"
                        width="100%"
                        height="400"
                        style="border:0;"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="strict-origin-when-cross-origin">
                    </iframe>

                </div>


                <!-- UPDATE -->

                <div class="form-actions">

                    <button type="submit" class="btn btn-primary">
                        Update Contact Details
                    </button>

                </div>

            </form>

        <?php else: ?>

            <p>No contact information found.</p>

        <?php endif; ?>

    </div>

</div>


<script>

/* UTF-8-safe Base64 encoder */
function b64EncodeUtf8(str) {
    return btoa(unescape(encodeURIComponent(str)));
}

/* Split into fixed-size chunks */
function splitIntoChunks(text, chunkSize) {
    const chunks = [];
    for (let i = 0; i < text.length; i += chunkSize) {
        chunks.push(text.substring(i, i + chunkSize));
    }
    return chunks;
}

/* Sync a text field into chunked hidden inputs */
function syncChunked(fieldId) {

    const visible    = document.getElementById(fieldId);
    const countInput = document.getElementById(fieldId + '_chunk_count');
    const container  = document.getElementById(fieldId + '_chunks_container');

    if (!visible || !countInput || !container) return;

    const base64 = b64EncodeUtf8(visible.value);
    const chunks = splitIntoChunks(base64, 200);

    countInput.value = chunks.length;

    container.innerHTML = '';
    for (let i = 0; i < chunks.length; i++) {
        const inp = document.createElement('input');
        inp.type  = 'hidden';
        inp.name  = fieldId + '_chunk_' + i;
        inp.value = chunks[i];
        container.appendChild(inp);
    }

}

/* Encode all fields before submit */
function prepareSubmit() {
    syncChunked('school_name');
    syncChunked('institution_name');
    syncChunked('address');
    syncChunked('email');
    syncChunked('phone');
    syncChunked('map_embed_url');
    return true;
}

/* Initialize on page load */
document.addEventListener('DOMContentLoaded', function () {
    syncChunked('school_name');
    syncChunked('institution_name');
    syncChunked('address');
    syncChunked('email');
    syncChunked('phone');
    syncChunked('map_embed_url');
});

</script>


<?php include 'footer.php'; ?>