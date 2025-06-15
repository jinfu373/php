<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=library;charset=utf8mb4", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (isset($_GET['delete']) && $_GET['delete'] !== $_SESSION['username']) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE username = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: manage_users.php");
    exit;
}

if (isset($_GET['toggle']) && $_GET['toggle'] !== $_SESSION['username']) {
    $stmt = $pdo->prepare("UPDATE users SET is_active = NOT is_active WHERE username = ?");
    $stmt->execute([$_GET['toggle']]);
    header("Location: manage_users.php");
    exit;
}

$stmt = $pdo->query("SELECT id, username, role, is_active FROM users ORDER BY id DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$loggedIn = isset($_SESSION['username']);
$role = $_SESSION['role'] ?? 'guest';
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>使用者管理</title>
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
            border: 2px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.3), 0 0 25px rgba(0, 128, 255, 0.3);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            position: relative;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #111;
            text-decoration: none;
        }
        .menu-toggle {
            display: none;
            font-size: 24px;
            background: none;
            border: none;
            color: #111;
            cursor: pointer;
        }
        nav ul {
            display: flex;
            gap: 15px;
            list-style: none;
            margin: 0;
            padding: 0;
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
            .menu-toggle {
                display: block;
            }
            nav ul {
                flex-direction: column;
                width: 100%;
                display: none;
                background: rgba(255,255,255,0.3);
                backdrop-filter: blur(10px);
                border-radius: 12px;
                padding: 10px;
            }
            nav ul.active {
                display: flex;
            }
        }

        h1 {
            padding: 20px 30px 0;
        }
        .glass {
            background: rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 30px;
            margin: 20px auto;
            max-width: 1000px;
            box-shadow: 0 0 40px rgba(0,0,0,0.1);
            position: relative;
        }
        .glass::before {
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

        table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255,255,255,0.4);
            backdrop-filter: blur(12px);
            border-radius: 12px;
            overflow: hidden;
        }
        th, td {
            padding: 12px;
            border: 1px solid rgba(0,0,0,0.1);
            text-align: left;
        }
        th {
            background: rgba(0,0,0,0.6);
            color: white;
        }
        a.action-link {
            margin-right: 10px;
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }
        a.action-link:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            table, thead, tbody, th, td, tr {
                display: block;
            }
            thead {
                display: none;
            }
            tr {
                margin-bottom: 1em;
            }
            td {
                padding: 10px;
                border: none;
                border-bottom: 1px solid #ccc;
                position: relative;
            }
            td::before {
                content: attr(data-label);
                font-weight: bold;
                position: absolute;
                left: 10px;
                top: 10px;
                color: #333;
            }
        }
    </style>
    <script>
        function toggleMenu() {
            const menu = document.querySelector("nav ul");
            menu.classList.toggle("active");
        }
    </script>
</head>
<body>

<header>
    <a href="index.php" class="logo">🌈 Library</a>
    <button class="menu-toggle" onclick="toggleMenu()">☰</button>
    <nav>
        <ul>
            <li><a href="index.php">首頁</a></li>
            <li><a href="books.php">圖書瀏覽</a></li>
            <?php if (!$loggedIn): ?>
                <li><a href="login.php">登入</a></li>
                <li><a href="register.php">註冊</a></li>
            <?php elseif ($role === 'user'): ?>
                <li><a href="dashboard_user.php">使用者主頁</a></li>
                <li><a href="logout.php">登出</a></li>
            <?php elseif ($role === 'admin'): ?>
                <li><a href="dashboard_admin.php">管理員主頁</a></li>
                <li><a href="logout.php">登出</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<h1>👤 使用者管理</h1>

<div class="glass">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>帳號</th>
                <th>角色</th>
                <th>狀態</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td data-label="ID"><?= $user['id'] ?></td>
                    <td data-label="帳號"><?= htmlspecialchars($user['username']) ?></td>
                    <td data-label="角色"><?= $user['role'] ?></td>
                    <td data-label="狀態"><?= $user['is_active'] ? '✅ 啟用' : '❌ 停權' ?></td>
                    <td data-label="操作">
                        <?php if ($user['username'] !== $_SESSION['username']): ?>
                            <a class="action-link" href="?toggle=<?= urlencode($user['username']) ?>">
                                <?= $user['is_active'] ? '停權' : '啟用' ?>
                            </a>
                            <a class="action-link" href="?delete=<?= urlencode($user['username']) ?>" onclick="return confirm('確定要刪除此帳號？')">刪除</a>
                        <?php else: ?>
                            👤 本人
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>
