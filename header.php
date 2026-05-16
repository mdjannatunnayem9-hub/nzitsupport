<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <img src="NZ Full Logo.png" alt="NZIT Support" height="35">
        </a>
        <div class="d-flex align-items-center gap-2">
            <?php if (isLoggedIn()): ?>
                <span class="text-light me-2">
                    <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['username']) ?>
                    <?php if (isAdmin()): ?>
                        <span class="badge bg-danger ms-1">ADMIN</span>
                    <?php endif; ?>
                </span>
                <?php if (isAdmin()): ?>
                    <a href="admin.php" class="btn btn-outline-warning btn-sm me-1"><i class="bi bi-gear"></i> Admin</a>
                <?php endif; ?>
                <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-box-arrow-in-right"></i> Login
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>
