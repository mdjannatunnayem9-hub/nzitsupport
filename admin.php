<?php
require_once 'config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - NZIT Support</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">NZIT Support</a>
            <div class="d-flex align-items-center gap-2">
                <span class="text-light">
                    <i class="bi bi-shield-lock"></i> Admin: <?= htmlspecialchars($_SESSION['username']) ?>
                </span>
                <a href="index.php" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
                <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <style>
        .perm-card {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #e0e0e0;
            max-width: 500px;
            margin: 0 auto;
            transition: transform .3s ease, box-shadow .3s ease;
        }
        .perm-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(233, 69, 96, .15);
        }
        .perm-card .icon-circle {
            width: 70px;
            height: 70px;
            margin: 0 auto;
            background: #fef0f2;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background .3s ease;
        }
        .perm-card:hover .icon-circle {
            background: #fde2e6;
        }
        .perm-card .icon-circle i {
            font-size: 2.2rem;
            color: #e94560;
            transition: transform .3s ease;
        }
        .perm-card:hover .icon-circle i {
            transform: scale(1.1);
        }
        .perm-card .card-title {
            color: #333;
            font-weight: 600;
            font-size: 1.3rem;
        }
        .perm-card .card-text {
            color: #888 !important;
            font-size: .9rem;
        }

    </style>

    <div class="container py-4">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card perm-card text-center shadow-lg border-0 h-100">
                    <div class="card-body py-4 px-4">
                        <div class="icon-circle mb-2">
                            <i class="bi bi-shield-lock-fill"></i>
                        </div>
                        <h3 class="card-title mt-2">Permission Management</h3>
                        <p class="card-text mb-3">Manage users, permissions, and page access control</p>
                        <a href="permission.php" class="btn btn-primary">
                            <i class="bi bi-shield-check me-2"></i> Open Permission Panel
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card perm-card text-center shadow-lg border-0 h-100">
                    <div class="card-body py-4 px-4">
                        <div class="icon-circle mb-2">
                            <i class="bi bi-database"></i>
                        </div>
                        <h3 class="card-title mt-2">Data Management</h3>
                        <p class="card-text mb-3">Manage users, statuses, and device options</p>
                        <a href="manage.php?tab=users" class="btn btn-primary mb-2 px-4">
                            <i class="bi bi-people me-1"></i> User Name
                        </a>
                        <a href="manage.php?tab=statuses" class="btn btn-primary mb-2 px-4">
                            <i class="bi bi-tags me-1"></i> Status
                        </a>
                        <a href="manage.php?tab=devices" class="btn btn-primary px-4">
                            <i class="bi bi-pc-display me-1"></i> Device
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
