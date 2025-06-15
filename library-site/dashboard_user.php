<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=library;charset=utf8mb4", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$_SESSION['username']]);
$userId = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM borrow_records WHERE user_id = ? AND returned = 0");
$stmt->execute([$userId]);
$currentlyBorrowed = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM borrow_records WHERE user_id = ? AND returned = 1");
$stmt->execute([$userId]);
$returnedCount = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>使用者主頁</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #cbe8f5, #fcd2e2, #ffe0b2, #e0c3fc);
            background-size: 400% 400%;
            animation: bgMove 15s ease infinite;
            color: #111;
        }

        @keyframes bgMove {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        header {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(12px);
            padding: 20px 30px;
            border-radius: 0 0 20px 20px;
            box-shadow: 0 0 25px rgba(255, 255, 255, 0.3), 0 0 20px rgba(0, 128, 255, 0.3);
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

        h1, h2 {
            margin: 30px;
        }

        .glass {
            background: rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 30px;
            margin: 20px auto;
            max-width: 900px;
            position: relative;
            box-shadow: 0 0 40px rgba(0,0,0,0.1);
        }

        .glass::before {
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

        .links a {
            display: inline-block;
            margin: 10px 10px 0 0;
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 10px;
            font-weight: bold;
            color: #111;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .links a:hover {
            background: rgba(255, 255, 255, 0.8);
        }

        @media (max-width: 768px) {
            nav ul {
                flex-direction: column;
                width: 100%;
            }
            .links a {
                display: block;
                width: 100%;
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>

<header>
    <a href="index.php" class="logo">📘 Library</a>
    <nav>
        <ul>
            <li><a href="index.php">首頁</a></li>
            <li><a href="books.php">圖書瀏覽</a></li>
            <li><a href="dashboard_user.php">使用者主頁</a></li>
            <li><a href="logout.php">登出</a></li>
        </ul>
    </nav>
</header>

<h1>📚 使用者後台</h1>
<p style="margin: 0 30px;">歡迎，<?= htmlspecialchars($_SESSION['username']) ?>！</p>

<div class="glass">
    <h2>📊 借閱統計</h2>
    <p>⏳ 借閱中書籍數：<?= $currentlyBorrowed ?></p>
    <p>✅ 已歸還書籍數：<?= $returnedCount ?></p>
</div>

<div class="glass links">
    <h2>📎 快速選單</h2>
    <a href="borrow.php">📖 借書</a>
    <a href="return.php">📦 還書</a>
    <a href="my_records.php">📝 我的紀錄</a>
</div>

</body>
</html>
