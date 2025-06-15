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

$message = '';
if (isset($_GET['record_id'])) {
    $recordId = (int)$_GET['record_id'];
    $stmt = $pdo->prepare("SELECT book_id FROM borrow_records WHERE id = ? AND user_id = ? AND returned = 0");
    $stmt->execute([$recordId, $userId]);
    $record = $stmt->fetch();

    if ($record) {
        $stmt = $pdo->prepare("UPDATE borrow_records SET returned = 1, return_date = NOW() WHERE id = ?");
        $stmt->execute([$recordId]);

        $stmt = $pdo->prepare("UPDATE books SET available = 1 WHERE id = ?");
        $stmt->execute([$record['book_id']]);

        $message = "✅ 成功還書";
    } else {
        $message = "⚠️ 還書紀錄無效";
    }
}

$sql = "
    SELECT br.id AS record_id, b.title, b.author, b.image, br.borrow_date
    FROM borrow_records br
    JOIN books b ON br.book_id = b.id
    WHERE br.user_id = ? AND br.returned = 0
    ORDER BY br.borrow_date DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$userId]);
$borrowedBooks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>還書</title>
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
        .msg {
            background: rgba(255,255,255,0.6);
            border-left: 5px solid #28a745;
            padding: 10px 20px;
            margin: 20px 30px;
            font-weight: bold;
            border-radius: 6px;
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
            background: linear-gradient(135deg, #96e6a1, #d4fc79, #84fab0, #8fd3f4);
            z-index: -1;
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: destination-out;
            mask-composite: exclude;
        }
        .book-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }
        .book-card {
            background: rgba(255,255,255,0.3);
            backdrop-filter: blur(15px);
            border-radius: 15px;
            padding: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.2s ease;
        }
        .book-card:hover {
            transform: translateY(-5px);
        }
        .book-card img {
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
        .book-card h3 {
            margin: 10px 0 5px;
            font-size: 18px;
        }
        .book-card p {
            margin: 0 0 10px;
            font-size: 14px;
        }
        .button {
            display: inline-block;
            padding: 8px 16px;
            background: #28a745;
            color: white;
            border-radius: 10px;
            text-decoration: none;
            transition: background 0.3s ease;
        }
        .button:hover {
            background: #218838;
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

<h1>📦 還書</h1>

<?php if ($message): ?>
    <div class="msg"><?= $message ?></div>
<?php endif; ?>

<div class="glass">
    <?php if (count($borrowedBooks) > 0): ?>
        <div class="book-grid">
            <?php foreach ($borrowedBooks as $book): ?>
                <div class="book-card">
                    <?php if (!empty($book['image']) && file_exists($book['image'])): ?>
                        <img src="<?= htmlspecialchars($book['image']) ?>" alt="封面">
                    <?php else: ?>
                        <div class="no-image">無圖</div>
                    <?php endif; ?>
                    <h3><?= htmlspecialchars($book['title']) ?></h3>
                    <p>作者：<?= htmlspecialchars($book['author']) ?></p>
                    <p>借出：<?= htmlspecialchars($book['borrow_date']) ?></p>
                    <a class="button" href="return.php?record_id=<?= $book['record_id'] ?>" onclick="return confirm('確定要還這本書嗎？')">還書</a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>✅ 目前沒有借出的書籍。</p>
    <?php endif; ?>
</div>
</body>
</html>
