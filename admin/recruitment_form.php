<?php
/*
|--------------------------------------------------------------------------
| Shared form for addrecruitment.php / editrecruitment.php
|--------------------------------------------------------------------------
| Expects: $form (values), $structure (recruitmentStructure()), $formAction, $submitLabel
*/

$h = function ($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
};

$jsonFlags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
?>

<style>
    .form-section-title {
        font-size: 13px;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #184C74;
        border-bottom: 1px solid #E5E7EB;
        padding-bottom: 6px;
        margin: 26px 0 14px;
    }

    .form-section-title:first-of-type {
        margin-top: 0;
    }

    .form-hint {
        display: block;
        color: #6B7280;
        font-size: 12px;
        margin-top: 4px;
    }

    .type-choice {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .type-choice label {
        display: flex;
        align-items: center;
        gap: 8px;
        border: 1px solid #D1D5DB;
        border-radius: 8px;
        padding: 10px 16px;
        cursor: pointer;
        font-weight: 500;
        margin: 0;
    }

    .type-choice input:checked + span {
        color: #184C74;
        font-weight: 700;
    }

    .type-choice label:has(input:checked) {
        border-color: #184C74;
        background: #EFF5FB;
    }
</style>

<form method="POST" action="<?= $h($formAction) ?>" id="recruitmentForm" onsubmit="return prepareSubmit()">

    <!-- Page is fixed by the menu (Join Us > Faculty Recruitment / Careers) -->
    <input type="hidden" name="page" id="page" value="<?= $h($form['page']) ?>">


    <!-- ============ 1. TITLE ============ -->
    <div class="form-section-title">1. Title</div>

    <div class="form-group">
        <label for="title">Title <span class="required">*</span></label>
        <select name="title" id="title" class="form-control" required onchange="onTitleChange(false)">
            <option value="">Select Title</option>
        </select>
        <span class="form-hint">The main heading above the table, e.g. "IIT Madras – Faculty Recruitment".</span>
    </div>

    <div class="form-group" id="newTitleGroup" style="display:none;">
        <label for="new_title">New Title <span class="required">*</span></label>
        <input type="text" id="new_title" class="form-control" maxlength="255"
            value="<?= $h($form['new_title']) ?>"
            placeholder="e.g. IIT Madras – Faculty Recruitment (Special Drive)"
            oninput="syncChunked('new_title')">
        <input type="hidden" id="new_title_chunk_count" name="new_title_chunk_count" value="0">
        <div id="new_title_chunks_container"></div>
    </div>


    <!-- ============ 2. HEADING ============ -->
    <div class="form-section-title">2. Table Heading</div>

    <div class="form-group">
        <label for="heading">Heading <span class="required">*</span></label>
        <select name="heading" id="heading" class="form-control" required onchange="onHeadingChange()">
            <option value="">Select Title first</option>
        </select>
        <span class="form-hint">The dark bar inside the table, e.g. "SPECIALIZATION AREAS".</span>
    </div>

    <div class="form-group" id="newHeadingGroup" style="display:none;">
        <label for="new_heading">New Heading <span class="required">*</span></label>
        <input type="text" id="new_heading" class="form-control" maxlength="255"
            value="<?= $h($form['new_heading']) ?>"
            placeholder="e.g. ELIGIBILITY"
            oninput="syncChunked('new_heading')">
        <input type="hidden" id="new_heading_chunk_count" name="new_heading_chunk_count" value="0">
        <div id="new_heading_chunks_container"></div>
    </div>


    <!-- ============ 3. ROW ============ -->
    <div class="form-section-title">3. Row</div>

    <div class="form-group">
        <label for="label">Label (left column) <span class="required">*</span></label>
        <input type="text" id="label" class="form-control" maxlength="255"
            value="<?= $h($form['label']) ?>"
            placeholder="e.g. Advertisement, 1, Note, Application Portal"
            oninput="syncChunked('label')" required>
        <input type="hidden" id="label_chunk_count" name="label_chunk_count" value="0">
        <div id="label_chunks_container"></div>
    </div>

    <div class="form-group">
        <label>Type <span class="required">*</span></label>
        <div class="type-choice">
            <?php foreach (RECRUITMENT_ROW_TYPES as $typeKey => $typeLabel): ?>
                <label>
                    <input type="radio" name="row_type" value="<?= $h($typeKey) ?>"
                        <?= $form['row_type'] === $typeKey ? 'checked' : '' ?>
                        onchange="toggleRowType()" required>
                    <span><?= $h($typeLabel) ?></span>
                </label>
            <?php endforeach; ?>
        </div>
        <span class="form-hint">Text = shows text in the right column. Button = shows a green button (e.g. "Click Here").</span>
    </div>

    <!-- Type: Text -->
    <div id="textFields">
        <div class="form-group">
            <label for="content">Text (right column) <span class="required">*</span></label>
            <textarea id="content" class="form-control" rows="4"
                placeholder="e.g. Advt.No.IITM/R/1/2026 Dt 27.02.2026"
                oninput="syncChunked('content')"><?= $h($form['content']) ?></textarea>
            <input type="hidden" id="content_chunk_count" name="content_chunk_count" value="0">
            <div id="content_chunks_container"></div>
        </div>
    </div>

    <!-- Type: Button -->
    <div id="buttonFields">
        <div class="form-group">
            <label for="link_url">Button Link <span class="required">*</span></label>
            <input type="text" id="link_url" class="form-control" maxlength="500"
                value="<?= $h($form['link_url']) ?>"
                placeholder="https://..."
                oninput="syncChunked('link_url')">
            <input type="hidden" id="link_url_chunk_count" name="link_url_chunk_count" value="0">
            <div id="link_url_chunks_container"></div>
        </div>

        <div class="form-group">
            <label for="link_text">Button Text</label>
            <input type="text" id="link_text" class="form-control" maxlength="100"
                value="<?= $h($form['link_text']) ?>"
                placeholder="Click Here"
                oninput="syncChunked('link_text')">
            <input type="hidden" id="link_text_chunk_count" name="link_text_chunk_count" value="0">
            <div id="link_text_chunks_container"></div>
            <span class="form-hint">Leave empty to use "Click Here".</span>
        </div>
    </div>

    <div class="form-group">
        <label for="status">Status</label>
        <select name="status" id="status" class="form-control">
            <option value="1" <?= (int)$form['status'] === 1 ? 'selected' : '' ?>>Active</option>
            <option value="0" <?= (int)$form['status'] === 0 ? 'selected' : '' ?>>Inactive</option>
        </select>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn-confirm-delete" style="background: #184C74;">
            <i class="fas fa-save"></i>
            <?= $h($submitLabel) ?>
        </button>
        <a href="<?= $h(recruitmentListUrl($form['page'])) ?>" class="btn-cancel">Cancel</a>
    </div>

</form>


<script>

/* Titles of this page with their headings */
const RECRUITMENT_STRUCTURE = <?= json_encode($structure, $jsonFlags) ?>;
const INITIAL_TITLE   = <?= json_encode($form['title'], $jsonFlags) ?>;
const INITIAL_HEADING = <?= json_encode($form['heading'], $jsonFlags) ?>;
const CHUNKED_FIELDS  = ['new_title', 'new_heading', 'label', 'content', 'link_url', 'link_text'];

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
    CHUNKED_FIELDS.forEach(syncChunked);
    return true;
}

function addOption(select, value, text, selected, order) {
    const opt = document.createElement('option');
    opt.value = value;
    opt.textContent = text;
    if (order !== undefined) opt.dataset.order = order;
    if (selected) opt.selected = true;
    select.appendChild(opt);
}

function findTitle(title) {
    return RECRUITMENT_STRUCTURE.find(function (t) { return t.title === title; });
}

/* Dropdown 1: titles */
function loadTitles(selected) {
    const select = document.getElementById('title');
    select.innerHTML = '';
    addOption(select, '', 'Select Title', false);
    RECRUITMENT_STRUCTURE.forEach(function (t) {
        addOption(select, t.title, t.title, t.title === selected, t.title_order);
    });
    addOption(select, '__new__', '+ Add New Title', selected === '__new__');
}

/* Dropdown 2: headings of the selected title */
function loadHeadings(title, selected) {
    const select = document.getElementById('heading');
    select.innerHTML = '';

    if (!title) {
        addOption(select, '', 'Select Title first', true);
        return;
    }

    addOption(select, '', 'Select Heading', false);

    const t = findTitle(title);
    (t ? t.headings : []).forEach(function (hd) {
        addOption(select, hd.heading, hd.heading, hd.heading === selected, hd.heading_order);
    });

    /* A new title has no headings yet -> go straight to "+ Add New Heading" */
    addOption(select, '__new__', '+ Add New Heading', selected === '__new__' || title === '__new__');
}

function onTitleChange(initial) {
    const title  = document.getElementById('title').value;
    const group  = document.getElementById('newTitleGroup');
    const input  = document.getElementById('new_title');

    if (title === '__new__') {
        group.style.display = 'block';
        input.required = true;
    } else {
        group.style.display = 'none';
        input.required = false;
        input.value = '';
        syncChunked('new_title');
    }

    loadHeadings(title, initial ? INITIAL_HEADING : '');
    onHeadingChange();
}

function onHeadingChange() {
    const heading = document.getElementById('heading').value;
    const group   = document.getElementById('newHeadingGroup');
    const input   = document.getElementById('new_heading');

    if (heading === '__new__') {
        group.style.display = 'block';
        input.required = true;
    } else {
        group.style.display = 'none';
        input.required = false;
        input.value = '';
        syncChunked('new_heading');
    }
}

/* Type: show Text fields or Button fields */
function toggleRowType() {
    const checked  = document.querySelector('input[name="row_type"]:checked');
    const type     = checked ? checked.value : '';
    const isButton = type === 'button';
    const isText   = type === 'text';

    document.getElementById('textFields').style.display   = isText ? 'block' : 'none';
    document.getElementById('buttonFields').style.display = isButton ? 'block' : 'none';

    document.getElementById('content').required  = isText;
    document.getElementById('link_url').required = isButton;
}

/* Initialize */
document.addEventListener('DOMContentLoaded', function () {
    loadTitles(INITIAL_TITLE);
    onTitleChange(true);
    toggleRowType();
    CHUNKED_FIELDS.forEach(syncChunked);
});

</script>
