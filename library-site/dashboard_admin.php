<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$loggedIn = true;
$role = $_SESSION['role'] ?? '';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=library;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $userCount = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM books");
    $bookCount = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM books WHERE available = 0");
    $borrowedCount = $stmt->fetchColumn();

} catch (PDOException $e) {
    die("資料庫錯誤：" . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>管理員主頁</title>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+TC&display=swap" rel="stylesheet">
  <style>
    body {
      margin: 0;
      font-family: 'Noto Sans TC', sans-serif;
      background: linear-gradient(135deg, #a0d8ef, #f7c4e1, #ffd8a9, #d2b4f4);
      background-size: 400% 400%;
      animation: bgShift 15s ease infinite;
      color: #111;
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

    h1 {
      padding: 20px 30px 0;
      font-size: 28px;
    }

    .glass-section {
      position: relative;
      background: rgba(255, 255, 255, 0.3);
      backdrop-filter: blur(20px);
      border-radius: 20px;
      padding: 30px;
      margin: 20px 30px;
      box-shadow: 0 0 40px rgba(0, 0, 0, 0.1);
    }

    .glass-section::before {
      content: "";
      position: absolute;
      inset: 0;
      border-radius: 20px;
      padding: 2px;
      background: linear-gradient(135deg, #ff9a9e, #fad0c4, #fbc2eb, #a6c1ee);
      z-index: -1;
      -webkit-mask:
        linear-gradient(#fff 0 0) content-box,
        linear-gradient(#fff 0 0);
      -webkit-mask-composite: destination-out;
      mask-composite: exclude;
    }

    .stats div {
      margin: 10px 0;
      font-size: 18px;
    }

    .links a {
      display: inline-block;
      margin: 10px 15px 0 0;
      padding: 12px 20px;
      background: rgba(255,255,255,0.5);
      color: #111;
      border-radius: 12px;
      text-decoration: none;
      font-weight: bold;
      transition: background 0.3s ease;
    }

    .links a:hover {
      background: rgba(255,255,255,0.8);
    }

    .welcome {
      padding: 10px 30px;
      font-size: 18px;
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

<h1>📋 管理員主頁</h1>
<div class="welcome">歡迎，<?= htmlspecialchars($_SESSION['username']) ?>！</div>

<div class="glass-section stats">
  <h2>📊 系統統計</h2>
  <div>👥 使用者總數：<?= $userCount ?></div>
  <div>📚 圖書總數：<?= $bookCount ?></div>
  <div>📦 借出中書籍：<?= $borrowedCount ?></div>
</div>

<div class="glass-section links">
  <h2>⚙️ 管理快速入口</h2>
  <a href="manage_books.php">📘 圖書管理</a>
  <a href="manage_users.php">👤 使用者管理</a>
  <a href="borrow_records.php">📝 借閱紀錄</a>
</div>

</body>
</html>
