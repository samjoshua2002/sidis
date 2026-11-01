<?php
/*
|--------------------------------------------------------------------------
| Join Us (Faculty Recruitment / Careers) — shared admin helpers
|--------------------------------------------------------------------------
| Structure on the website:
|   Title   (e.g. "IIT Madras – Faculty Recruitment")      title / title_order
|     Heading (dark bar, e.g. "SPECIALIZATION AREAS")       heading / heading_order
|       Row  (label + Text  OR  label + Button)             row_type / display_order
*/

/* Pages stored in recruitment.page => label shown in admin */
const RECRUITMENT_PAGES = [
    'faculty' => 'Faculty Recruitment',
    'careers' => 'Careers',
];

/* Separate admin list page (sidebar menu item) for each recruitment.page */
const RECRUITMENT_LIST_FILES = [
    'faculty' => 'faculty_recruitment.php',
    'careers' => 'careers.php',
];

/* Row types */
const RECRUITMENT_ROW_TYPES = [
    'text'   => 'Text',
    'button' => 'Button',
];

function recruitmentListUrl($page, $query = '')
{
    $file = RECRUITMENT_LIST_FILES[$page] ?? RECRUITMENT_LIST_FILES['faculty'];
    return $file . ($query !== '' ? '?' . $query : '');
}

/*
|--------------------------------------------------------------------------
| Chunked Base64 decoding (same contract as editms.php)
|--------------------------------------------------------------------------
| Browser sends:  <field>_chunk_count = N  and  <field>_chunk_0 .. _chunk_{N-1}
| Each chunk is a 200-char slice of base64(utf8(value)). Server joins and decodes.
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
            if (!isset($_POST[$chunkName])) {
                // A missing chunk means the payload was truncated (e.g. max_input_vars) – reject.
                return '';
            }
            $base64 .= $_POST[$chunkName];
        }

        return decodeInput($base64);
    }
}

/* Only http(s), mailto, or site-relative links (blocks javascript:, data:, ...) */
function recruitmentUrlIsSafe($url)
{
    if ($url === '') return true;
    if (preg_match('#^(https?://|mailto:)#i', $url)) return true;
    return !preg_match('#^[a-z][a-z0-9+.\-]*:#i', $url);
}

/*
| Titles of one page with their headings:
| [ ['title' => .., 'title_order' => .., 'headings' => [['heading' => .., 'heading_order' => ..], ...]], ... ]
*/
function recruitmentStructure(PDO $pdo, $page)
{
    $titles = [];

    try {
        $stmt = $pdo->prepare("
            SELECT title, MIN(title_order) AS title_order, heading, MIN(heading_order) AS heading_order
            FROM recruitment
            WHERE page = ? AND TRIM(title) <> '' AND TRIM(heading) <> ''
            GROUP BY title, heading
            ORDER BY MIN(title_order), title, MIN(heading_order), heading
        ");
        $stmt->execute([$page]);

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $t = $r['title'];
            if (!isset($titles[$t])) {
                $titles[$t] = ['title' => $t, 'title_order' => (int)$r['title_order'], 'headings' => []];
            }
            $titles[$t]['title_order'] = min($titles[$t]['title_order'], (int)$r['title_order']);
            $titles[$t]['headings'][] = ['heading' => $r['heading'], 'heading_order' => (int)$r['heading_order']];
        }
    } catch (PDOException $e) {
        error_log('Recruitment structure error: ' . $e->getMessage());
    }

    return array_values($titles);
}

/*
| Reads + validates the POSTed form. $_POST['page'] must already be fixed by the caller.
| Returns [$data, $error].
*/
function recruitmentReadPost(PDO $pdo, $currentId = 0)
{
    $data = [
        'page'          => trim($_POST['page'] ?? ''),
        'title'         => trim($_POST['title'] ?? ''),
        'new_title'     => reconstructChunks('new_title'),
        'heading'       => trim($_POST['heading'] ?? ''),
        'new_heading'   => reconstructChunks('new_heading'),
        'label'         => reconstructChunks('label'),
        'row_type'      => trim($_POST['row_type'] ?? ''),
        'content'       => reconstructChunks('content'),
        'link_url'      => reconstructChunks('link_url'),
        'link_text'     => reconstructChunks('link_text'),
        'status'        => (($_POST['status'] ?? '1') === '0') ? 0 : 1,
    ];

    if (!isset(RECRUITMENT_PAGES[$data['page']])) {
        return [$data, 'Invalid page.'];
    }

    /* ---- Title ---- */
    $data['final_title'] = $data['title'] === '__new__' ? $data['new_title'] : $data['title'];

    if ($data['title'] === '__new__' && $data['new_title'] === '') {
        return [$data, 'Please enter the new title.'];
    }
    if ($data['final_title'] === '') {
        return [$data, 'Please select or enter a title.'];
    }
    if (mb_strlen($data['final_title']) > 255) {
        return [$data, 'Title must be 255 characters or fewer.'];
    }

    /* ---- Heading ---- */
    $data['final_heading'] = $data['heading'] === '__new__' ? $data['new_heading'] : $data['heading'];

    if ($data['heading'] === '__new__' && $data['new_heading'] === '') {
        return [$data, 'Please enter the new heading.'];
    }
    if ($data['final_heading'] === '') {
        return [$data, 'Please select or enter a heading.'];
    }
    if (mb_strlen($data['final_heading']) > 255) {
        return [$data, 'Heading must be 255 characters or fewer.'];
    }

    /* ---- Row ---- */
    if ($data['label'] === '') {
        return [$data, 'Please enter the label (left column).'];
    }
    if (mb_strlen($data['label']) > 255) {
        return [$data, 'Label must be 255 characters or fewer.'];
    }
    if (!isset(RECRUITMENT_ROW_TYPES[$data['row_type']])) {
        return [$data, 'Please choose the type: Text or Button.'];
    }

    if ($data['row_type'] === 'text') {
        if ($data['content'] === '') {
            return [$data, 'Please enter the text.'];
        }
        $data['link_url']  = '';
        $data['link_text'] = '';
    } else {
        if ($data['link_url'] === '') {
            return [$data, 'Please enter the button link.'];
        }
        if (mb_strlen($data['link_url']) > 500) {
            return [$data, 'Button link must be 500 characters or fewer.'];
        }
        if (!recruitmentUrlIsSafe($data['link_url'])) {
            return [$data, 'Button link must start with http://, https:// or mailto:'];
        }
        if ($data['link_text'] === '') {
            $data['link_text'] = 'Click Here';
        }
        if (mb_strlen($data['link_text']) > 100) {
            return [$data, 'Button text must be 100 characters or fewer.'];
        }
        $data['content'] = '';
    }

    /*
    | ---- Orders are automatic (re-ordering is drag & drop on the list page) ----
    | Title  : existing title keeps its position, a new title goes to the bottom of the page.
    | Heading: existing heading keeps its position, a new heading goes to the bottom of its title.
    | Row    : an edited row that stays in the same title+heading keeps its position,
    |          otherwise it goes to the bottom of its heading.
    */
    $page    = $data['page'];
    $title   = $data['final_title'];
    $heading = $data['final_heading'];
    $id      = (int)$currentId;

    $stmt = $pdo->prepare("SELECT MIN(title_order) FROM recruitment WHERE page = ? AND title = ? AND id <> ?");
    $stmt->execute([$page, $title, $id]);
    $order = $stmt->fetchColumn();
    if ($order === null || $order === false) {
        $stmt = $pdo->prepare("SELECT COALESCE(MAX(title_order), 0) + 1 FROM recruitment WHERE page = ? AND id <> ?");
        $stmt->execute([$page, $id]);
        $order = $stmt->fetchColumn();
    }
    $data['title_order'] = (int)$order;

    $stmt = $pdo->prepare("SELECT MIN(heading_order) FROM recruitment WHERE page = ? AND title = ? AND heading = ? AND id <> ?");
    $stmt->execute([$page, $title, $heading, $id]);
    $order = $stmt->fetchColumn();
    if ($order === null || $order === false) {
        $stmt = $pdo->prepare("SELECT COALESCE(MAX(heading_order), 0) + 1 FROM recruitment WHERE page = ? AND title = ? AND id <> ?");
        $stmt->execute([$page, $title, $id]);
        $order = $stmt->fetchColumn();
    }
    $data['heading_order'] = (int)$order;

    $order = null;
    if ($id > 0) {
        $stmt = $pdo->prepare("SELECT display_order FROM recruitment WHERE id = ? AND page = ? AND title = ? AND heading = ?");
        $stmt->execute([$id, $page, $title, $heading]);
        $order = $stmt->fetchColumn();
    }
    if ($order === null || $order === false) {
        $stmt = $pdo->prepare("
            SELECT COALESCE(MAX(display_order), 0) + 1
            FROM recruitment
            WHERE page = ? AND title = ? AND heading = ? AND id <> ?
        ");
        $stmt->execute([$page, $title, $heading, $id]);
        $order = $stmt->fetchColumn();
    }
    $data['display_order'] = (int)$order;

    return [$data, ''];
}

/* Keep every row of a title / heading on the same order number */
function recruitmentSyncOrders(PDO $pdo, array $data)
{
    $stmt = $pdo->prepare("UPDATE recruitment SET title_order = ? WHERE page = ? AND title = ?");
    $stmt->execute([$data['title_order'], $data['page'], $data['final_title']]);

    $stmt = $pdo->prepare("UPDATE recruitment SET heading_order = ? WHERE page = ? AND title = ? AND heading = ?");
    $stmt->execute([$data['heading_order'], $data['page'], $data['final_title'], $data['final_heading']]);
}
