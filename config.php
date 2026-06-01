<?php
if (file_exists(__DIR__ . '/maintenance.php')) { require __DIR__ . '/maintenance.php'; exit; }
mysqli_report(MYSQLI_REPORT_OFF);
session_start();

$envFile = __DIR__ . '/.env';
$env = file_exists($envFile) ? parse_ini_file($envFile, false, INI_SCANNER_RAW) : [];
$env = is_array($env) ? $env : [];

date_default_timezone_set($env['APP_TIMEZONE'] ?? 'Asia/Dhaka');

function envv($key, $default = '') {
    global $env;
    return $env[$key] ?? $default;
}

$conn = @new mysqli(
    envv('DB_LOCAL_HOST', 'localhost'),
    envv('DB_LOCAL_USER', 'root'),
    envv('DB_LOCAL_PASS', ''),
    envv('DB_LOCAL_NAME', 'nzitsupport')
);
if ($conn->connect_error) {
    $conn = @new mysqli(
        envv('DB_REMOTE_HOST'),
        envv('DB_REMOTE_USER'),
        envv('DB_REMOTE_PASS'),
        envv('DB_REMOTE_NAME')
    );
}
if ($conn->connect_error) {
    die("<h3>Database Connection Error</h3><p>Please check your database credentials or import db_backup.sql via phpMyAdmin.</p>");
}


function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function hasPermission($perm) {
    if (isAdmin()) return true;
    return isset($_SESSION[$perm]) && $_SESSION[$perm] == 1;
}

function hasPagePermission($pageName, $perm) {
    if (isAdmin()) return true;
    return isset($_SESSION['page_perms'][$pageName][$perm]) && $_SESSION['page_perms'][$pageName][$perm] == 1;
}

function loadPagePermissions($userId) {
    global $conn;
    $page_perms = [];
    $stmt = $conn->prepare("SELECT page_name, can_view, can_edit, can_delete, can_update FROM user_page_permissions WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $page_perms[$row['page_name']] = [
            'can_view' => $row['can_view'],
            'can_edit' => $row['can_edit'],
            'can_delete' => $row['can_delete'],
            'can_update' => $row['can_update'],
        ];
    }
    $_SESSION['page_perms'] = $page_perms;
}

function isPagePublic($pageName) {
    global $conn;
    $stmt = $conn->prepare("SELECT is_public FROM page_permissions WHERE page_name = ? LIMIT 1");
    $stmt->bind_param("s", $pageName);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row && $row['is_public'] == 1;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}
