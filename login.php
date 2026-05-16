<?php
require_once 'config.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_type = $_POST['login_type'] ?? 'user';

    if ($login_type === 'master') {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if ($username && $password) {
            $stmt = $conn->prepare("SELECT * FROM users WHERE role = 'admin' AND username = ? LIMIT 1");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $admin = $result->fetch_assoc();

            if ($admin && password_verify($password, $admin['password'])) {
                $_SESSION['user_id'] = $admin['id'];
                $_SESSION['username'] = $admin['username'];
                $_SESSION['role'] = $admin['role'];
                $_SESSION['can_view'] = $admin['can_view'];
                $_SESSION['can_edit'] = $admin['can_edit'];
                $_SESSION['can_delete'] = $admin['can_delete'];
                $_SESSION['can_update'] = $admin['can_update'];
                loadPagePermissions($admin['id']);
                header('Location: index.php');
                exit;
            }
            $error = 'Invalid username or password.';
        } else {
            $error = 'Please enter username and password.';
        }
    } else {
        $key = trim($_POST['key'] ?? '');
        if ($key) {
            $stmt = $conn->prepare("SELECT * FROM users WHERE user_key = ? LIMIT 1");
            $stmt->bind_param("s", $key);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['can_view'] = $user['can_view'];
                $_SESSION['can_edit'] = $user['can_edit'];
                $_SESSION['can_delete'] = $user['can_delete'];
                $_SESSION['can_update'] = $user['can_update'];
                loadPagePermissions($user['id']);
                header('Location: index.php');
                exit;
            }
            $error = 'Invalid key.';
        } else {
            $error = 'Please enter your key.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - NZIT Support</title>
    <link rel="icon" href="NZ Icon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light d-flex flex-column" style="min-height:100vh;overflow-x:hidden;">
    <div class="container flex-grow-1 d-flex align-items-center justify-content-center">
        <div class="col-md-5">
            <div class="card shadow">
                    <div class="card-body p-4">
                        <h3 class="text-center mb-3">NZIT Support</h3>

                        <ul class="nav nav-tabs nav-justified mb-3" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="user-tab" data-bs-toggle="tab" data-bs-target="#user-login" type="button" role="tab">
                                    <i class="bi bi-person"></i> User Login
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="master-tab" data-bs-toggle="tab" data-bs-target="#master-login" type="button" role="tab">
                                    <i class="bi bi-shield-lock"></i> Master Login
                                </button>
                            </li>
                        </ul>

                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>

                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="user-login" role="tabpanel">
                                <form method="post">
                                    <input type="hidden" name="login_type" value="user">
                                    <div class="mb-3">
                                        <label class="form-label">Your Key</label>
                                        <input type="password" name="key" class="form-control" placeholder="Enter your key" required autofocus>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-box-arrow-in-right"></i> Login
                                    </button>
                                </form>

                            </div>

                            <div class="tab-pane fade" id="master-login" role="tabpanel">
                                <form method="post">
                                    <input type="hidden" name="login_type" value="master">
                                    <div class="mb-3">
                                        <label class="form-label">Username</label>
                                        <input type="text" name="username" class="form-control" placeholder="Admin username">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Password</label>
                                        <input type="password" name="password" class="form-control" placeholder="Admin password">
                                    </div>
                                    <button type="submit" class="btn btn-dark w-100">
                                        <i class="bi bi-shield-lock"></i> Master Login
                                    </button>
                                </form>

                            </div>
                        </div>

                        <div class="text-center mt-3">
                            <a href="index.php" class="text-decoration-none small">
                                <i class="bi bi-arrow-left"></i> Back to Home
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php require_once 'footer.php'; ?>
</body>
</html>
