<?php
require_once 'config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: login.php');
    exit;
}

// Export users to CSV
if (isset($_GET['export_users'])) {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename=user_registry.csv');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['OID', 'Name', 'Designation', 'Department', 'Phone']);
    $rows = $conn->query("SELECT oid, name, designation, department, phone FROM user_registry ORDER BY name");
    while ($r = $rows->fetch_assoc()) {
        fputcsv($out, [$r['oid'], $r['name'], $r['designation'] ?? '', $r['department'] ?? '', $r['phone'] ?? '']);
    }
    fclose($out);
    exit;
}

$tab = $_GET['tab'] ?? 'users';

// Handle add user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tab === 'users' && isset($_POST['add_user_registry'])) {
    $oid = trim($_POST['oid']);
    $name = trim($_POST['name']);
    $designation = trim($_POST['designation'] ?: '');
    $department = trim($_POST['department'] ?: '');
    $phone = trim($_POST['phone'] ?: '');
    if ($oid && $name) {
        $stmt = $conn->prepare("INSERT IGNORE INTO user_registry (oid, name, designation, department, phone) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $oid, $name, $designation, $department, $phone);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            $success = "User '$name' added!";
        } else {
            $error = "OID '$oid' already exists!";
        }
    } else {
        $error = 'OID and Name are required.';
    }
}

// Handle delete user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tab === 'users' && isset($_POST['delete_user_registry'])) {
    $id = intval($_POST['id']);
    $conn->query("DELETE FROM user_registry WHERE id=$id");
    $success = 'User deleted!';
}

// Handle update user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tab === 'users' && isset($_POST['update_user_registry'])) {
    $uid = intval($_POST['uid']);
    $oid = trim($_POST['oid']);
    $name = trim($_POST['name']);
    $designation = trim($_POST['designation'] ?: '');
    $department = trim($_POST['department'] ?: '');
    $phone = trim($_POST['phone'] ?: '');
    if ($uid && $oid && $name) {
        $stmt = $conn->prepare("UPDATE user_registry SET oid=?, name=?, designation=?, department=?, phone=? WHERE id=?");
        $stmt->bind_param("sssssi", $oid, $name, $designation, $department, $phone, $uid);
        $stmt->execute();
        $success = "User '$name' updated!";
    } else {
        $error = 'OID and Name are required.';
    }
}

// Handle approve user request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tab === 'users' && isset($_POST['approve_request'])) {
    $rid = intval($_POST['request_id']);
    $req = $conn->query("SELECT * FROM user_requests WHERE id=$rid")->fetch_assoc();
    if ($req) {
        $stmt = $conn->prepare("INSERT IGNORE INTO user_registry (oid, name, designation, department, phone) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $req['oid'], $req['name'], $req['designation'], $req['department'], $req['phone']);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            $conn->query("UPDATE user_requests SET status='approved' WHERE id=$rid");
            $success = "User '{$req['name']}' approved and added!";
        } else {
            $error = "OID '{$req['oid']}' already exists!";
        }
    }
}

// Handle reject user request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tab === 'users' && isset($_POST['reject_request'])) {
    $rid = intval($_POST['request_id']);
    $conn->query("UPDATE user_requests SET status='rejected' WHERE id=$rid");
    $success = 'Request rejected.';
}

// Handle Excel import for users
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tab === 'users' && isset($_POST['import_users'])) {
    if (isset($_FILES['user_excel']) && $_FILES['user_excel']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['user_excel']['name'], PATHINFO_EXTENSION));
        $imported = 0; $skipped = 0;
        if ($ext === 'csv') {
            $handle = fopen($_FILES['user_excel']['tmp_name'], 'r');
            $header = fgetcsv($handle);
            while (($data = fgetcsv($handle)) !== false) {
                $oid = trim($data[0] ?? '');
                $name = trim($data[1] ?? '');
                $desig = trim($data[2] ?? '');
                $dept = trim($data[3] ?? '');
                $phone = trim($data[4] ?? '');
                if ($oid && $name) {
                    $stmt = $conn->prepare("INSERT IGNORE INTO user_registry (oid, name, designation, department, phone) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssss", $oid, $name, $desig, $dept, $phone);
                    $stmt->execute();
                    if ($stmt->affected_rows > 0) $imported++; else $skipped++;
                }
            }
            fclose($handle);
        } elseif ($ext === 'xlsx') {
            require_once 'lib/SimpleXLSX.php';
            $xlsx = SimpleXLSX::parse($_FILES['user_excel']['tmp_name']);
            if ($xlsx) {
                $rows = $xlsx->rows();
                $first = true;
                foreach ($rows as $row) {
                    if ($first) { $first = false; continue; }
                    $oid = trim($row[0] ?? '');
                    $name = trim($row[1] ?? '');
                    $desig = trim($row[2] ?? '');
                    $dept = trim($row[3] ?? '');
                    $phone = trim($row[4] ?? '');
                    if ($oid && $name) {
                        $stmt = $conn->prepare("INSERT IGNORE INTO user_registry (oid, name, designation, department, phone) VALUES (?, ?, ?, ?, ?)");
                        $stmt->bind_param("sssss", $oid, $name, $desig, $dept, $phone);
                        $stmt->execute();
                        if ($stmt->affected_rows > 0) $imported++; else $skipped++;
                    }
                }
            }
        }
        $success = "Import complete: $imported added, $skipped skipped.";
    } else {
        $error = 'Please upload a file.';
    }
}

// Handle add status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tab === 'statuses' && isset($_POST['add_status'])) {
    $name = trim($_POST['name']);
    $color = trim($_POST['color'] ?: '#6c757d');
    $text_color = trim($_POST['text_color'] ?: '#fff');
    $abbr = trim($_POST['abbreviation'] ?: '');
    if ($name) {
        $max_order = $conn->query("SELECT MAX(sort_order) AS m FROM statuses")->fetch_assoc()['m'];
        $next_order = ($max_order ?? 0) + 1;
        $stmt = $conn->prepare("INSERT IGNORE INTO statuses (name, color, text_color, sort_order, abbreviation) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssis", $name, $color, $text_color, $next_order, $abbr);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            $success = "Status '$name' added!";
        } else {
            $error = "Status '$name' already exists!";
        }
    } else {
        $error = 'Status name is required.';
    }
}

// Handle edit status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tab === 'statuses' && isset($_POST['edit_status'])) {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    $color = trim($_POST['color'] ?: '#6c757d');
    $text_color = trim($_POST['text_color'] ?: '#fff');
    $abbr = trim($_POST['abbreviation'] ?: '');
    if ($name) {
        $stmt = $conn->prepare("UPDATE statuses SET name=?, color=?, text_color=?, abbreviation=? WHERE id=?");
        $stmt->bind_param("ssssi", $name, $color, $text_color, $abbr, $id);
        $stmt->execute();
        $success = "Status updated!";
    }
}

// Handle delete status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tab === 'statuses' && isset($_POST['delete_status'])) {
    $id = intval($_POST['id']);
    $conn->query("DELETE FROM statuses WHERE id=$id");
    $success = 'Status deleted!';
}

// Handle add device
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tab === 'devices' && isset($_POST['add_device'])) {
    $name = trim($_POST['name']);
    if ($name) {
        $conn->query("INSERT IGNORE INTO devices (name) VALUES ('$name')");
        if ($conn->affected_rows > 0) {
            $success = "Device '$name' added!";
        } else {
            $error = "Device '$name' already exists!";
        }
    } else {
        $error = 'Device name is required.';
    }
}

// Handle delete device
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tab === 'devices' && isset($_POST['delete_device'])) {
    $id = intval($_POST['id']);
    $conn->query("DELETE FROM devices WHERE id=$id");
    $success = 'Device deleted!';
}

$statuses = $conn->query("SELECT * FROM statuses ORDER BY sort_order");
$devices = $conn->query("SELECT * FROM devices ORDER BY name");
$users = $conn->query("SELECT * FROM user_registry ORDER BY name");
$pending_requests = $conn->query("SELECT * FROM user_requests WHERE status='pending' ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Data - NZIT Support</title>
    <link rel="icon" href="NZ Icon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light" style="overflow-x:hidden;">
    <?php require_once 'header.php'; ?>

    <div class="container py-4">
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <h4 class="mb-4"><i class="bi bi-gear"></i> Manage Data</h4>

        <!-- Tabs -->
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link <?= $tab === 'users' ? 'active' : '' ?>" href="?tab=users">
                    <i class="bi bi-people"></i> Users
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $tab === 'statuses' ? 'active' : '' ?>" href="?tab=statuses">
                    <i class="bi bi-tags"></i> Statuses
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $tab === 'devices' ? 'active' : '' ?>" href="?tab=devices">
                    <i class="bi bi-pc-display"></i> Devices
                </a>
            </li>
        </ul>

        <?php if ($tab === 'users'): ?>
        <!-- ==================== USERS ==================== -->
        <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-person-plus"></i> Add New User
            </div>
            <div class="card-body">
                <form method="post" class="row g-2">
                    <div class="col-md-2">
                        <input type="text" name="oid" class="form-control" placeholder="OID" required>
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="name" class="form-control" placeholder="Full Name" required>
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="designation" class="form-control" placeholder="Designation">
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="department" class="form-control" placeholder="Department">
                    </div>
                    <div class="col-md-1">
                        <input type="text" name="phone" class="form-control" placeholder="Phone">
                    </div>
                    <div class="col-md-1">
                        <button type="submit" name="add_user_registry" class="btn btn-primary w-100">
                            <i class="bi bi-plus"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header bg-success text-white">
                <i class="bi bi-file-earmark-excel"></i> Import Users from Excel/CSV
            </div>
            <div class="card-body">
                <form method="post" enctype="multipart/form-data" class="row g-2">
                    <div class="col-md-6">
                        <input type="file" name="user_excel" class="form-control" accept=".xlsx,.csv" required>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" name="import_users" class="btn btn-success w-100">
                            <i class="bi bi-upload"></i> Import
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="?tab=users&export_users=1" class="btn btn-info w-100">
                            <i class="bi bi-download"></i> Export
                        </a>
                    </div>
                </form>
                <small class="text-muted">Format: OID, Name, Designation, Department, Phone (header row required)</small>
            </div>
        </div>

        <div class="card shadow">
            <div class="card-header bg-dark text-white">
                <i class="bi bi-people"></i> User Registry
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead class="table-secondary">
                            <tr><th>OID</th><th>Name</th><th>Designation</th><th>Department</th><th>Phone</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            <?php if ($users && $users->num_rows > 0): ?>
                                <?php while ($u = $users->fetch_assoc()): ?>
                                <tr>
                                    <td><code><?= htmlspecialchars($u['oid']) ?></code></td>
                                    <td><?= htmlspecialchars($u['name']) ?></td>
                                    <td><?= htmlspecialchars($u['designation'] ?: '-') ?></td>
                                    <td><?= htmlspecialchars($u['department'] ?: '-') ?></td>
                                    <td><?= htmlspecialchars($u['phone'] ?: '-') ?></td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <button class="btn btn-warning btn-sm" onclick="editUser(<?= $u['id'] ?>, '<?= htmlspecialchars($u['oid'], ENT_QUOTES) ?>', '<?= htmlspecialchars($u['name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($u['designation'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($u['department'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($u['phone'] ?? '', ENT_QUOTES) ?>')">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form method="post" onsubmit="return confirm('Delete this user?')">
                                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                                <button type="submit" name="delete_user_registry" class="btn btn-danger btn-sm">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center text-muted py-3">No users found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- User Requests -->
        <?php if ($pending_requests && $pending_requests->num_rows > 0): ?>
        <div class="card shadow mt-4">
            <div class="card-header bg-warning text-dark">
                <i class="bi bi-hourglass-split"></i> Pending User Requests (<?= $pending_requests->num_rows ?>)
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead class="table-warning">
                            <tr><th>OID</th><th>Name</th><th>Designation</th><th>Department</th><th>Phone</th><th>Requested By</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            <?php while ($rq = $pending_requests->fetch_assoc()): ?>
                            <tr>
                                <td><code><?= htmlspecialchars($rq['oid']) ?></code></td>
                                <td><?= htmlspecialchars($rq['name']) ?></td>
                                <td><?= htmlspecialchars($rq['designation'] ?: '-') ?></td>
                                <td><?= htmlspecialchars($rq['department'] ?: '-') ?></td>
                                <td><?= htmlspecialchars($rq['phone'] ?: '-') ?></td>
                                <td><?= htmlspecialchars($rq['requested_by']) ?></td>
                                <td>
                                    <form method="post" style="display:inline">
                                        <input type="hidden" name="request_id" value="<?= $rq['id'] ?>">
                                        <button type="submit" name="approve_request" class="btn btn-success btn-sm" onclick="return confirm('Approve this user?')">
                                            <i class="bi bi-check-lg"></i> Approve
                                        </button>
                                        <button type="submit" name="reject_request" class="btn btn-danger btn-sm" onclick="return confirm('Reject this request?')">
                                            <i class="bi bi-x-lg"></i> Reject
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php elseif ($tab === 'statuses'): ?>
        <!-- ==================== STATUSES ==================== -->
        <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-tag-plus"></i> Add New Status
            </div>
            <div class="card-body">
                <form method="post" class="row g-2">
                    <div class="col-md-3">
                        <input type="text" name="name" class="form-control" placeholder="Status Name" required>
                    </div>
                    <div class="col-md-2">
                        <input type="color" name="color" class="form-control form-control-color" value="#6c757d" title="Background color">
                    </div>
                    <div class="col-md-2">
                        <input type="color" name="text_color" class="form-control form-control-color" value="#ffffff" title="Text color">
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="abbreviation" class="form-control" placeholder="Abbreviation (e.g. IP)">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" name="add_status" class="btn btn-primary w-100">
                            <i class="bi bi-plus"></i> Add
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow">
            <div class="card-header bg-dark text-white">
                <i class="bi bi-tags"></i> Status List
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead class="table-secondary">
                            <tr><th>Name</th><th>Color</th><th>Text Color</th><th>Abbreviation</th><th>Sort</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            <?php if ($statuses && $statuses->num_rows > 0): ?>
                                <?php while ($s = $statuses->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <span class="badge" style="background:<?= $s['color'] ?>;color:<?= $s['text_color'] ?>">
                                            <?= htmlspecialchars($s['name']) ?>
                                        </span>
                                    </td>
                                    <td><code><?= $s['color'] ?></code></td>
                                    <td><code><?= $s['text_color'] ?></code></td>
                                    <td><code><?= htmlspecialchars($s['abbreviation'] ?: '-') ?></code></td>
                                    <td><?= $s['sort_order'] ?></td>
                                    <td>
                                        <button class="btn btn-warning btn-sm" data-bs-toggle="collapse" data-bs-target="#editStatus<?= $s['id'] ?>">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form method="post" class="d-inline" onsubmit="return confirm('Delete status <?= htmlspecialchars($s['name']) ?>?')">
                                            <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                            <button type="submit" name="delete_status" class="btn btn-danger btn-sm">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <tr class="collapse" id="editStatus<?= $s['id'] ?>">
                                    <td colspan="6" class="p-2">
                                        <form method="post" class="row g-2">
                                            <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                            <div class="col-md-3">
                                                <input type="text" name="name" class="form-control form-control-sm" value="<?= htmlspecialchars($s['name']) ?>" required>
                                            </div>
                                            <div class="col-md-2">
                                                <input type="color" name="color" class="form-control form-control-color form-control-sm" value="<?= $s['color'] ?>">
                                            </div>
                                            <div class="col-md-2">
                                                <input type="color" name="text_color" class="form-control form-control-color form-control-sm" value="<?= $s['text_color'] ?>">
                                            </div>
                                            <div class="col-md-3">
                                                <input type="text" name="abbreviation" class="form-control form-control-sm" value="<?= htmlspecialchars($s['abbreviation']) ?>" placeholder="Abbr">
                                            </div>
                                            <div class="col-md-2">
                                                <button type="submit" name="edit_status" class="btn btn-success btn-sm w-100">
                                                    <i class="bi bi-check"></i> Save
                                                </button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center text-muted py-3">No statuses found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php elseif ($tab === 'devices'): ?>
        <!-- ==================== DEVICES ==================== -->
        <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-plus-circle"></i> Add New Device
            </div>
            <div class="card-body">
                <form method="post" class="row g-2">
                    <div class="col-md-6">
                        <input type="text" name="name" class="form-control" placeholder="Device Name" required>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" name="add_device" class="btn btn-primary w-100">
                            <i class="bi bi-plus"></i> Add
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow">
            <div class="card-header bg-dark text-white">
                <i class="bi bi-pc-display"></i> Device List
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead class="table-secondary">
                            <tr><th>Name</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            <?php if ($devices && $devices->num_rows > 0): ?>
                                <?php while ($d = $devices->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($d['name']) ?></td>
                                    <td>
                                        <form method="post" onsubmit="return confirm('Delete device <?= htmlspecialchars($d['name']) ?>?')">
                                            <input type="hidden" name="id" value="<?= $d['id'] ?>">
                                            <button type="submit" name="delete_device" class="btn btn-danger btn-sm">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="2" class="text-center text-muted py-3">No devices found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title"><i class="bi bi-pencil"></i> Edit User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="uid" id="editUid">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">OID <span class="text-danger">*</span></label>
                                <input type="text" name="oid" id="editOid" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="editName" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Designation</label>
                                <input type="text" name="designation" id="editDesignation" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Department</label>
                                <input type="text" name="department" id="editDepartment" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" id="editPhone" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_user_registry" class="btn btn-warning"><i class="bi bi-check-lg"></i> Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function editUser(id, oid, name, designation, department, phone) {
        document.getElementById('editUid').value = id;
        document.getElementById('editOid').value = oid;
        document.getElementById('editName').value = name;
        document.getElementById('editDesignation').value = designation;
        document.getElementById('editDepartment').value = department;
        document.getElementById('editPhone').value = phone;
        var modal = new bootstrap.Modal(document.getElementById('editUserModal'));
        modal.show();
    }
    </script>
    <?php require_once 'footer.php'; ?>
</body>
</html>
