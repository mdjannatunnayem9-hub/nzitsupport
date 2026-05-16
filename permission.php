<?php
require_once 'config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: login.php');
    exit;
}

$selected_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$selected_user = null;

// Handle add new user key
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $new_key = trim($_POST['new_key'] ?? '');
    $new_username = trim($_POST['new_username'] ?? '');
    if ($new_key && $new_username) {
        $conn->query("INSERT IGNORE INTO users (username, password, role, user_key, can_view, can_edit, can_delete, can_update) VALUES ('$new_username', '', 'user', '$new_key', 1, 1, 0, 1)");
        $new_id = $conn->insert_id;
        if ($new_id) {
            $pages = $conn->query("SELECT page_name FROM page_permissions");
            while ($p = $pages->fetch_assoc()) {
                $pn = $p['page_name'];
                $conn->query("INSERT IGNORE INTO user_page_permissions (user_id, page_name, can_view, can_edit, can_delete, can_update) VALUES ($new_id, '$pn', 1, 0, 0, 0)");
            }
            $success = "User '$new_username' with key '$new_key' added!";
        } else {
            $error = "Key '$new_key' already exists!";
        }
    } else {
        $error = 'Please enter both username and key.';
    }
}

// Handle public/private page permissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_page_perms_public'])) {
    $page_ids = $_POST['page_id'] ?? [];
    foreach ($page_ids as $pid) {
        $pid = intval($pid);
        $public = isset($_POST['is_public'][$pid]) ? 1 : 0;
        $conn->query("UPDATE page_permissions SET is_public=$public WHERE id=$pid");
    }
    $success = 'Page public/private settings updated!';
}

// Handle page permission update for selected user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_page_perms'])) {
    $sel_user_id = intval($_POST['sel_user_id']);
    $page_names = $_POST['page_name'] ?? [];

    foreach ($page_names as $page_name) {
        $page_name = trim($page_name);
        $view  = isset($_POST['can_view'][$page_name]) ? 1 : 0;
        $edit  = isset($_POST['can_edit'][$page_name]) ? 1 : 0;
        $del   = isset($_POST['can_delete'][$page_name]) ? 1 : 0;
        $upd   = isset($_POST['can_update'][$page_name]) ? 1 : 0;

        $stmt = $conn->prepare("INSERT INTO user_page_permissions (user_id, page_name, can_view, can_edit, can_delete, can_update)
                                VALUES (?, ?, ?, ?, ?, ?)
                                ON DUPLICATE KEY UPDATE can_view=VALUES(can_view), can_edit=VALUES(can_edit), can_delete=VALUES(can_delete), can_update=VALUES(can_update)");
        $stmt->bind_param("isiiii", $sel_user_id, $page_name, $view, $edit, $del, $upd);
        $stmt->execute();
    }
    $selected_user_id = $sel_user_id;
    $success = 'Page permissions updated for user!';
}

// Fetch selected user
if ($selected_user_id) {
    $user_res = $conn->query("SELECT * FROM users WHERE id = $selected_user_id AND role='user'");
    $selected_user = $user_res->fetch_assoc();
}

$users = $conn->query("SELECT * FROM users WHERE role='user' ORDER BY username");
$pages = $conn->query("SELECT * FROM page_permissions ORDER BY id");

// Fetch page permissions for selected user
$user_page_perms = [];
if ($selected_user) {
    $perms_res = $conn->query("SELECT * FROM user_page_permissions WHERE user_id = $selected_user_id");
    while ($pr = $perms_res->fetch_assoc()) {
        $user_page_perms[$pr['page_name']] = $pr;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permission Panel - NZIT Support</title>
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

        <h4 class="mb-4"><i class="bi bi-shield-check"></i> Permission Management</h4>

        <!-- Add New User Key -->
        <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-person-plus"></i> Add New User Key
            </div>
            <div class="card-body">
                <form method="post" class="row g-2">
                    <div class="col-md-4">
                        <input type="text" name="new_username" class="form-control" placeholder="Username (e.g. Rahim)" required>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="new_key" class="form-control" placeholder="Key (e.g. rahim123)" required>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" name="add_user" class="btn btn-primary w-100">
                            <i class="bi bi-plus"></i> Add
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Select User -->
        <div class="card shadow mb-4">
            <div class="card-header bg-secondary text-white">
                <i class="bi bi-person-badge"></i> Select User
            </div>
            <div class="card-body">
                <form method="get" class="row g-2 align-items-end">
                    <div class="col-md-6">
                        <select name="user_id" class="form-select" required>
                            <option value="">-- Select User --</option>
                            <?php if ($users && $users->num_rows > 0): ?>
                                <?php while ($u = $users->fetch_assoc()): ?>
                                    <option value="<?= $u['id'] ?>" <?= $selected_user_id == $u['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($u['username']) ?> (key: <?= htmlspecialchars($u['user_key']) ?>)
                                    </option>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-secondary w-100">
                            <i class="bi bi-search"></i> Load
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($selected_user): ?>
            <!-- Page Permissions for Selected User -->
            <div class="card shadow">
                <div class="card-header bg-dark text-white">
                    <i class="bi bi-shield-check"></i> Page Permissions: <strong><?= htmlspecialchars($selected_user['username']) ?></strong>
                </div>
                <div class="card-body p-0">
                    <form method="post">
                        <input type="hidden" name="sel_user_id" value="<?= $selected_user['id'] ?>">
                        <table class="table table-bordered mb-0">
                            <thead class="table-secondary">
                                <tr>
                                    <th>Page</th>
                                    <th class="text-center">View</th>
                                    <th class="text-center">Add</th>
                                    <th class="text-center">Edit</th>
                                    <th class="text-center">Delete</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($pages && $pages->num_rows > 0): ?>
                                    <?php $pages->data_seek(0); while ($p = $pages->fetch_assoc()): ?>
                                        <?php $perms = $user_page_perms[$p['page_name']] ?? []; ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($p['page_title']) ?></strong>
                                                <code class="ms-2"><?= htmlspecialchars($p['page_name']) ?></code>
                                            </td>
                                            <td class="text-center">
                                                <input type="hidden" name="page_name[]" value="<?= $p['page_name'] ?>">
                                                <input type="checkbox" name="can_view[<?= $p['page_name'] ?>]" value="1"
                                                    <?= (isset($perms['can_view']) && $perms['can_view']) ? 'checked' : '' ?> class="form-check-input">
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" name="can_edit[<?= $p['page_name'] ?>]" value="1"
                                                    <?= (isset($perms['can_edit']) && $perms['can_edit']) ? 'checked' : '' ?> class="form-check-input">
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" name="can_update[<?= $p['page_name'] ?>]" value="1"
                                                    <?= (isset($perms['can_update']) && $perms['can_update']) ? 'checked' : '' ?> class="form-check-input">
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" name="can_delete[<?= $p['page_name'] ?>]" value="1"
                                                    <?= (isset($perms['can_delete']) && $perms['can_delete']) ? 'checked' : '' ?> class="form-check-input">
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center text-muted py-3">No pages found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <div class="p-3 text-end">
                            <button type="submit" name="update_page_perms" class="btn btn-success">
                                <i class="bi bi-floppy"></i> Save Permissions for <?= htmlspecialchars($selected_user['username']) ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <!-- Page Public/Private Settings -->
        <div class="card shadow mt-4">
            <div class="card-header bg-info text-white">
                <i class="bi bi-globe"></i> Page View Permissions (Public/Private)
            </div>
            <div class="card-body p-0">
                <form method="post">
                    <table class="table table-bordered mb-0">
                        <thead class="table-info">
                            <tr>
                                <th>Page</th>
                                <th class="text-center">Public (Anyone can view)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($pages && $pages->num_rows > 0): ?>
                                <?php $pages->data_seek(0); while ($p = $pages->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($p['page_title']) ?></strong>
                                            <code class="ms-2"><?= htmlspecialchars($p['page_name']) ?></code>
                                        </td>
                                        <td class="text-center">
                                            <input type="hidden" name="page_id[]" value="<?= $p['id'] ?>">
                                            <input type="checkbox" name="is_public[<?= $p['id'] ?>]" value="1"
                                                <?= $p['is_public'] ? 'checked' : '' ?> class="form-check-input">
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <div class="p-3 text-end">
                        <button type="submit" name="update_page_perms_public" class="btn btn-info">
                            <i class="bi bi-floppy"></i> Save Public/Private Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php require_once 'footer.php'; ?>
</body>
</html>
