<?php
session_start();
$loggedIn = isset($_SESSION['username']);
$role = $_SESSION['role'] ?? 'guest';

$pdo = new PDO("mysql:host=localhost;dbname=library;charset=utf8mb4", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$search = $_GET['search'] ?? '';
$availableOnly = isset($_GET['available']) ? true : false;

$sql = "SELECT * FROM books WHERE 1";
$params = [];

if (!empty($search)) {
    $sql .= " AND title LIKE ?";
    $params[] = "%$search%";
}

if ($availableOnly) {
    $sql .= " AND available = 1";
}

$sql .= " ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>圖書瀏覽</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        <?php?>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #a0d8ef, #f7c4e1, #ffd8a9, #d2b4f4);
            background-size: 400% 400%;
            animation: bgShift 15s ease infinite;
            padding: 0;
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
        .logo { font-size: 28px; font-weight: bold; color: #111; text-decoration: none; }
        nav { display: flex; flex-wrap: wrap; gap: 15px; margin-top: 10px; }
        nav ul { display: flex; gap: 15px; list-style: none; padding: 0; margin: 0; }
        nav li a {
            color: #111; text-decoration: none; padding: 8px 14px;
            border-radius: 8px; transition: background 0.3s;
        }
        nav li a:hover { background: rgba(0, 0, 0, 0.1); }
        h1 { padding: 20px 30px 0; color: #111; }
        form {
            margin: 20px auto;
            background: rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 15px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
            max-width: 800px;
        }
        input[type="text"] {
            padding: 8px; border: 1px solid #ccc;
            border-radius: 6px; flex: 1;
        }
        button {
            padding: 8px 16px; background: #333; color: white;
            border: none; border-radius: 6px; cursor: pointer;
        }
        .glass {
            background: rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 30px;
            margin: 20px auto;
            position: relative;
            box-shadow: 0 0 30px rgba(0,0,0,0.1);
            max-width: 1000px;
        }
        .glass::before {
            content: ""; position: absolute; inset: 0; border-radius: 20px; padding: 2px;
            background: linear-gradient(135deg, #ffecd2, #fcb69f, #a1c4fd);
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
            padding: 12px; border: 1px solid rgba(0,0,0,0.1);
            text-align: left; color: #111;
        }
        th {
            background: rgba(0,0,0,0.6); color: white;
        }
        img {
            max-width: 60px;
            border-radius: 6px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
        }
        @media (max-width: 768px) {
            nav ul { flex-direction: column; width: 100%; }
            form { flex-direction: column; align-items: stretch; }
            table, thead, tbody, th, td, tr { display: block; }
            tr { margin-bottom: 1em; }
            td { padding: 10px 5px; }
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

<h1>📚 圖書瀏覽</h1>

<?php if (isset($_SESSION['message'])): ?>
    <p style="text-align:center; color:green; font-weight:bold;">
        <?= $_SESSION['message'] ?>
    </p>
    <?php unset($_SESSION['message']); ?>
<?php endif; ?>

<form method="get">
    <input type="text" name="search" placeholder="搜尋書名" value="<?= htmlspecialchars($search) ?>">
    <label>
        <input type="checkbox" name="available" <?= $availableOnly ? 'checked' : '' ?>> 只顯示可借書籍
    </label>
    <button type="submit">搜尋</button>
</form>

<div class="glass">
<?php if (count($books) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>封面</th>
                <th>書名</th>
                <th>作者</th>
                <th>狀態</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($books as $book): ?>
                <tr>
                    <td>
                        <?php if (!empty($book['image']) && file_exists($book['image'])): ?>
                            <img src="<?= htmlspecialchars($book['image']) ?>" alt="封面">
                        <?php else: ?>無圖<?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($book['title']) ?></td>
                    <td><?= htmlspecialchars($book['author']) ?></td>
                    <td>
                        <?= $book['available'] ? '✅ 可借閱' : '❌ 已借出' ?>
                        <?php if ($loggedIn && $role === 'user' && $book['available']): ?>
                            <form method="POST" action="borrow.php" style="margin-top:8px;">
                                <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                                <button type="submit">借書</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>查無書籍資料。</p>
<?php endif; ?>
</div>

</body>
</html>
