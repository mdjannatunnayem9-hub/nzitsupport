<?php
require_once 'config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: login.php');
    exit;
}

$tab = $_GET['tab'] ?? 'users';

// Handle add user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tab === 'users' && isset($_POST['add_user_registry'])) {
    $oid = trim($_POST['oid']);
    $name = trim($_POST['name']);
    $department = trim($_POST['department'] ?: '');
    $phone = trim($_POST['phone'] ?: '');
    if ($oid && $name) {
        $stmt = $conn->prepare("INSERT IGNORE INTO user_registry (oid, name, department, phone) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $oid, $name, $department, $phone);
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
                $dept = trim($data[2] ?? '');
                $phone = trim($data[3] ?? '');
                if ($oid && $name) {
                    $stmt = $conn->prepare("INSERT IGNORE INTO user_registry (oid, name, department, phone) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("ssss", $oid, $name, $dept, $phone);
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
                    $dept = trim($row[2] ?? '');
                    $phone = trim($row[3] ?? '');
                    if ($oid && $name) {
                        $stmt = $conn->prepare("INSERT IGNORE INTO user_registry (oid, name, department, phone) VALUES (?, ?, ?, ?)");
                        $stmt->bind_param("ssss", $oid, $name, $dept, $phone);
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Data - NZIT Support</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">NZIT Support</a>
            <div class="d-flex align-items-center gap-2">
                <span class="text-light">
                    <i class="bi bi-shield-lock"></i> Admin: <?= htmlspecialchars($_SESSION['username']) ?>
                </span>
                <a href="admin.php" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-arrow-left"></i> Back to Admin
                </a>
                <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

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
                    <div class="col-md-3">
                        <input type="text" name="oid" class="form-control" placeholder="OID (Office ID)" required>
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="name" class="form-control" placeholder="Full Name" required>
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="department" class="form-control" placeholder="Department">
                    </div>
                    <div class="col-md-2">
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
                        <button type="submit" name="import_users" class="btn btn-success">
                            <i class="bi bi-upload"></i> Import
                        </button>
                    </div>
                </form>
                <small class="text-muted">Format: OID, Name, Department, Phone (header row required)</small>
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
                            <tr><th>OID</th><th>Name</th><th>Department</th><th>Phone</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            <?php if ($users && $users->num_rows > 0): ?>
                                <?php while ($u = $users->fetch_assoc()): ?>
                                <tr>
                                    <td><code><?= htmlspecialchars($u['oid']) ?></code></td>
                                    <td><?= htmlspecialchars($u['name']) ?></td>
                                    <td><?= htmlspecialchars($u['department'] ?: '-') ?></td>
                                    <td><?= htmlspecialchars($u['phone'] ?: '-') ?></td>
                                    <td>
                                        <form method="post" onsubmit="return confirm('Delete this user?')">
                                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                            <button type="submit" name="delete_user_registry" class="btn btn-danger btn-sm">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center text-muted py-3">No users found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
