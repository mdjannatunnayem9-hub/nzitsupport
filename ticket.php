<?php
require_once 'config.php';

$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) { echo "Invalid ticket."; exit; }

// Handle add remark
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_remark'])) {
    $remark = trim($_POST['remark_text'] ?? '');
    if ($remark) {
        $res_chk = $conn->query("SELECT * FROM nzsupportlist WHERE id = $id");
        $row_chk = $res_chk->fetch_assoc();
        $uname = isLoggedIn() ? $_SESSION['username'] : ($row_chk['user_name'] ?? 'Guest');
        $stmt = $conn->prepare("INSERT INTO remarks (support_id, user_name, remark) VALUES (?,?,?)");
        $stmt->bind_param("iss", $id, $uname, $remark);
        $stmt->execute();
    }
    header("Location: ticket.php?id=$id");
    exit;
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    requireLogin();
    $new_status = trim($_POST['status'] ?? '');
    if ($new_status) {
        $old_q = $conn->query("SELECT status FROM nzsupportlist WHERE id = $id");
        $old_st = $old_q->fetch_assoc()['status'] ?? '';
        $end_date = ($new_status === 'Complete') ? date('Y-m-d') : null;
        if ($end_date) {
            $conn->query("UPDATE nzsupportlist SET status='$new_status', end_date='$end_date' WHERE id=$id");
        } else {
            $conn->query("UPDATE nzsupportlist SET status='$new_status' WHERE id=$id");
        }
        if ($old_st !== $new_status) {
            $uname = $_SESSION['username'] ?? 'System';
            $log_rem = "Status: $old_st -> $new_status";
            $stmt_l = $conn->prepare("INSERT INTO remarks (support_id, user_name, remark) VALUES (?, ?, ?)");
            $stmt_l->bind_param("iss", $id, $uname, $log_rem);
            $stmt_l->execute();
        }
    }
    header("Location: ticket.php?id=$id");
    exit;
}

$res = $conn->query("SELECT * FROM nzsupportlist WHERE id = $id");
$row = $res->fetch_assoc();
if (!$row) { echo "Ticket not found."; exit; }

$uinfo = $conn->query("SELECT * FROM user_registry WHERE name = '{$conn->real_escape_string($row['user_name'])}'")->fetch_assoc();

$rem_res = $conn->query("SELECT * FROM remarks WHERE support_id = $id ORDER BY created_at ASC");
$all_rems = [];
if ($rem_res) {
    while ($r = $rem_res->fetch_assoc()) $all_rems[] = $r;
}

$is_support = isLoggedIn() && ($_SESSION['username'] === $row['support_person'] || isAdmin());

$wa_ticket_link = $base_url . '/ticket.php?id=' . $row['id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket <?= htmlspecialchars($row['sup_id']) ?> - NZIT Support</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f5f5f5; }
        .ticket-card { max-width: 800px; margin: 40px auto; }
        .info-label { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px; color: #888; margin-bottom: 2px; }
        .info-value { font-weight: 600; font-size: 0.95rem; color: #333; }
    </style>
</head>
<body style="overflow-x:hidden;">
    <div class="container ticket-card">
        <div class="card shadow border-0">
            <div class="card-header bg-dark text-white py-3 position-relative">
                <div class="text-center">
                    <h5 class="mb-0"><i class="bi bi-ticket"></i> NZ SUPPORT TICKET</h5>
                    <div class="fw-bold" style="color:#fff;font-size:0.9rem;"><?= htmlspecialchars($row['sup_id']) ?></div>
                </div>
                <?php if ($is_support): ?>
                <a href="https://api.whatsapp.com/send?text=<?= rawurlencode("Ticket: {$row['sup_id']}\n$wa_ticket_link") ?>" target="_blank" class="btn btn-success btn-sm position-absolute top-50 end-0 translate-middle-y me-3" title="Share on WhatsApp">
                    <i class="bi bi-whatsapp"></i> Share
                </a>
                <?php endif; ?>
            </div>
            <div class="card-body p-4">
                <?php if ($is_support): ?>
                <form method="post" class="row g-2 align-items-end mb-4 p-3 rounded" style="background:#fff3cd;">
                    <div class="col-md-4">
                        <label class="info-label">Update Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <?php
                            $st_res = $conn->query("SELECT * FROM statuses ORDER BY sort_order");
                            while ($s = $st_res->fetch_assoc()):
                            ?>
                                <option value="<?= htmlspecialchars($s['name']) ?>" <?= $s['name'] === $row['status'] ? 'selected' : '' ?>><?= htmlspecialchars($s['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" name="update_status" class="btn btn-warning btn-sm"><i class="bi bi-arrow-repeat"></i> Update</button>
                        <a href="nzsupportlist.php?edit=<?= $id ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil"></i> Edit</a>
                    </div>
                </form>
                <?php endif; ?>

                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="p-3 rounded" style="background:#e3f2fd">
                            <div class="info-label">Status</div>
                            <div class="info-value"><?= htmlspecialchars($row['status']) ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded" style="background:#e3f2fd">
                            <div class="info-label">User Name</div>
                            <div class="info-value"><?= htmlspecialchars($row['user_name']) ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded" style="background:#e3f2fd">
                            <div class="info-label">Device</div>
                            <div class="info-value"><?= htmlspecialchars($row['device']) ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded" style="background:#fff3e0">
                            <div class="info-label">Support Person</div>
                            <div class="info-value"><?= htmlspecialchars($row['support_person']) ?: '-' ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded" style="background:#fff3e0">
                            <div class="info-label">Designation</div>
                            <div class="info-value"><?= htmlspecialchars($uinfo['designation'] ?? '-') ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded" style="background:#fff3e0">
                            <div class="info-label">Department</div>
                            <div class="info-value"><?= htmlspecialchars($uinfo['department'] ?? '-') ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded" style="background:#f3e5f5">
                            <div class="info-label">Start Date</div>
                            <div class="info-value"><?= htmlspecialchars($row['start_date']) ?: '-' ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded" style="background:#f3e5f5">
                            <div class="info-label">Deadline</div>
                            <div class="info-value"><?= htmlspecialchars($row['deadline']) ?: '-' ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded" style="background:#f3e5f5">
                            <div class="info-label">Remaining Days</div>
                            <div class="info-value">
                                <?php if (!$row['deadline'] || $row['deadline'] === '0000-00-00'): ?>
                                    <strong>—</strong>
                                <?php elseif ($row['status'] === 'Complete'): ?>
                                    <span class="badge bg-secondary">Completed</span>
                                <?php elseif ($row['remaining_days'] > 0): ?>
                                    <span class="badge bg-info"><?= $row['remaining_days'] ?> days</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Overdue</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded" style="background:#f3e5f5">
                            <div class="info-label">End Date</div>
                            <div class="info-value"><?= htmlspecialchars($row['end_date']) ?: '-' ?></div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="p-3 rounded" style="background:#e8f5e9">
                            <div class="info-label">Description</div>
                            <div class="info-value"><?= nl2br(htmlspecialchars($row['description'])) ?: '-' ?></div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="info-label mb-2">Remarks</div>
                        <?php if ($all_rems): ?>
                            <?php foreach ($all_rems as $rem): ?>
                                <div class="bg-light p-2 rounded mb-1" style="border-left:3px solid #0d6efd;">
                                    <small class="text-muted">
                                        <strong><?= htmlspecialchars($rem['user_name']) ?></strong>
                                        &middot; <?= htmlspecialchars($rem['created_at']) ?>
                                    </small>
                                    <div class="mt-1"><?= nl2br(htmlspecialchars($rem['remark'])) ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted small">No remarks yet.</p>
                        <?php endif; ?>

                        <form method="post" class="mt-3">
                            <div class="input-group">
                                <input type="text" name="remark_text" class="form-control form-control-sm" placeholder="<?= isLoggedIn() ? 'Add a remark...' : "Add remark as " . htmlspecialchars($row['user_name']) ?>..." required>
                                <button type="submit" name="add_remark" class="btn btn-primary btn-sm"><i class="bi bi-send"></i></button>
                            </div>
                            <?php if (!isLoggedIn()): ?>
                            <small class="text-muted">Posting as <strong><?= htmlspecialchars($row['user_name']) ?></strong></small>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
