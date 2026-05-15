<?php
require_once 'config.php';

// Load statuses and devices from DB
$statuses = $conn->query("SELECT * FROM statuses ORDER BY sort_order");
$devices = $conn->query("SELECT * FROM devices ORDER BY name");

// Build lookup arrays from DB
$status_order = []; $colors = []; $text_colors = [];
if ($statuses && $statuses->num_rows > 0) {
    $statuses->data_seek(0);
    while ($s = $statuses->fetch_assoc()) {
        $status_order[$s['name']] = $s['abbreviation'] ?: substr($s['name'], 0, 2);
        $colors[$s['name']] = $s['color'];
        $text_colors[$s['name']] = $s['text_color'];
    }
    $statuses->data_seek(0);
}

// Page permission check
if (!isPagePublic('nzsupportlist')) {
    requireLogin();
}

// CRUD requires login
if (isset($_GET['delete']) || $_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['edit'])) {
    requireLogin();
}

// Build preserved query params for redirects (strip delete/edit)
$preserved_params = $_GET;
unset($preserved_params['delete'], $preserved_params['edit']);
$preserved_query = $preserved_params ? '?' . http_build_query($preserved_params) : '';

// Close action
if (isset($_POST['close_id'])) {
    if (hasPagePermission('nzsupportlist', 'can_update')) {
        $cid = intval($_POST['close_id']);
        $today = date('Y-m-d');
        $old_q = $conn->query("SELECT status FROM nzsupportlist WHERE id = $cid");
        $old_st = $old_q->fetch_assoc()['status'] ?? '';
        $conn->query("UPDATE nzsupportlist SET status='Complete', end_date='$today' WHERE id=$cid");
        if ($old_st !== 'Complete') {
            $uname = $_SESSION['username'] ?? 'System';
            $log_rem = "Status: $old_st -> Complete";
            $stmt_l = $conn->prepare("INSERT INTO remarks (support_id, user_name, remark) VALUES (?, ?, ?)");
            $stmt_l->bind_param("iss", $cid, $uname, $log_rem);
            $stmt_l->execute();
        }
    }
    header('Location: nzsupportlist.php' . $preserved_query);
    exit;
}

// Add remark
if (isset($_POST['add_remark_id']) && hasPagePermission('nzsupportlist', 'can_update')) {
    $sid = intval($_POST['add_remark_id']);
    $remark = trim($_POST['remark_text'] ?? '');
    $uname = $_SESSION['username'] ?? 'Unknown';
    if ($remark) {
        $stmt = $conn->prepare("INSERT INTO remarks (support_id, user_name, remark) VALUES (?,?,?)");
        $stmt->bind_param("iss", $sid, $uname, $remark);
        $stmt->execute();
    }
    header('Location: nzsupportlist.php' . $preserved_query);
    exit;
}

// Delete (GET)
if (isset($_GET['delete']) && hasPagePermission('nzsupportlist', 'can_delete')) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM nzsupportlist WHERE id = $id");
    $params = $_GET;
    unset($params['delete']);
    $q = $params ? '?' . http_build_query($params) : '';
    header('Location: nzsupportlist.php' . $q);
    exit;
}

// Main POST (add / edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $edit_id = $_POST['edit_id'] ?? '';

    $sup_id = $edit_id ? ($_POST['sup_id'] ?? '') : '';
    $status = $_POST['status'] ?? 'Pending';
    $user_name = $_POST['user_name'] ?? '';
    $device = $_POST['device'] ?? '';
    $support_person = $_POST['support_person'] ?? '';
    $start_date = $_POST['start_date'] ?? '';
    if (!$edit_id) $start_date = date('Y-m-d');
    $description = $_POST['description'] ?? '';
    $remarks = $_POST['remarks'] ?? '';
    $deadline = $_POST['deadline'] ?? '';
    $end_date = ($status === 'Complete') ? date('Y-m-d') : null;

    $remaining_days = 0;
    if ($deadline) {
        $dead = new DateTime($deadline);
        $today = new DateTime();
        $remaining_days = max(0, (int) $today->diff($dead)->days);
    }

    if ($edit_id) {
        if (!hasPagePermission('nzsupportlist', 'can_update')) { header('Location: nzsupportlist.php' . $preserved_query); exit; }

        $old_q = $conn->query("SELECT * FROM nzsupportlist WHERE id = ".intval($edit_id));
        $old_d = $old_q->fetch_assoc();
        $logs = [];
        $check_fields = [
            'status' => 'Status',
            'user_name' => 'User',
            'device' => 'Device',
            'support_person' => 'Staff',
            'deadline' => 'Deadline',
            'description' => 'Desc'
        ];
        foreach ($check_fields as $f => $lbl) {
            $new_v = match($f) {
                'status' => $status,
                'user_name' => $user_name,
                'device' => $device,
                'support_person' => $support_person,
                'deadline' => $deadline,
                'description' => $description,
                default => ''
            };
            $old_v = trim($old_d[$f] ?? '');
            $new_v = trim($new_v);
            if ($old_v != $new_v) {
                $logs[] = "$lbl: " . ($old_v ?: 'None') . " -> " . ($new_v ?: 'None');
            }
        }
        if (!empty($logs)) {
            $log_txt = "Update: " . implode(" | ", $logs);
            $sys_user = $_SESSION['username'] ?? 'System';
            $stmt_l = $conn->prepare("INSERT INTO remarks (support_id, user_name, remark) VALUES (?, ?, ?)");
            $stmt_l->bind_param("iss", $edit_id, $sys_user, $log_txt);
            $stmt_l->execute();
        }

        $stmt = $conn->prepare("UPDATE nzsupportlist SET sup_id=?, status=?, user_name=?, device=?, support_person=?, description=?, remarks=?, deadline=?, remaining_days=?, end_date=? WHERE id=?");
        $stmt->bind_param("ssssssssisi", $sup_id, $status, $user_name, $device, $support_person, $description, $remarks, $deadline, $remaining_days, $end_date, $edit_id);
        $stmt->execute();
    } else {
        if (!hasPagePermission('nzsupportlist', 'can_edit')) { header('Location: nzsupportlist.php' . $preserved_query); exit; }
        $max = $conn->query("SELECT MAX(CAST(SUBSTRING(sup_id,4) AS UNSIGNED)) AS mx FROM nzsupportlist");
        $m = $max->fetch_assoc();
        $next = ($m['mx'] ?? 0) + 1;
        $sup_id = 'NZ-' . str_pad($next, 4, '0', STR_PAD_LEFT);
        $stmt = $conn->prepare("INSERT INTO nzsupportlist (sup_id, status, user_name, device, support_person, start_date, description, remarks, deadline, remaining_days, end_date) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("sssssssssis", $sup_id, $status, $user_name, $device, $support_person, $start_date, $description, $remarks, $deadline, $remaining_days, $end_date);
        $stmt->execute();
    }

    header('Location: nzsupportlist.php' . $preserved_query);
    exit;
}

$edit_row = null;
if (isset($_GET['edit']) && hasPagePermission('nzsupportlist', 'can_update')) {
    $eid = intval($_GET['edit']);
    $res = $conn->query("SELECT * FROM nzsupportlist WHERE id = $eid");
    $edit_row = $res->fetch_assoc();
}

// Server-side pagination & filtering
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 100;
$offset = ($page - 1) * $limit;
$search = trim($_GET['search'] ?? '');
$raw_status = $_GET['status'] ?? [];
$selected_statuses = is_array($raw_status) ? $raw_status : [];

// On initial load (no status in URL), use defaults matching checkboxes
if (empty($selected_statuses) && !array_key_exists('status', $_GET)) {
    $selected_statuses = ['Running', 'Pending', 'Analyzing', 'On Hold'];
}

$where = [];
$params = [];
$types = '';
if ($search !== '') {
    $where[] = "(user_name LIKE ? OR device LIKE ? OR sup_id LIKE ? OR description LIKE ?)";
    $like = "%$search%";
    $params = array_merge($params, [$like, $like, $like, $like]);
    $types .= 'ssss';
}
if (!empty($selected_statuses)) {
    $placeholders = implode(',', array_fill(0, count($selected_statuses), '?'));
    $where[] = "status IN ($placeholders)";
    $params = array_merge($params, $selected_statuses);
    $types .= str_repeat('s', count($selected_statuses));
}
$where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Total count (for pagination)
$count_sql = "SELECT COUNT(*) AS total FROM nzsupportlist $where_clause";
$stmt = $conn->prepare($count_sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['total'];
$total_pages = max(1, ceil($total / $limit));

// Data for current page
$data_sql = "SELECT * FROM nzsupportlist $where_clause ORDER BY id DESC LIMIT $limit OFFSET $offset";
$stmt = $conn->prepare($data_sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Status counts (unfiltered – shows totals)
$status_counts = [];
$cnt_res = $conn->query("SELECT status, COUNT(*) AS cnt FROM nzsupportlist GROUP BY status");
if ($cnt_res) {
    while ($c = $cnt_res->fetch_assoc()) {
        $status_counts[$c['status']] = $c['cnt'];
    }
}

// Default Support Person based on logged-in user
$default_support = 'All IT';
if (isset($_SESSION['username'])) {
    if ($_SESSION['username'] === 'Bappy') $default_support = 'Bappi';
    elseif ($_SESSION['username'] === 'Nayem') $default_support = 'Nayem';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NZ Support List - NZIT Support</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .main-row td { vertical-align: middle; }
        .table-bordered, .table-bordered td, .table-bordered th { border-color: #000 !important; }
        .table-bordered { border: none !important; }
        .main-row.shade-0 td:not(.status-cell) { background: #ffebee; }
        .main-row.shade-1 td:not(.status-cell) { background: #fffde7; }
        .main-row.shade-2 td:not(.status-cell) { background: #e8f5e9; }
        .main-row.shade-3 td:not(.status-cell) { background: #e3f2fd; }
        .detail-row td { border-top: none !important; padding: 0 !important; }
        .btn-3d { position: relative; border: none !important; border-bottom: 4px solid rgba(0,0,0,0.35) !important; box-shadow: 0 5px 15px rgba(0,0,0,0.3) !important; transform: translateY(-1px); transition: all 0.1s ease; }
        .btn-3d:active { transform: translateY(3px) !important; border-bottom-width: 1px !important; box-shadow: 0 2px 5px rgba(0,0,0,0.2) !important; }
        .btn-3d.btn-success { background: linear-gradient(#28a745, #1e7e34) !important; }
        .btn-3d.btn-warning { background: linear-gradient(#ffc107, #dba005) !important; }
        .btn-3d.btn-danger { background: linear-gradient(#dc3545, #b02a37) !important; }
        .main-row { transition: all 0.2s ease; }
        .main-row:hover td:not(.status-cell) { box-shadow: inset 0 -3px 0 #0d6efd, 0 4px 12px rgba(0,0,0,0.12); position: relative; z-index: 1; }
        .status-cell { font-weight: 600; }
        .status-select { width: auto; min-width: 140px; border: 1px solid rgba(255,255,255,0.3); }
        .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .search-dropdown { position: relative; }
        .search-dropdown .dropdown-menu { width:100%; max-height:200px; overflow-y:auto; }
        .search-dropdown .dropdown-item { cursor:pointer; font-size:0.9rem; }
        .search-dropdown .dropdown-item:hover { background:#0d6efd; color:#fff; }
        @media (max-width: 576px) {
            .table-responsive table { font-size: 0.75rem; }
            .table-responsive .btn-sm { padding: 0.1rem 0.25rem; font-size: 0.65rem; }
            .status-select { min-width: 100px; font-size: 0.7rem; }
        }
        .remark-item { transition: all 0.2s ease; }
        .expand-hint:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">NZIT Support</a>
            <div class="d-flex align-items-center gap-2">
                <?php if (isLoggedIn()): ?>
                    <span class="text-light me-2">
                        <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['username']) ?>
                        <?php if (isAdmin()): ?>
                            <span class="badge bg-danger ms-1">ADMIN</span>
                        <?php endif; ?>
                    </span>
                    <a href="index.php" class="btn btn-outline-light btn-sm">
                        <i class="bi bi-house"></i> Home
                    </a>
                    <?php if (isAdmin()): ?>
                        <a href="admin.php" class="btn btn-outline-warning btn-sm">
                            <i class="bi bi-gear"></i> Admin Panel
                        </a>
                    <?php endif; ?>
                    <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
                <?php else: ?>
                    <a href="index.php" class="btn btn-outline-light btn-sm">
                        <i class="bi bi-house"></i> Home
                    </a>
                    <a href="login.php" class="btn btn-outline-light btn-sm">
                        <i class="bi bi-box-arrow-in-right"></i> Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 gap-2">
            <h4 class="mb-0"><i class="bi bi-table"></i> NZ Support List</h4>
            <div class="d-flex gap-2">
                <?php if (isLoggedIn() && hasPagePermission('nzsupportlist', 'can_edit')): ?>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
                        <i class="bi bi-plus-lg"></i> Add New
                    </button>
                <?php endif; ?>
                <?php if (isAdmin()): ?>
                    <a href="import_excel.php" class="btn btn-success btn-sm">
                        <i class="bi bi-file-earmark-excel"></i> Import
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Filter Bar (GET form) -->
        <form method="get" class="card shadow-sm mb-3">
            <div class="card-body py-2 px-3">
                <div class="row g-2 align-items-center">
                    <div class="col-md-4" style="display:none">
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-8">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#statusFilterCollapse">
                                <i class="bi bi-funnel"></i> Status Filter <i class="bi bi-chevron-down"></i>
                            </button>
                            <button class="btn btn-outline-primary btn-sm" type="button" onclick="sortByPriority()" title="Sort by Priority"><i class="bi bi-sort-down"></i></button>
                        </div>
                        <div class="collapse show mt-2" id="statusFilterCollapse">
                            <div class="d-flex flex-wrap align-items-center gap-2 p-2 border rounded bg-light">
                                <?php foreach ($status_order as $st_name => $st_abbr):
                                    $checked = in_array($st_name, $selected_statuses) || (empty($selected_statuses) && in_array($st_name, ['Running','Pending','Analyzing','On Hold']));
                                ?>
                                    <label class="form-check-label small" style="cursor:pointer">
                                        <input type="checkbox" name="status[]" class="form-check-input status-filter" value="<?= $st_name ?>" <?= $checked ? 'checked' : '' ?> onchange="this.form.submit()">
                                        <?= $st_name ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- Status Counter -->
        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
            <?php foreach ($status_order as $status => $abbr): ?>
                <?php $cnt = $status_counts[$status] ?? 0; ?>
                <span class="badge fw-bold" style="padding:0.2rem 0.35rem;background:<?= $colors[$status] ?>;color:<?= $text_colors[$status] ?>;font-size:0.7rem;">
                    <?= $abbr ?>=<?= $cnt ?>
                </span>
            <?php endforeach; ?>
        </div>

        <div class="card shadow">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>SupID</th>
                                <th>Status</th>
                                <th>User Name</th>
                                <th>Device</th>
                                <th width="50"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php $shade_idx = 0; while ($row = $result->fetch_assoc()): ?>
                                    <tr class="main-row shade-<?= $shade_idx ?>" data-id="<?= $row['id'] ?>">
                                        <td style="cursor:pointer;" class="sup-id-cell">
                                            <strong class="text-primary"><?= htmlspecialchars($row['sup_id']) ?></strong>
                                        </td>
                                        <?php
                                        $st = trim($row['status']);
                                        $bg = $colors[$st] ?? '#6c757d';
                                        $tc = $text_colors[$st] ?? '#fff';
                                        ?>
                                        <td class="status-cell" style="cursor:pointer;background:<?= $bg ?>;color:<?= $tc ?>;">
                                            <span class="status-badge fw-bold"><?= htmlspecialchars($row['status']) ?></span>
                                            <?php if (isLoggedIn() && hasPagePermission('nzsupportlist', 'can_update')): ?>
                                                <form method="post" style="display:none" class="status-form">
                                                    <input type="hidden" name="edit_id" value="<?= $row['id'] ?>">
                                                    <input type="hidden" name="sup_id" value="<?= htmlspecialchars($row['sup_id']) ?>">
                                                    <input type="hidden" name="user_name" value="<?= htmlspecialchars($row['user_name']) ?>">
                                                    <input type="hidden" name="device" value="<?= htmlspecialchars($row['device']) ?>">
                                                    <input type="hidden" name="support_person" value="<?= htmlspecialchars($row['support_person']) ?>">
                                                    <input type="hidden" name="description" value="<?= htmlspecialchars($row['description']) ?>">
                                                    <input type="hidden" name="remarks" value="<?= htmlspecialchars($row['remarks']) ?>">
                                                    <input type="hidden" name="deadline" value="<?= htmlspecialchars($row['deadline']) ?>">
                                                    <select name="status" class="form-select form-select-sm status-select" onchange="this.form.submit()" style="background:<?= $bg ?>;color:<?= $tc ?>;">
                                                        <?php foreach ($status_order as $st_name => $st_abbr):
                                                            if ($st_name === 'Complete') continue; ?>
                                                            <option value="<?= $st_name ?>" <?= $row['status']==$st_name?'selected':'' ?>><?= $st_name ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong><?= htmlspecialchars($row['user_name']) ?></strong></td>
                                        <td><?= htmlspecialchars($row['device']) ?></td>
                                        <td class="text-center" style="cursor:pointer;">
                                            <i class="bi bi-plus toggle-icon text-primary"></i>
                                        </td>
                                    </tr>
                                    <tr class="detail-row" style="display:none" data-id="<?= $row['id'] ?>">
                                        <td colspan="5">
                                            <div class="detail-inner" id="detail-inner-<?= $row['id'] ?>">
                                                <div class="p-3 bg-light">
                                                <div class="row g-3">
                                                    <div class="col-md-3">
                                                        <small class="text-muted d-block">Support Person</small>
                                                        <strong><?= htmlspecialchars($row['support_person']) ?: '-' ?></strong>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <small class="text-muted d-block">Start Date</small>
                                                        <strong><?= htmlspecialchars($row['start_date']) ?: '-' ?></strong>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <small class="text-muted d-block">Dead Line</small>
                                                        <strong><?= htmlspecialchars($row['deadline']) ?: '-' ?></strong>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <small class="text-muted d-block">Remaining Days</small>
                                                        <?php if ($row['remaining_days'] > 0): ?>
                                                            <span class="badge bg-info"><?= $row['remaining_days'] ?> days</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger">Overdue</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <small class="text-muted d-block">End Date</small>
                                                        <strong><?= htmlspecialchars($row['end_date']) ?: '-' ?></strong>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <small class="text-muted d-block">Description</small>
                                                        <strong><?= nl2br(htmlspecialchars($row['description'])) ?: '-' ?></strong>
                                                    </div>
                                                    <div class="col-12">
                                                        <small class="text-muted d-block mb-1" style="cursor:pointer" onclick="var c=this.nextElementSibling; if(c && c.classList.contains('remarks-container')) expandRemarks(c)">Remarks</small>
                                                        <?php
                                                        $rem_res = $conn->query("SELECT * FROM remarks WHERE support_id = {$row['id']} ORDER BY created_at ASC");
                                                        $all_rems = [];
                                                        if ($rem_res) {
                                                            while($r = $rem_res->fetch_assoc()) $all_rems[] = $r;
                                                        }
                                                        $rem_count = count($all_rems);
                                                        if ($rem_count > 0): ?>
                                                            <div class="remarks-container" style="cursor:pointer" onclick="expandRemarks(this)">
                                                                <?php foreach ($all_rems as $idx => $rem):
                                                                    $is_latest = ($idx === $rem_count - 1);
                                                                    ?>
                                                                    <div class="border rounded p-2 mb-1 bg-white remark-item <?= !$is_latest ? 'd-none' : '' ?>" style="font-size:0.85rem;">
                                                                        <div class="d-flex justify-content-between">
                                                                            <strong><?= htmlspecialchars($rem['user_name']) ?></strong>
                                                                            <small class="text-muted"><?= date('d-m-Y H:i', strtotime($rem['created_at'])) ?></small>
                                                                        </div>
                                                                        <div><?= nl2br(htmlspecialchars($rem['remark'])) ?></div>
                                                                        <?php if ($is_latest && $rem_count > 1): ?>
                                                                            <div class="text-center border-top mt-1 pt-1 expand-hint">
                                                                                <small class="text-primary">View all remarks (<?= $rem_count ?>)</small>
                                                                            </div>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="text-muted small mb-2">-</div>
                                                        <?php endif; ?>
                                                        <?php if (isLoggedIn() && hasPagePermission('nzsupportlist', 'can_update')): ?>
                                                            <form method="post" class="mt-1">
                                                                <input type="hidden" name="add_remark_id" value="<?= $row['id'] ?>">
                                                                <div class="input-group input-group-sm">
                                                                    <input type="text" name="remark_text" class="form-control" placeholder="Write a remark..." required>
                                                                    <button type="submit" class="btn btn-outline-primary btn-sm">Add</button>
                                                                </div>
                                                            </form>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php if (isLoggedIn() && (hasPagePermission('nzsupportlist', 'can_update') || hasPagePermission('nzsupportlist', 'can_delete'))): ?>
                                                    <div class="col-12 d-flex justify-content-between align-items-center">
                                                        <div>
                                                        <?php if (hasPagePermission('nzsupportlist', 'can_update') && $row['status'] !== 'Complete'): ?>
                                                            <form method="post" style="display:inline">
                                                                <input type="hidden" name="close_id" value="<?= $row['id'] ?>">
                                                                <button type="submit" class="btn btn-sm btn-success btn-3d">
                                                                    <i class="bi bi-check-lg"></i> Close
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                        </div>
                                                        <div>
                                                        <?php if (hasPagePermission('nzsupportlist', 'can_delete')): ?>
                                                            <?php $del_params = array_merge($_GET, ['delete' => $row['id']]); unset($del_params['edit']); ?>
                                                            <a href="?<?= http_build_query($del_params) ?>" class="btn btn-sm btn-danger btn-3d" onclick="return confirm('Delete this entry?')">
                                                                <i class="bi bi-trash"></i> Delete
                                                            </a>
                                                        <?php endif; ?>
                                                        </div>
                                                        <div>
                                                        <?php if (hasPagePermission('nzsupportlist', 'can_update')): ?>
                                                            <?php $edit_params = array_merge($_GET, ['edit' => $row['id']]); unset($edit_params['delete']); ?>
                                                            <a href="?<?= http_build_query($edit_params) ?>" class="btn btn-sm btn-warning btn-3d">
                                                                <i class="bi bi-pencil"></i> Edit
                                                            </a>
                                                        <?php endif; ?>
                                                        </div>
                                                        <div>
                                                            <button type="button" class="btn btn-sm btn-info btn-3d text-white" onclick="printEntry(<?= $row['id'] ?>, '<?= htmlspecialchars($row['sup_id']) ?>')">
                                                                <i class="bi bi-printer"></i> Print
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        </td>
                                    </tr>
                                <?php $shade_idx = ($shade_idx + 1) % 4; endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox"></i> No entries yet.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Server-side Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <small class="text-muted">Page <?= $page ?> of <?= $total_pages ?> (<?= $total ?> entries)</small>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <?php
                    $page_params = $preserved_params;
                    unset($page_params['page']);
                    $prev_disabled = $page <= 1 ? ' disabled' : '';
                    ?>
                    <li class="page-item<?= $prev_disabled ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($page_params, ['page' => $page - 1])) ?>">&laquo;</a>
                    </li>
                    <?php
                    $start_p = max(1, $page - 2);
                    $end_p = min($total_pages, $page + 2);
                    if ($start_p > 1): ?>
                        <li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($page_params, ['page' => 1])) ?>">1</a></li>
                        <?php if ($start_p > 2): ?>
                            <li class="page-item disabled"><a class="page-link" href="#">...</a></li>
                        <?php endif; ?>
                    <?php endif;
                    for ($p = $start_p; $p <= $end_p; $p++):
                        $active = $p == $page ? ' active' : ''; ?>
                        <li class="page-item<?= $active ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($page_params, ['page' => $p])) ?>"><?= $p ?></a>
                        </li>
                    <?php endfor;
                    if ($end_p < $total_pages):
                        if ($end_p < $total_pages - 1): ?>
                            <li class="page-item disabled"><a class="page-link" href="#">...</a></li>
                        <?php endif; ?>
                        <li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($page_params, ['page' => $total_pages])) ?>"><?= $total_pages ?></a></li>
                    <?php endif; ?>
                    <?php $next_disabled = $page >= $total_pages ? ' disabled' : ''; ?>
                    <li class="page-item<?= $next_disabled ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($page_params, ['page' => $page + 1])) ?>">&raquo;</a>
                    </li>
                </ul>
            </nav>
        </div>
        <?php endif; ?>

        <?php if (!isLoggedIn()): ?>
            <div class="text-center mt-3">
                <a href="login.php" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-box-arrow-in-right"></i> Login to add or edit entries
                </a>
            </div>
        <?php endif; ?>
    </div>

    <?php if (isLoggedIn() && hasPagePermission('nzsupportlist', 'can_edit')): ?>
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="post">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="bi bi-plus-lg"></i> New Support Entry</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <?php foreach ($status_order as $st_name => $st_abbr):
                                        if ($st_name === 'Complete') continue; ?>
                                        <option value="<?= $st_name ?>"><?= $st_name ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">User Name</label>
                                <input type="text" name="user_name" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Device</label>
                                <div class="search-dropdown">
                                    <input type="text" class="form-control" readonly placeholder="-- Select --" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                                    <input type="hidden" name="device" class="device-value" value="">
                                    <div class="dropdown-menu">
                                        <input type="text" class="form-control form-control-sm mb-1 dropdown-search" placeholder="&#x1F50D; Search device..." style="position:sticky;top:0;z-index:1;">
                                        <a class="dropdown-item" data-value="">-- Select --</a>
                                        <?php if ($devices && $devices->num_rows > 0):
                                            $devices->data_seek(0);
                                            while ($d = $devices->fetch_assoc()): ?>
                                            <a class="dropdown-item" data-value="<?= htmlspecialchars($d['name']) ?>"><?= htmlspecialchars($d['name']) ?></a>
                                        <?php endwhile; endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Support Person</label>
                                <select name="support_person" class="form-select">
                                    <option value="">-- Select --</option>
                                    <option value="Bappi" <?= (!isset($default_support) || $default_support=='Bappi')?'selected':'' ?>>Bappi</option>
                                    <option value="Nayem" <?= (isset($default_support) && $default_support=='Nayem')?'selected':'' ?>>Nayem</option>
                                    <option value="All IT" <?= (isset($default_support) && $default_support=='All IT')?'selected':'' ?>>All IT</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Dead Line</label>
                                <input type="date" name="deadline" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Remarks</label>
                                <textarea name="remarks" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($edit_row && isLoggedIn()): ?>
    <div class="modal fade show" id="editModal" tabindex="-1" style="display:block;background:rgba(0,0,0,.5);">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="post">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title"><i class="bi bi-pencil"></i> Edit Support Entry</h5>
                        <a href="nzsupportlist.php<?= $preserved_query ?>" class="btn-close"></a>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="edit_id" value="<?= $edit_row['id'] ?>">
                        <input type="hidden" name="sup_id" value="<?= htmlspecialchars($edit_row['sup_id']) ?>">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">SupID</label>
                                <p class="fw-bold text-primary pt-2"><?= htmlspecialchars($edit_row['sup_id']) ?></p>
                            </div>
                            <div class="col-md-3">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <?php foreach ($status_order as $st_name => $st_abbr):
                                            if ($st_name === 'Complete') continue; ?>
                                            <option value="<?= $st_name ?>" <?= $edit_row['status']==$st_name?'selected':'' ?>><?= $st_name ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <div class="col-md-3">
                                <label class="form-label">User Name</label>
                                <input type="text" name="user_name" class="form-control" value="<?= htmlspecialchars($edit_row['user_name']) ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Device</label>
                                <div class="search-dropdown">
                                    <input type="text" class="form-control" readonly placeholder="<?= htmlspecialchars($edit_row['device']) ?: '-- Select --' ?>" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                                    <input type="hidden" name="device" class="device-value" value="<?= htmlspecialchars($edit_row['device']) ?>">
                                    <div class="dropdown-menu">
                                        <input type="text" class="form-control form-control-sm mb-1 dropdown-search" placeholder="&#x1F50D; Search device..." style="position:sticky;top:0;z-index:1;">
                                        <a class="dropdown-item" data-value="">-- Select --</a>
                                        <?php if ($devices && $devices->num_rows > 0):
                                            $devices->data_seek(0);
                                            while ($d = $devices->fetch_assoc()): ?>
                                            <a class="dropdown-item" data-value="<?= htmlspecialchars($d['name']) ?>"><?= htmlspecialchars($d['name']) ?></a>
                                        <?php endwhile; endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Support Person</label>
                                <select name="support_person" class="form-select">
                                    <option value="">-- Select --</option>
                                    <option value="Bappi" <?= $edit_row['support_person']=='Bappi'?'selected':'' ?>>Bappi</option>
                                    <option value="Nayem" <?= $edit_row['support_person']=='Nayem'?'selected':'' ?>>Nayem</option>
                                    <option value="All IT" <?= $edit_row['support_person']=='All IT'?'selected':'' ?>>All IT</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Dead Line</label>
                                <input type="date" name="deadline" class="form-control" value="<?= htmlspecialchars($edit_row['deadline']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="2"><?= htmlspecialchars($edit_row['description']) ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Remarks</label>
                                <textarea name="remarks" class="form-control" rows="2"><?= htmlspecialchars($edit_row['remarks']) ?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="nzsupportlist.php<?= $preserved_query ?>" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-warning"><i class="bi bi-check-lg"></i> Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
    // Save original row order on page load
    var originalOrder = [];
    document.querySelectorAll('.main-row').forEach(function(r) {
        originalOrder.push(r.dataset.id);
    });

    // Sort by priority toggle
    var prioritySorted = true;
    var priorityMap = {};
    <?php $idx = 1; foreach ($status_order as $st => $abbr): ?>
    priorityMap['<?= $st ?>'] = <?= $idx++ ?>;
    <?php endforeach; ?>

    function sortByPriority() {
        var tbody = document.querySelector('table tbody');
        prioritySorted = !prioritySorted;

        if (!prioritySorted) {
            originalOrder.forEach(function(id) {
                var row = document.querySelector('.main-row[data-id="' + id + '"]');
                var detail = document.querySelector('.detail-row[data-id="' + id + '"]');
                if (row) tbody.appendChild(row);
                if (detail) tbody.appendChild(detail);
            });
            return;
        }

        var rows = Array.from(tbody.querySelectorAll('.main-row'));

        rows.sort(function(a, b) {
            var sa = a.querySelector('.status-badge') ? a.querySelector('.status-badge').textContent.trim() : '';
            var sb = b.querySelector('.status-badge') ? b.querySelector('.status-badge').textContent.trim() : '';
            return (priorityMap[sa] || 99) - (priorityMap[sb] || 99);
        });

        rows.forEach(function(row) {
            var detail = document.querySelector('.detail-row[data-id="' + row.dataset.id + '"]');
            tbody.appendChild(row);
            if (detail) tbody.appendChild(detail);
        });
    }

    // Apply priority sort on load
    prioritySorted = false;
    sortByPriority();

    // Row expand/collapse
    document.querySelectorAll('.main-row').forEach(function(row) {
        var toggleIcon = row.querySelector('.toggle-icon');
        var supIdCell = row.querySelector('.sup-id-cell');
        var detailRow = document.querySelector('.detail-row[data-id="' + row.dataset.id + '"]');

        function toggle() {
            if (detailRow.style.display === 'none') {
                detailRow.style.display = '';
                toggleIcon.className = 'bi bi-dash toggle-icon text-primary';
            } else {
                detailRow.style.display = 'none';
                toggleIcon.className = 'bi bi-plus toggle-icon text-primary';
            }
        }

        if (toggleIcon) toggleIcon.addEventListener('click', toggle);
        if (supIdCell) supIdCell.addEventListener('click', toggle);
    });

    // Inline status edit
    document.querySelectorAll('.status-cell').forEach(function(cell) {
        var badge = cell.querySelector('.status-badge');
        var form = cell.querySelector('.status-form');
        var select = cell.querySelector('.status-select');
        if (badge && form && select) {
            badge.addEventListener('click', function(e) {
                e.stopPropagation();
                badge.style.display = 'none';
                form.style.display = 'inline';
                select.focus();
            });
            select.addEventListener('blur', function() {
                setTimeout(function() {
                    badge.style.display = '';
                    form.style.display = 'none';
                }, 200);
            });
        }
    });

    function expandRemarks(container) {
        const hiddenItems = container.querySelectorAll('.remark-item.d-none');
        if (hiddenItems.length > 0) {
            hiddenItems.forEach(item => item.classList.remove('d-none'));
            const hint = container.querySelector('.expand-hint');
            if (hint) hint.remove();
            container.style.cursor = 'default';
        }
    }
    </script>
    <?php if ($edit_row && isLoggedIn()): ?>
    <script>
        var editModal = new bootstrap.Modal(document.getElementById('editModal'));
        editModal.show();
    </script>
    <?php endif; ?>
    <script>
    // Searchable dropdown for Device
    document.querySelectorAll('.search-dropdown').forEach(function(dd) {
        var input = dd.querySelector('input[data-bs-toggle="dropdown"]');
        var hidden = dd.querySelector('.device-value');
        var menu = dd.querySelector('.dropdown-menu');
        var search = dd.querySelector('.dropdown-search');
        var items = dd.querySelectorAll('.dropdown-item');

        function highlight(val) {
            input.value = val || '-- Select --';
            if (hidden) hidden.value = val;
        }

        items.forEach(function(item) {
            item.addEventListener('click', function() {
                highlight(this.dataset.value);
            });
        });

        if (search) {
            search.addEventListener('keyup', function(e) {
                e.stopPropagation();
                var q = this.value.toLowerCase().trim();
                items.forEach(function(item) {
                    item.style.display = (!q || item.textContent.toLowerCase().indexOf(q) > -1) ? '' : 'none';
                });
            });
            search.addEventListener('click', function(e) { e.stopPropagation(); });
        }
    });

    // Print Ticket function (Reliable across PC and Mobile)
    function printEntry(id, supId) {
        const row = document.querySelector('.main-row[data-id="' + id + '"]');
        if (!row) return;
        const status = row.querySelector('.status-badge').textContent.trim();
        const userName = row.cells[2].textContent.trim();
        const device = row.cells[3].textContent.trim();

        const detailRow = document.querySelector('.detail-row[data-id="' + id + '"]');
        const supportPerson = detailRow.querySelector('.col-md-3:nth-of-type(1) strong')?.textContent || '-';
        const startDate = detailRow.querySelector('.col-md-3:nth-of-type(2) strong')?.textContent || '-';
        const deadline = detailRow.querySelector('.col-md-3:nth-of-type(3) strong')?.textContent || '-';
        const endDate = detailRow.querySelector('.col-md-3:nth-of-type(5) strong')?.textContent || '-';
        const description = detailRow.querySelector('.col-md-6 strong')?.textContent || '-';
        const remainingDaysBadge = detailRow.querySelector('.badge');

        let badgeHtml = '-';
        if (remainingDaysBadge) {
            const type = remainingDaysBadge.classList.contains('bg-info') ? 'bg-info' : 'bg-danger';
            badgeHtml = `<span class="badge ${type}">${remainingDaysBadge.textContent.trim()}</span>`;
        }

        const remarksHtml = Array.from(detailRow.querySelectorAll('.remark-item')).map(rem => {
            const user = rem.querySelector('strong').textContent;
            const time = rem.querySelector('small').textContent;
            const text = rem.querySelector('div:not(.d-flex)').innerHTML
                .replace(/<div class="text-center border-top mt-1 pt-1 expand-hint">.*?<\/div>/g, '')
                .replace(/<small class="text-primary">.*?<\/small>/g, '');
            return `<div class="remark-item"><div class="remark-meta"><span>${user}</span><span>${time}</span></div><div class="remark-text">${text}</div></div>`;
        }).join('');

        let printFrame = document.getElementById('printFrame');
        if (!printFrame) {
            printFrame = document.createElement('iframe');
            printFrame.id = 'printFrame';
            printFrame.style.display = 'none';
            document.body.appendChild(printFrame);
        }

        const doc = printFrame.contentWindow.document;
        doc.open();
        doc.write(`
            <html>
            <head>
                <title>Print Ticket - ${supId}</title>
                <style>
                    body { font-family: 'Segoe UI', Arial, sans-serif; padding: 20px; color: #333; line-height: 1.4; }
                    .header { text-align: center; margin-bottom: 20px; }
                    .header h1 { margin: 0; font-size: 24px; font-weight: normal; color: #222; }
                    .header .id { margin-top: 5px; font-size: 14px; font-weight: bold; }
                    hr { border: 0; border-top: 2px solid #000; margin-top: 15px; margin-bottom: 0; }
                    table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
                    .summary-table th { background: #f5f5f5; border: 1px solid #ddd; padding: 8px 12px; font-size: 9px; color: #888; text-transform: uppercase; text-align: left; font-weight: normal; }
                    .summary-table td { border: 1px solid #ddd; padding: 10px 12px; font-weight: bold; font-size: 13px; }
                    .details-grid { display: flex; flex-wrap: wrap; margin-bottom: 20px; }
                    .details-item { width: 25%; margin-bottom: 15px; }
                    .details-item.wide { width: 75%; }
                    .label { font-size: 10px; color: #999; margin-bottom: 3px; }
                    .value { font-weight: bold; font-size: 11px; }
                    .remarks-section { margin-top: 25px; }
                    .remarks-title { font-size: 11px; color: #999; margin-bottom: 8px; }
                    .remarks-box { background: #fcfcfc; border: 1px solid #f0f0f0; border-radius: 4px; padding: 10px; }
                    .remark-item { border-bottom: 1px solid #eee; padding: 6px 0; margin-bottom: 4px; }
                    .remark-meta { display: flex; justify-content: space-between; font-size: 9px; color: #888; margin-bottom: 2px; }
                    .remark-text { font-size: 10px; color: #444; line-height: 1.3; }
                    .signatures { margin-top: 70px; display: flex; justify-content: space-between; }
                    .sig-block { width: 45%; text-align: center; border-top: 1.5px solid #000; padding-top: 6px; }
                    .sig-name { font-weight: bold; font-size: 12px; }
                    .sig-label { font-size: 10px; color: #777; }
                    .footer { text-align: right; margin-top: 40px; font-size: 8px; color: #bbb; }
                    .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 9px; color: #fff; }
                    .bg-info { background-color: #0dcaf0; }
                    .bg-danger { background-color: #dc3545; }
                    @media print { body { padding: 0; } @page { margin: 0.5in; } }
                </style>
            </head>
            <body>
                <div class="header"><h1>NZ SUPPORT TICKET</h1><div class="id">Ticket ID: ${supId}</div><hr></div>
                <table class="summary-table"><tr><th>User Name</th><th>Device</th><th>Status</th></tr><tr><td>${userName}</td><td>${device}</td><td style="color: ${status === 'Complete' ? '#dc3545' : '#0d6efd'}">${status}</td></tr></table>
                <div class="details-grid"><div class="details-item"><div class="label">Support Person</div><div class="value">${supportPerson}</div></div><div class="details-item"><div class="label">Start Date</div><div class="value">${startDate}</div></div><div class="details-item"><div class="label">Dead Line</div><div class="value">${deadline}</div></div><div class="details-item"><div class="label">Remaining Days</div><div class="value">${badgeHtml}</div></div><div class="details-item"><div class="label">End Date</div><div class="value">${endDate}</div></div><div class="details-item wide"><div class="label">Description</div><div class="value">${description}</div></div></div>
                <div class="remarks-section"><div class="remarks-title">Remarks</div><div class="remarks-box">${remarksHtml || 'No remarks available'}</div></div>
                <div class="signatures"><div class="sig-block"><div class="sig-name">${userName}</div><div class="sig-label">User Signature</div></div><div class="sig-block"><div class="sig-name">IT Support</div><div class="sig-label">Authorized Signature</div></div></div>
                <div class="footer">Generated: ${new Date().toLocaleString()}</div>
            </body>
            </html>
        `);
        doc.close();

        setTimeout(() => {
            printFrame.contentWindow.focus();
            printFrame.contentWindow.print();
        }, 500);
    }
    </script>
</body>
</html>
