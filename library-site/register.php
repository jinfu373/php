<?php
session_start();

if (isset($_SESSION['username'])) {
    header("Location: dashboard_user.php");
    exit;
}

$host = 'localhost';
$dbname = 'library';
$username = 'root';
$password = '';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputUsername = trim($_POST['username'] ?? '');
    $inputPassword = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($inputUsername) || empty($inputPassword)) {
        $errors[] = '請輸入帳號與密碼';
    } elseif ($inputPassword !== $confirmPassword) {
        $errors[] = '密碼不一致';
    } else {
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$inputUsername]);
            if ($stmt->fetch()) {
                $errors[] = '帳號已存在';
            } else {
                $hashedPassword = password_hash($inputPassword, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
                $stmt->execute([$inputUsername, $hashedPassword]);

                $_SESSION['username'] = $inputUsername;
                $_SESSION['role'] = 'user';
                header("Location: dashboard_user.php");
                exit;
            }
        } catch (PDOException $e) {
            $errors[] = "資料庫錯誤：" . $e->getMessage();
        }
    }
}
$loggedIn = isset($_SESSION['username']);
$role = $_SESSION['role'] ?? 'guest';
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>註冊帳號 - 圖書館系統</title>
  <style>
    body {
      margin: 0;
      font-family: sans-serif;
      background: linear-gradient(135deg, #a0d8ef, #f7c4e1, #ffd8a9, #d2b4f4);
      background-size: 400% 400%;
      animation: bgShift 15s ease infinite;
    }
    @keyframes bgShift {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }
    header {
      background: rgba(255, 255, 255, 0.2);
      backdrop-filter: blur(12px);
      padding: 20px 30px;
      border-radius: 0 0 20px 20px;
      border: 2px solid rgba(255, 255, 255, 0.5);
      box-shadow: 0 0 20px rgba(255, 255, 255, 0.3), 0 0 25px rgba(0, 128, 255, 0.3);
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
    }
    .logo {
      font-size: 28px;
      font-weight: bold;
      color: #111;
      text-decoration: none;
    }
    .menu-toggle {
      font-size: 26px;
      background: none;
      border: none;
      color: #111;
      cursor: pointer;
      display: none;
    }
    nav {
      display: flex;
      flex-wrap: wrap;
      gap: 15px;
      margin-top: 10px;
    }
    nav ul {
      display: flex;
      gap: 15px;
      list-style: none;
      padding: 0;
      margin: 0;
    }
    nav li a {
      color: #111;
      text-decoration: none;
      padding: 8px 14px;
      border-radius: 8px;
      transition: background 0.3s;
    }
    nav li a:hover {
      background: rgba(0, 0, 0, 0.1);
    }
    @media (max-width: 768px) {
      nav ul {
        flex-direction: column;
        width: 100%;
      }
      .menu-toggle {
        display: block;
      }
      nav {
        display: none;
        width: 100%;
      }
      nav.active {
        display: block;
      }
    }
    .glass-card {
      margin: 40px auto;
      max-width: 400px;
      background: rgba(255, 255, 255, 0.3);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border-radius: 20px;
      padding: 40px;
      box-shadow: 0 0 40px rgba(0, 0, 0, 0.1);
      position: relative;
      color: #111;
    }
    .glass-card::before {
      content: "";
      position: absolute;
      inset: 0;
      border-radius: 20px;
      padding: 2px;
      background: linear-gradient(135deg, #ff9a9e, #fad0c4, #fbc2eb, #a6c1ee);
      z-index: -1;
      -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
      -webkit-mask-composite: destination-out;
      mask-composite: exclude;
    }
    h2 {
      text-align: center;
      margin-bottom: 20px;
    }
    form {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }
    label {
      font-weight: bold;
    }
    input {
      padding: 10px;
      border-radius: 8px;
      border: 1px solid #ccc;
      font-size: 1em;
      background: rgba(255, 255, 255, 0.4);
      box-shadow: inset 0 2px 5px rgba(0,0,0,0.1);
      color: #111;
    }
    input:focus {
      outline: none;
      background: rgba(255, 255, 255, 0.7);
      border-color: #999;
    }
    button {
      padding: 10px;
      border-radius: 10px;
      border: none;
      font-weight: bold;
      background: rgba(255, 255, 255, 0.5);
      color: #111;
      cursor: pointer;
    }
    button:hover {
      background: rgba(255, 255, 255, 0.8);
    }
    .error {
      color: red;
      margin-bottom: 10px;
    }
    @media (max-width: 600px) {
      .glass-card {
        padding: 30px 20px;
      }
    }
  </style>
</head>
<body>
<header>
  <a href="index.php" class="logo">🌈 Library</a>
  <button class="menu-toggle" onclick="document.querySelector('nav').classList.toggle('active')">☰</button>
  <nav>
    <ul>
      <?php if (!$loggedIn): ?>
        <li><a href="index.php">首頁</a></li>
        <li><a href="books.php">圖書瀏覽</a></li>
        <li><a href="login.php">登入</a></li>
        <li><a href="register.php">註冊</a></li>
      <?php elseif ($role === 'user'): ?>
        <li><a href="index.php">首頁</a></li>
        <li><a href="books.php">圖書瀏覽</a></li>
        <li><a href="dashboard_user.php">使用者主頁</a></li>
        <li><a href="logout.php">登出</a></li>
      <?php elseif ($role === 'admin'): ?>
        <li><a href="index.php">首頁</a></li>
        <li><a href="books.php">圖書瀏覽</a></li>
        <li><a href="dashboard_admin.php">管理員主頁</a></li>
        <li><a href="logout.php">登出</a></li>
      <?php endif; ?>
    </ul>
  </nav>
</header>

<div class="glass-card">
  <h2>使用者註冊</h2>
  <?php if (!empty($errors)): ?>
    <div class="error">
      <ul>
        <?php foreach ($errors as $e): ?>
          <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="post">
    <label for="username">帳號：</label>
    <input type="text" name="username" id="username" required>

    <label for="password">密碼：</label>
    <input type="password" name="password" id="password" required>

    <label for="confirm_password">確認密碼：</label>
    <input type="password" name="confirm_password" id="confirm_password" required>

    <button type="submit">註冊</button>
  </form>
  <p style="text-align:center; margin-top:15px;">已有帳號？<a href="login.php">登入這裡</a></p>
</div>
</body>
</html>