<?php
// /includes/db.php
$host = 'localhost';
$db   = 'library_system';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>

<!-- /includes/header.php -->
<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <title>图书馆系统</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <noscript><link rel="stylesheet" href="/assets/css/noscript.css" /></noscript>
</head>
<body class="is-preload">
    <div id="wrapper">

<!-- /includes/footer.php -->
    </div> <!-- #wrapper -->
    <script src="/assets/js/jquery.min.js"></script>
    <script src="/assets/js/browser.min.js"></script>
    <script src="/assets/js/breakpoints.min.js"></script>
    <script src="/assets/js/util.js"></script>
    <script src="/assets/js/main.js"></script>
</body>
</html>

<!-- /index.php -->
<?php include 'includes/header.php'; ?>
<section id="banner">
    <div class="inner">
        <h2>欢迎使用图书馆系统</h2>
        <p>请先<a href="login.php">登录</a>或<a href="register.php">注册</a>开始使用。</p>
    </div>
</section>
<?php include 'includes/footer.php'; ?>

<!-- /login.php -->
<?php
include 'includes/db.php';
session_start();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // 用户尝试
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = 'user';
        header('Location: dashboard_user.php');
        exit();
    }

    // 管理员尝试
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password_hash'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['role'] = 'admin';
        header('Location: dashboard_admin.php');
        exit();
    }

    $error = '登入失败，请确认账号密码';
}
?>
<?php include 'includes/header.php'; ?>
<section class="inner">
    <h2>登入</h2>
    <?php if ($error): ?><p style="color:red;"><?= $error ?></p><?php endif; ?>
    <form method="POST">
        <input type="text" name="username" placeholder="用户名或邮箱" required />
        <input type="password" name="password" placeholder="密码" required />
        <button type="submit">登入</button>
    </form>
</section>
<?php include 'includes/footer.php'; ?>

<!-- /register.php -->
<?php
include 'includes/db.php';
session_start();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $error = '此邮箱已注册';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$name, $email, $hash]);
        header('Location: login.php');
        exit();
    }
}
?>
<?php include 'includes/header.php'; ?>
<section class="inner">
    <h2>用户注册</h2>
    <?php if ($error): ?><p style="color:red;"><?= $error ?></p><?php endif; ?>
    <form method="POST">
        <input type="text" name="name" placeholder="姓名" required />
        <input type="email" name="email" placeholder="邮箱" required />
        <input type="password" name="password" placeholder="密码" required />
        <button type="submit">注册</button>
    </form>
</section>
<?php include 'includes/footer.php'; ?>
