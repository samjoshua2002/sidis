<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config.php';

/*
|--------------------------------------------------------------------------
| BASE64 & CHUNKING DECODE HELPERS
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

$success = '';
$error = '';

/*
|--------------------------------------------------------------------------
| TOGGLE PROFILE PAGE TYPE (INNER vs PERSONAL)
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_page_type') {
    $facultyId = (int)reconstructChunks('faculty_id');
    $targetType = reconstructChunks('target_type');

    if ($facultyId > 0 && in_array($targetType, ['inner', 'personal'], true)) {
        try {
            $stmt = $pdo->prepare("UPDATE faculty SET page_type = :pt WHERE id = :id AND top_order > 0");
            $stmt->execute([':pt' => $targetType, ':id' => $facultyId]);
            $success = 'Profile type updated to ' . ($targetType === 'inner' ? 'Inner Faculty Page' : 'External Link') . '.';
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

/*
|--------------------------------------------------------------------------
| SAVE / UPDATE USER LOGIN
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_user') {
    $facultyId = (int)reconstructChunks('faculty_id');
    $username  = reconstructChunks('username');
    $password  = reconstructChunks('password');
    $status    = (int)reconstructChunks('status');

    if ($facultyId <= 0) {
        $error = 'Please select a valid faculty member.';
    } elseif ($username === '') {
        $error = 'Please enter a valid username.';
    } else {
        try {
            // Check if username is taken by another user
            $checkStmt = $pdo->prepare("SELECT id FROM users WHERE username = :u AND (faculty_id != :fid OR faculty_id IS NULL) LIMIT 1");
            $checkStmt->execute([':u' => $username, ':fid' => $facultyId]);
            if ($checkStmt->fetch()) {
                $error = 'The username "' . htmlspecialchars($username) . '" is already taken.';
            } else {
                // Check if user already exists for this faculty
                $existingStmt = $pdo->prepare("SELECT id FROM users WHERE faculty_id = :fid LIMIT 1");
                $existingStmt->execute([':fid' => $facultyId]);
                $existingUser = $existingStmt->fetch(PDO::FETCH_ASSOC);

                if ($existingUser) {
                    if ($password !== '') {
                        $hash = password_hash($password, PASSWORD_DEFAULT);
                        $updateStmt = $pdo->prepare("
                            UPDATE users 
                            SET username = :u, password = :p, status = :s, role = 'faculty', updated_at = NOW() 
                            WHERE id = :id
                        ");
                        $updateStmt->execute([
                            ':u' => $username,
                            ':p' => $hash,
                            ':s' => $status,
                            ':id' => $existingUser['id']
                        ]);
                    } else {
                        $updateStmt = $pdo->prepare("
                            UPDATE users 
                            SET username = :u, status = :s, role = 'faculty', updated_at = NOW() 
                            WHERE id = :id
                        ");
                        $updateStmt->execute([
                            ':u' => $username,
                            ':s' => $status,
                            ':id' => $existingUser['id']
                        ]);
                    }
                    $success = 'Faculty login credentials updated successfully.';
                } else {
                    if ($password === '') {
                        $error = 'Please enter a password for the new faculty login.';
                    } else {
                        $hash = password_hash($password, PASSWORD_DEFAULT);
                        $insertStmt = $pdo->prepare("
                            INSERT INTO users (username, password, role, faculty_id, status, created_at, updated_at) 
                            VALUES (:u, :p, 'faculty', :fid, :s, NOW(), NOW())
                        ");
                        $insertStmt->execute([
                            ':u' => $username,
                            ':p' => $hash,
                            ':fid' => $facultyId,
                            ':s' => $status
                        ]);
                        $success = 'Faculty login credentials created successfully.';
                    }
                }
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}


/*
|--------------------------------------------------------------------------
| FETCH TOP-ORDER FACULTY
|--------------------------------------------------------------------------
*/
$query = "
    SELECT 
        f.id AS faculty_id,
        f.name,
        f.lname,
        f.designation,
        f.department,
        f.email,
        f.office,
        f.phone,
        f.image,
        f.top_order,
        COALESCE(f.page_type, 'personal') AS page_type,
        f.personal_page,
        u.id AS user_id,
        u.username,
        u.status AS user_status,
        u.updated_at AS user_last_updated
    FROM faculty f
    LEFT JOIN users u ON u.faculty_id = f.id AND u.role = 'faculty'
    WHERE f.top_order > 0 AND f.status = 1
    ORDER BY f.top_order ASC, f.id ASC
";
$topFaculty = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);

function resolveFacultyThumb($image) {
    if (empty($image)) return '../images/faculty/dummy.png';
    $clean = trim($image);
    if (file_exists(__DIR__ . '/../images/faculty/' . $clean)) {
        return '../images/faculty/' . $clean;
    }
    return '../images/faculty/dummy.png';
}

include 'header.php';
?>

<div class="dashboard-content">
    <!-- PAGE HEADER -->
    <div class="page-header">
        <div>
            <h2>Users & Faculty Logins</h2>
            <p>Manage portal login accounts, credentials, and profile page types for SIDIS top-order faculty.</p>
        </div>
        <div>
            <button type="button" class="btn-add" onclick="openAddUserModal()">
                + Create Faculty Login
            </button>
        </div>
    </div>

    <!-- ALERTS -->
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <!-- USERS CARD -->
    <div class="dashboard-card">
        <div class="card-header">
            <div>
                <h3>Top-Order Faculty Portal Accounts</h3>
                <p>View profile options, faculty portal accounts, or switch between Inner Profile Page and External Link.</p>
            </div>
        </div>

        <?php if (!empty($topFaculty)): ?>
            <!-- SEARCH TOOLBAR -->
            <div class="table-toolbar">
                <div class="table-search">
                    <input
                        type="text"
                        id="userSearch"
                        class="table-search-input"
                        placeholder="Search faculty name, email, department, username..."
                        autocomplete="off"
                    >
                    <span class="table-search-icon">&#128269;</span>
                </div>
                <div id="userSearchCount" class="search-result-count"></div>
            </div>

            <!-- TABLE -->
            <div class="table-wrapper">
                <table class="data-table" id="userTable">
                    <thead>
                        <tr>
                            <th style="width: 70px;">Photo</th>
                            <th>Faculty Member</th>
                            <th>Designation & Dept</th>
                            <th style="width: 175px;">Profile Type</th>
                            <th style="width: 160px;">Username</th>
                            <th style="width: 110px;">Status</th>
                            <th style="width: 220px;">Actions</th>
                        </tr>
                    </thead>

                    <tbody id="userTableBody">
                        <?php foreach ($topFaculty as $fac): ?>
                            <?php
                            $thumb = resolveFacultyThumb($fac['image']);
                            $isInner = ($fac['page_type'] === 'inner');
                            $hasLogin = !empty($fac['username']) || (!empty($fac['user_id']) && (int)$fac['user_id'] > 0);
                            $fullName = trim($fac['name'] . (!empty($fac['lname']) ? ' ' . $fac['lname'] : ''));
                            ?>
                            <tr class="user-row" 
                                data-faculty-id="<?= (int)$fac['faculty_id'] ?>"
                                data-faculty-name="<?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') ?>"
                                data-username="<?= htmlspecialchars($fac['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                data-status="<?= $fac['user_status'] !== null ? (int)$fac['user_status'] : 1 ?>"
                                data-user-id="<?= (int)($fac['user_id'] ?? 0) ?>"
                            >
                                <!-- PHOTO -->
                                <td>
                                    <img
                                        src="<?= htmlspecialchars($thumb, ENT_QUOTES, 'UTF-8') ?>"
                                        alt=""
                                        style="width: 52px; height: 52px; object-fit: cover; border-radius: 6px; border: 1px solid #ddd;"
                                    >
                                </td>

                                <!-- FACULTY MEMBER -->
                                <td>
                                    <strong style="color: #184C74; font-size: 15px;">
                                        <?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') ?>
                                    </strong>
                                    <?php
                                    $catOrder = (int)($fac['top_order'] ?? 1);
                                    if ($catOrder === 1) {
                                        echo '<span style="display:inline-block; font-size:11px; font-weight:600; padding:2px 8px; border-radius:4px; background:#d4edda; color:#155724; margin-left:6px;">Core Faculty</span>';
                                    } elseif ($catOrder === 2) {
                                        echo '<span style="display:inline-block; font-size:11px; font-weight:600; padding:2px 8px; border-radius:4px; background:#e0e7ff; color:#3730a3; margin-left:6px;">Adjunct / Visiting</span>';
                                    } elseif ($catOrder === 3) {
                                        echo '<span style="display:inline-block; font-size:11px; font-weight:600; padding:2px 8px; border-radius:4px; background:#fef3c7; color:#92400e; margin-left:6px;">Prof. of Practice (PoP)</span>';
                                    }
                                    ?>
                                    <div style="font-size: 12px; color: #555; margin-top: 3px;">
                                        <i class="fa-regular fa-envelope" style="margin-right: 4px; color:#184C74;"></i><?= htmlspecialchars($fac['email']) ?>
                                    </div>
                                    <?php if (!empty($fac['office'])): ?>
                                        <div style="font-size: 11px; color: #777; margin-top: 2px;">
                                            <i class="fa-regular fa-building" style="margin-right: 4px;"></i><?= htmlspecialchars($fac['office']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <!-- DESIGNATION & DEPT -->
                                <td>
                                    <div style="font-weight: 500; font-size: 13px; color: #333;">
                                        <?= htmlspecialchars($fac['designation']) ?>
                                    </div>
                                    <div style="font-size: 12px; color: #666; margin-top: 2px;">
                                        <?= htmlspecialchars($fac['department']) ?>
                                    </div>
                                </td>

                                <!-- PROFILE TYPE -->
                                <td>
                                    <?php if ($isInner): ?>
                                        <span style="display:inline-block; padding: 4px 10px; border-radius: 4px; background: #f0fdf4; color: #15803d; font-size: 12px; font-weight: 600; border: 1px solid #bbf7d0;">
                                            Inner Faculty Page
                                        </span>
                                        <div style="margin-top: 5px;">
                                            <a href="../innerfaculty.php?id=<?= (int)$fac['faculty_id'] ?>" target="_blank" style="font-size: 12px; color: #184C74; font-weight: 500;">
                                                View Live Page &#8599;
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <span style="display:inline-block; padding: 4px 10px; border-radius: 4px; background: #e0f2fe; color: #0369a1; font-size: 12px; font-weight: 600; border: 1px solid #bae6fd;">
                                            External Link
                                        </span>
                                        <?php if (!empty($fac['personal_page']) && $fac['personal_page'] !== '#'): ?>
                                            <div style="margin-top: 5px; max-width: 170px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                <a href="<?= htmlspecialchars($fac['personal_page'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" style="font-size: 12px; color: #0369a1;" title="<?= htmlspecialchars($fac['personal_page'], ENT_QUOTES, 'UTF-8') ?>">
                                                    <?= htmlspecialchars($fac['personal_page'], ENT_QUOTES, 'UTF-8') ?> &#8599;
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <div style="font-size: 11px; color: #94a3b8; font-style: italic; margin-top: 3px;">No link set</div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>

                                <!-- USERNAME (PORTAL LOGIN) -->
                                <td>
                                    <?php if ($hasLogin): ?>
                                        <div style="font-family: monospace; font-size: 13.5px; font-weight: 700; color: #184C74; background: #f0f7ff; padding: 4px 10px; border-radius: 6px; border: 1px solid #bfdbfe; display: inline-flex; align-items: center; gap: 6px;">
                                            <i class="fa-solid fa-user" style="font-size: 11px; color: #184C74;"></i>
                                            <span><?= htmlspecialchars($fac['username']) ?></span>
                                        </div>
                                    <?php else: ?>
                                        <span style="display: inline-flex; align-items: center; gap: 5px; padding: 4px 9px; border-radius: 4px; background: #f8fafc; color: #94a3b8; font-size: 12px; font-style: italic; border: 1px solid #e2e8f0;">
                                            <i class="fa-regular fa-circle-xmark"></i> Not Created
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- STATUS -->
                                <td>
                                    <?php if ($hasLogin): ?>
                                        <span style="display:inline-flex; align-items:center; gap:5px; padding: 4px 9px; border-radius: 4px; background: #dcfce7; color: #15803d; font-size: 12px; font-weight: 600; border: 1px solid #bbf7d0;">
                                            <i class="fa-solid fa-circle-check" style="font-size: 11px;"></i> Password Set
                                        </span>
                                    <?php else: ?>
                                        <span style="display:inline-flex; align-items:center; gap:5px; padding: 4px 9px; border-radius: 4px; background: #fef2f2; color: #b91c1c; font-size: 12px; font-weight: 600; border: 1px solid #fecaca;">
                                            <i class="fa-solid fa-circle-xmark" style="font-size: 11px;"></i> Not Set
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- ACTIONS -->
                                <td>
                                    <div class="table-actions">
                                        <button
                                            type="button"
                                            class="btn-edit"
                                            onclick="openCredentialsModal(
                                                <?= (int)$fac['faculty_id'] ?>,
                                                <?= htmlspecialchars(json_encode($fullName), ENT_QUOTES, 'UTF-8') ?>,
                                                <?= htmlspecialchars(json_encode($fac['username'] ?? ''), ENT_QUOTES, 'UTF-8') ?>,
                                                <?= $fac['user_status'] !== null ? (int)$fac['user_status'] : 1 ?>,
                                                <?= (int)($fac['user_id'] ?? 0) ?>
                                            )"
                                        >
                                            <?= $hasLogin ? 'Edit Login' : '+ Set Login' ?>
                                        </button>


                                        <a
                                            href="editfaculty.php?id=<?= (int)$fac['faculty_id'] ?>"
                                            class="btn-edit"
                                            style="background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1;"
                                            title="Edit Faculty Master Data"
                                        >
                                            Edit Info
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- NO SEARCH RESULTS -->
            <div id="userNoResults" class="table-empty" style="display:none;">
                <p>No matching faculty or usernames found.</p>
            </div>

            <!-- PAGINATION (matching clusters.php) -->
            <div id="userPagination" class="table-pagination"></div>

        <?php else: ?>
            <div class="table-empty">
                <p>No top-order faculty members found.</p>
                <a href="faculty.php" class="btn-add">View All Faculty</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- CREDENTIALS MODAL -->
<div id="credentialsModal" class="delete-modal">
    <div class="delete-modal-content" style="max-width: 480px; text-align: left; padding: 25px 30px;">
        <h3 id="modalTitle" style="margin-top: 0; color: #184C74; font-size: 20px;">Faculty Login Credentials</h3>
        <p style="color: #666; font-size: 13px; margin-bottom: 20px;">
            Set up username and password for <strong id="modalFacultyName" style="color: #111;"></strong> to access the dedicated Faculty Portal.
        </p>

        <form method="POST" action="users.php" id="credentialsForm" onsubmit="return prepareCredentialsSubmit()">
            <input type="hidden" name="action" value="save_user">

            <!-- FACULTY ID (chunked) -->
            <input type="hidden" id="faculty_id" value="">
            <input type="hidden" id="faculty_id_chunk_count" name="faculty_id_chunk_count" value="0">
            <div id="faculty_id_chunks_container"></div>

            <!-- FACULTY SELECT (shown only if creating new login from top button) -->
            <div class="form-group" id="facultySelectGroup" style="margin-bottom: 15px; display: none;">
                <label for="select_faculty" style="display: block; font-weight: 600; margin-bottom: 6px; font-size: 14px;">Select Faculty</label>
                <select id="select_faculty" class="form-control" style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px;" onchange="onSelectFacultyChange(this.value)">
                    <option value="">-- Choose Faculty Member --</option>
                    <?php foreach ($topFaculty as $f): ?>
                        <?php
                        $catLabel = 'Core';
                        if ((int)$f['top_order'] === 2) $catLabel = 'Adjunct/Visiting';
                        elseif ((int)$f['top_order'] === 3) $catLabel = 'PoP';
                        ?>
                        <option value="<?= (int)$f['faculty_id'] ?>" data-name="<?= htmlspecialchars($f['name'], ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars($f['name'] . ' [' . $catLabel . ' - ' . $f['department'] . ']') ?>
                            <?= !empty($f['username']) ? ' [Account: ' . htmlspecialchars($f['username']) . ']' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- USERNAME (chunked) -->
            <div class="form-group" style="margin-bottom: 15px;">
                <label for="modalUsername" style="display: block; font-weight: 600; margin-bottom: 6px; font-size: 14px;">Username</label>
                <input
                    type="text"
                    id="modalUsername"
                    class="form-control"
                    placeholder="e.g. ravindra or r.naik"
                    style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px;"
                    oninput="syncChunkedField('modalUsername', 'username')"
                    required
                >
                <input type="hidden" id="username_chunk_count" name="username_chunk_count" value="0">
                <div id="username_chunks_container"></div>
            </div>

            <!-- PASSWORD (chunked) -->
            <div class="form-group" style="margin-bottom: 15px;">
                <label for="modalPassword" style="display: block; font-weight: 600; margin-bottom: 6px; font-size: 14px;">Password</label>
                <input
                    type="password"
                    id="modalPassword"
                    class="form-control"
                    placeholder="Enter password (leave blank to keep existing)"
                    style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px;"
                    oninput="syncChunkedField('modalPassword', 'password')"
                >
                <small id="passwordHelp" style="color: #64748b; font-size: 12px; display: block; margin-top: 4px;">Leave blank if you don't wish to change the existing password.</small>
                <input type="hidden" id="password_chunk_count" name="password_chunk_count" value="0">
                <div id="password_chunks_container"></div>
            </div>

            <!-- STATUS (chunked) -->
            <div class="form-group" style="margin-bottom: 20px;">
                <label for="modalStatus" style="display: block; font-weight: 600; margin-bottom: 6px; font-size: 14px;">Account Status</label>
                <select id="modalStatus" class="form-control" style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px;" onchange="syncChunkedField('modalStatus', 'status')">
                    <option value="1">Active (Can Log In)</option>
                    <option value="0">Disabled (Login Blocked)</option>
                </select>
                <input type="hidden" id="status_chunk_count" name="status_chunk_count" value="0">
                <div id="status_chunks_container"></div>
            </div>

            <div class="delete-modal-actions" style="margin-top: 25px; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn-cancel" onclick="closeCredentialsModal()">Cancel</button>
                <button type="submit" class="btn-confirm-delete" style="background: #184C74;">Save Credentials</button>
            </div>
        </form>
    </div>
</div>

<!-- TOGGLE PROFILE TYPE FORM (Hidden, chunked Base64) -->
<form method="POST" action="users.php" id="togglePageTypeForm" style="display: none;">
    <input type="hidden" name="action" value="toggle_page_type">
    <input type="hidden" id="toggle_faculty_id_chunk_count" name="faculty_id_chunk_count" value="0">
    <div id="toggle_faculty_id_chunks_container"></div>
    <input type="hidden" id="toggle_target_type_chunk_count" name="target_type_chunk_count" value="0">
    <div id="toggle_target_type_chunks_container"></div>
</form>


<script>
/* UTF-8 Safe Base64 */
function b64EncodeUtf8(str) {
    return btoa(unescape(encodeURIComponent(str)));
}

function splitIntoChunks(text, chunkSize) {
    const chunks = [];
    for (let i = 0; i < text.length; i += chunkSize) {
        chunks.push(text.substring(i, i + chunkSize));
    }
    return chunks;
}

function syncChunkedField(sourceId, targetName) {
    const elem = document.getElementById(sourceId);
    if (!elem) return;
    const val = elem.value || '';
    const encoded = b64EncodeUtf8(val);
    const chunks = splitIntoChunks(encoded, 200);

    const countElem = document.getElementById(targetName + '_chunk_count');
    const container = document.getElementById(targetName + '_chunks_container');

    if (countElem) countElem.value = chunks.length;
    if (container) {
        container.innerHTML = '';
        chunks.forEach((chunk, idx) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = targetName + '_chunk_' + idx;
            input.value = chunk;
            container.appendChild(input);
        });
    }
}

function syncCustomChunked(targetName, val) {
    const encoded = b64EncodeUtf8(String(val || ''));
    const chunks = splitIntoChunks(encoded, 200);

    const countElem = document.getElementById(targetName + '_chunk_count');
    const container = document.getElementById(targetName + '_chunks_container');

    if (countElem) countElem.value = chunks.length;
    if (container) {
        container.innerHTML = '';
        chunks.forEach((chunk, idx) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = targetName + '_chunk_' + idx;
            input.value = chunk;
            container.appendChild(input);
        });
    }
}

/* Modals */
function openCredentialsModal(facultyId, facultyName, username, status, userId) {
    document.getElementById('modalFacultyName').textContent = facultyName;
    document.getElementById('faculty_id').value = facultyId;
    document.getElementById('modalUsername').value = username || '';
    document.getElementById('modalPassword').value = '';
    document.getElementById('modalStatus').value = status !== undefined ? status : '1';

    document.getElementById('facultySelectGroup').style.display = 'none';
    if (userId > 0 || (username && username.length > 0)) {
        document.getElementById('modalTitle').textContent = 'Edit Login for ' + facultyName;
        document.getElementById('modalPassword').required = false;
        document.getElementById('passwordHelp').style.display = 'block';
    } else {
        document.getElementById('modalTitle').textContent = 'Create Login for ' + facultyName;
        document.getElementById('modalPassword').required = true;
        document.getElementById('passwordHelp').style.display = 'none';
    }

    syncChunkedField('faculty_id', 'faculty_id');
    syncChunkedField('modalUsername', 'username');
    syncChunkedField('modalPassword', 'password');
    syncChunkedField('modalStatus', 'status');

    document.getElementById('credentialsModal').classList.add('show');
}

function openAddUserModal() {
    document.getElementById('modalTitle').textContent = 'Create New Faculty Login';
    document.getElementById('modalFacultyName').textContent = 'Selected Faculty';
    document.getElementById('faculty_id').value = '';
    document.getElementById('modalUsername').value = '';
    document.getElementById('modalPassword').value = '';
    document.getElementById('modalPassword').required = true;
    document.getElementById('passwordHelp').style.display = 'none';
    document.getElementById('modalStatus').value = '1';

    const select = document.getElementById('select_faculty');
    select.value = '';
    document.getElementById('facultySelectGroup').style.display = 'block';

    syncChunkedField('faculty_id', 'faculty_id');
    syncChunkedField('modalUsername', 'username');
    syncChunkedField('modalPassword', 'password');
    syncChunkedField('modalStatus', 'status');

    document.getElementById('credentialsModal').classList.add('show');
}

function onSelectFacultyChange(val) {
    document.getElementById('faculty_id').value = val;
    const select = document.getElementById('select_faculty');
    const opt = select.options[select.selectedIndex];
    if (opt && opt.dataset.name) {
        document.getElementById('modalFacultyName').textContent = opt.dataset.name;
    }
    syncChunkedField('faculty_id', 'faculty_id');
}

function closeCredentialsModal() {
    document.getElementById('credentialsModal').classList.remove('show');
}

function prepareCredentialsSubmit() {
    const fid = document.getElementById('faculty_id').value;
    if (!fid || fid === '0') {
        alert('Please select a faculty member.');
        return false;
    }
    syncChunkedField('faculty_id', 'faculty_id');
    syncChunkedField('modalUsername', 'username');
    syncChunkedField('modalPassword', 'password');
    syncChunkedField('modalStatus', 'status');
    return true;
}

function toggleProfileType(facultyId, targetType, facultyName) {
    const msg = targetType === 'inner' 
        ? 'Switch ' + facultyName + ' to use an Inner Profile Page (innerfaculty.php)?' 
        : 'Switch ' + facultyName + ' to use an External Link?';

    if (confirm(msg)) {
        syncCustomChunked('toggle_faculty_id', facultyId);
        syncCustomChunked('toggle_target_type', targetType);
        document.getElementById('togglePageTypeForm').submit();
    }
}


/* Close on backdrop or ESC */
document.querySelectorAll('.delete-modal').forEach(m => {
    m.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('show');
        }
    });
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.delete-modal').forEach(m => m.classList.remove('show'));
    }
});

/* Search and Pagination (10 rows per page matching clusters.php) */
document.addEventListener('DOMContentLoaded', function() {
    const rows = Array.from(document.querySelectorAll('#userTableBody .user-row'));
    const searchInput = document.getElementById('userSearch');
    const pagination = document.getElementById('userPagination');
    const noResults = document.getElementById('userNoResults');
    const searchCount = document.getElementById('userSearchCount');

    const rowsPerPage = 10;
    let currentPage = 1;
    let filteredRows = [...rows];

    function displayTable() {
        rows.forEach(r => r.style.display = 'none');
        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        filteredRows.slice(start, end).forEach(r => r.style.display = 'table-row');

        if (filteredRows.length === 0) {
            searchCount.textContent = '';
            noResults.style.display = 'block';
        } else {
            searchCount.textContent = filteredRows.length + (filteredRows.length === 1 ? ' faculty' : ' faculties');
            noResults.style.display = 'none';
        }
        createPagination();
    }

    function createPagination() {
        if (!pagination) return;
        pagination.innerHTML = '';
        const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
        if (totalPages <= 1) return;

        const previous = document.createElement('button');
        previous.type = 'button';
        previous.className = 'pagination-btn';
        previous.innerHTML = '&#10094;';
        previous.disabled = currentPage === 1;
        previous.addEventListener('click', function() {
            if (currentPage > 1) { currentPage--; displayTable(); }
        });
        pagination.appendChild(previous);

        for (let page = 1; page <= totalPages; page++) {
            const pageButton = document.createElement('button');
            pageButton.type = 'button';
            pageButton.className = 'pagination-btn';
            pageButton.textContent = page;
            if (page === currentPage) pageButton.classList.add('active');
            pageButton.addEventListener('click', function() {
                currentPage = page;
                displayTable();
            });
            pagination.appendChild(pageButton);
        }

        const next = document.createElement('button');
        next.type = 'button';
        next.className = 'pagination-btn';
        next.innerHTML = '&#10095;';
        next.disabled = currentPage === totalPages;
        next.addEventListener('click', function() {
            if (currentPage < totalPages) { currentPage++; displayTable(); }
        });
        pagination.appendChild(next);
    }

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const term = this.value.toLowerCase().trim();
            filteredRows = rows.filter(r => r.textContent.toLowerCase().includes(term));
            currentPage = 1;
            displayTable();
        });
    }

    displayTable();
});
</script>

<?php include 'footer.php'; ?>
