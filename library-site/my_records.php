<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

$loggedIn = isset($_SESSION['username']);
$role = $_SESSION['role'] ?? '';

$pdo = new PDO("mysql:host=localhost;dbname=library;charset=utf8mb4", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$_SESSION['username']]);
$userId = $stmt->fetchColumn();

$sql = "
    SELECT br.id, b.title, b.author, b.image, br.borrow_date, br.return_date, br.returned
    FROM borrow_records br
    JOIN books b ON br.book_id = b.id
    WHERE br.user_id = ?
    ORDER BY br.borrow_date DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$userId]);
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>我的借閱紀錄</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        .glass {
            background: rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 30px;
            margin: 20px 30px;
            position: relative;
            box-shadow: 0 0 40px rgba(0,0,0,0.1);
        }
        .glass::before {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: 20px;
            padding: 2px;
            background: linear-gradient(135deg, #f9d423, #ff4e50, #8fd3f4);
            z-index: -1;
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: destination-out;
            mask-composite: exclude;
        }
        .record-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
        }
        .record-card {
            background: rgba(255,255,255,0.3);
            backdrop-filter: blur(15px);
            border-radius: 15px;
            padding: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.2s ease;
        }
        .record-card:hover {
            transform: translateY(-5px);
        }
        .record-card img {
            max-width: 100px;
            height: auto;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        .no-image {
            width: 100px;
            height: 140px;
            background: #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #555;
            font-size: 14px;
            border-radius: 8px;
            margin: 0 auto 10px;
        }
        .record-card h3 {
            margin: 10px 0 5px;
            font-size: 18px;
        }
        .record-card p {
            margin: 0 0 6px;
            font-size: 14px;
        }
        .status {
            font-weight: bold;
            padding-top: 6px;
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

<h1>📝 我的借閱紀錄</h1>

<div class="glass">
    <?php if (count($records) > 0): ?>
        <div class="record-grid">
            <?php foreach ($records as $r): ?>
                <div class="record-card">
                    <?php if (!empty($r['image']) && file_exists($r['image'])): ?>
                        <img src="<?= htmlspecialchars($r['image']) ?>" alt="封面">
                    <?php else: ?>
                        <div class="no-image">無圖</div>
                    <?php endif; ?>
                    <h3><?= htmlspecialchars($r['title']) ?></h3>
                    <p>作者：<?= htmlspecialchars($r['author']) ?></p>
                    <p>借出：<?= $r['borrow_date'] ?></p>
                    <p>還書：<?= $r['return_date'] ?? '—' ?></p>
                    <p class="status"><?= $r['returned'] ? '✅ 已還書' : '⏳ 借閱中' ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>目前沒有任何借閱紀錄。</p>
    <?php endif; ?>
</div>
</body>
</html>
