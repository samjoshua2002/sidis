<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

function sendJson($data) {
    echo json_encode($data);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$clusterId = (int)($_POST['cluster_id'] ?? $_GET['cluster_id'] ?? 0);

if ($clusterId <= 0) {
    sendJson(['status' => 'error', 'message' => 'Invalid cluster ID.']);
}

// Helper to fetch current coordinators for cluster
function getClusterCoordinators($pdo, $clusterId) {
    $stmt = $pdo->prepare("
        SELECT id, name, image, department, designation, email, coordinator_role, coordinator_programme, coordinator_order
        FROM faculty
        WHERE status = 1
          AND (
                FIND_IN_SET(?, cluster_id) > 0
                OR cluster_id = ?
              )
          AND coordinator_role IS NOT NULL
          AND TRIM(coordinator_role) != ''
        ORDER BY coordinator_order ASC, name ASC, id ASC
    ");
    $stmt->execute([(string)$clusterId, (string)$clusterId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($action === 'get_coordinators') {
    $coordinators = getClusterCoordinators($pdo, $clusterId);
    sendJson(['status' => 'success', 'coordinators' => $coordinators]);
}

if ($action === 'save_coordinator') {
    $mode = $_POST['mode'] ?? 'existing'; // 'existing' or 'new'
    $role = trim($_POST['coordinator_role'] ?? '');
    if ($role === '__custom__') {
        $role = trim($_POST['custom_coordinator_role'] ?? '');
    }
    $programme = trim($_POST['coordinator_programme'] ?? '');
    $order = (int)($_POST['coordinator_order'] ?? 0);

    if ($role === '') {
        sendJson(['status' => 'error', 'message' => 'Please select or enter a Coordinator Role.']);
    }

    if ($mode === 'existing') {
        $facultyId = (int)($_POST['faculty_id'] ?? 0);
        if ($facultyId <= 0) {
            sendJson(['status' => 'error', 'message' => 'Please select a faculty member.']);
        }

        // Fetch current faculty record
        $fStmt = $pdo->prepare("SELECT id, name, cluster_id FROM faculty WHERE id = ? LIMIT 1");
        $fStmt->execute([$facultyId]);
        $faculty = $fStmt->fetch(PDO::FETCH_ASSOC);

        if (!$faculty) {
            sendJson(['status' => 'error', 'message' => 'Selected faculty member was not found.']);
        }

        // Update cluster_id to include this cluster if not already present
        $currentClusters = array_filter(array_map('trim', explode(',', $faculty['cluster_id'] ?? '')));
        if (!in_array((string)$clusterId, $currentClusters, true)) {
            $currentClusters[] = (string)$clusterId;
        }
        $newClusterIdStr = implode(',', $currentClusters);

        $upStmt = $pdo->prepare("
            UPDATE faculty
            SET cluster_id = ?,
                coordinator_role = ?,
                coordinator_programme = ?,
                coordinator_order = ?
            WHERE id = ?
        ");
        $upStmt->execute([$newClusterIdStr, $role, $programme, $order, $facultyId]);

        $coordinators = getClusterCoordinators($pdo, $clusterId);
        sendJson([
            'status' => 'success',
            'message' => 'Coordinator saved successfully!',
            'coordinators' => $coordinators
        ]);
    } elseif ($mode === 'new') {
        $name = trim($_POST['new_name'] ?? '');
        $designation = trim($_POST['new_designation'] ?? '');
        $department = trim($_POST['new_department'] ?? '');
        $email = trim($_POST['new_email'] ?? '');

        if ($name === '') {
            sendJson(['status' => 'error', 'message' => 'Please enter the faculty name.']);
        }

        $imageName = 'member.png';
        if (isset($_FILES['new_image']) && $_FILES['new_image']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($_FILES['new_image']['tmp_name']);
            if (isset($allowedTypes[$mime])) {
                $ext = $allowedTypes[$mime];
                $uploadDir = __DIR__ . '/../images/faculty/';
                if (!is_dir($uploadDir)) {
                    @mkdir($uploadDir, 0755, true);
                }
                $cleanName = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($name));
                $targetFile = time() . '_' . $cleanName . '.' . $ext;
                if (move_uploaded_file($_FILES['new_image']['tmp_name'], $uploadDir . $targetFile)) {
                    $imageName = $targetFile;
                }
            }
        }

        $insStmt = $pdo->prepare("
            INSERT INTO faculty
            (name, image, department, designation, email, cluster_id, status, coordinator_role, coordinator_programme, coordinator_order)
            VALUES (?, ?, ?, ?, ?, ?, 1, ?, ?, ?)
        ");
        $insStmt->execute([
            $name,
            $imageName,
            $department,
            $designation,
            $email,
            (string)$clusterId,
            $role,
            $programme,
            $order
        ]);

        $coordinators = getClusterCoordinators($pdo, $clusterId);
        sendJson([
            'status' => 'success',
            'message' => 'New faculty created and assigned as coordinator!',
            'coordinators' => $coordinators
        ]);
    }
}

if ($action === 'remove_coordinator') {
    $facultyId = (int)($_POST['faculty_id'] ?? 0);
    if ($facultyId <= 0) {
        sendJson(['status' => 'error', 'message' => 'Invalid faculty ID.']);
    }

    $upStmt = $pdo->prepare("
        UPDATE faculty
        SET coordinator_role = NULL,
            coordinator_programme = NULL,
            coordinator_order = 0
        WHERE id = ?
    ");
    $upStmt->execute([$facultyId]);

    $coordinators = getClusterCoordinators($pdo, $clusterId);
    sendJson([
        'status' => 'success',
        'message' => 'Coordinator removed from cluster.',
        'coordinators' => $coordinators
    ]);
}

sendJson(['status' => 'error', 'message' => 'Invalid action.']);
