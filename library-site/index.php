<?php
session_start();
$loggedIn = isset($_SESSION['username']);
$role = $_SESSION['role'] ?? 'guest';
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Library 圖書館首頁</title>
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

    main {
      padding: 60px 30px;
      text-align: center;
      color: #111;
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

<main>
  <h1>歡迎來到 Library 圖書館系統</h1>
  <p>這是一個簡單的線上圖書管理平台。</p>
</main>

</body>
</html>
