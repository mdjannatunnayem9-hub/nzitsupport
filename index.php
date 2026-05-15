<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NZIT Support</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light" style="overflow-x:hidden;">
    <?php require_once 'header.php'; ?>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card text-center shadow-lg border-0">
                    <div class="card-body py-5">
                        <i class="bi bi-layout-text-window-reverse text-primary" style="font-size:4rem;"></i>
                        <h3 class="card-title mt-3">NZ Support List</h3>
                        <p class="card-text text-muted">Manage and view all support entries</p>
                        <a href="nzsupportlist.php" class="btn btn-primary btn-lg px-5">
                            <i class="bi bi-arrow-right-circle"></i> NZ Support List
                        </a>
                        <?php if (!isLoggedIn()): ?>
                            <div class="mt-3">
                                <small class="text-muted">
                                    <a href="login.php" class="text-decoration-none">Login</a> to add or edit entries
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php require_once 'footer.php'; ?>
</body>
</html>
