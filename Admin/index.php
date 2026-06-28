<?php
require_once dirname(__DIR__) . '/config.php';
require_once ROOT_DIR . '/includes/auth.php';
require_once ROOT_DIR . '/includes/functions.php';

$settings = readSettings();

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request.';
    } else {
        $user = $_POST['username'] ?? '';
        $pass = $_POST['password'] ?? '';
        $res = attemptLogin($user, $pass);
        if ($res === true) {
            header('Location: dashboard.php');
            exit;
        } else {
            $error = $res;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | SpiderMod Admin</title>
    <link rel="stylesheet" href="assets/admin.css?v=<?= time() ?>">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-box">
            <div style="display: flex; flex-direction: column; align-items: center; gap: 10px; margin-bottom: 20px;">
                <img src="<?= sanitize((strpos($settings['logo_url'] ?? '', 'http') === 0) ? $settings['logo_url'] : '../' . ltrim($settings['logo_url'] ?? 'public/assets/img/555.png', '/')) ?>" alt="SpiderMod Logo" style="width: 60px; height: 60px; object-fit: contain; filter: drop-shadow(0 0 10px #81ecff);">
                <h1 style="margin: 0;"><?= htmlspecialchars($settings['brand_name'] ?? 'SpiderMod') ?></h1>
            </div>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>
            <form method="POST">
                <?= csrfField() ?>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" required autofocus autocomplete="off">
                </div>
                <div class="form-group" style="margin-bottom: 30px;">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Log In to Dashboard</button>
            </form>
        </div>
    </div>
</body>
</html>
