<?php
session_start();

// Auto-detect environment
$is_local = isset($_SERVER['SERVER_NAME']) && ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1');

if ($is_local) {
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $db   = 'nzitsupport';
} else {
    $host = 'sql306.infinityfree.com';
    $user = 'if0_41848546';
    $pass = 'nayem1999';
    $db   = 'if0_41848546_nzitsupport';
}

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
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
    $res = $conn->query("SELECT page_name, can_view, can_edit, can_delete, can_update FROM user_page_permissions WHERE user_id = $userId");
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
