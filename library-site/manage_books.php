<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$loggedIn = isset($_SESSION['username']);
$role = $_SESSION['role'] ?? 'guest';

$pdo = new PDO("mysql:host=localhost;dbname=library;charset=utf8mb4", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (!is_dir("uploads")) {
    mkdir("uploads", 0777, true);
}

if (isset($_POST['add'])) {
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);

    $imagePath = null;
    if (!empty($_FILES['image']['name'])) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $imagePath = "uploads/" . uniqid() . "." . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], $imagePath);
    }

    $stmt = $pdo->prepare("INSERT INTO books (title, author, image) VALUES (?, ?, ?)");
    $stmt->execute([$title, $author, $imagePath]);
}

if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $available = isset($_POST['available']) ? 1 : 0;

    $imagePath = $_POST['existing_image'] ?? null;
    if (!empty($_FILES['image']['name'])) {
        if (!empty($imagePath) && file_exists($imagePath)) {
            unlink($imagePath);
        }
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $imagePath = "uploads/" . uniqid() . "." . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], $imagePath);
    }

    $stmt = $pdo->prepare("UPDATE books SET title=?, author=?, available=?, image=? WHERE id=?");
    $stmt->execute([$title, $author, $available, $imagePath, $id]);
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("SELECT image FROM books WHERE id = ?");
    $stmt->execute([$id]);
    $book = $stmt->fetch();
    if ($book && !empty($book['image']) && file_exists($book['image'])) {
        unlink($book['image']);
    }
    $stmt = $pdo->prepare("DELETE FROM books WHERE id = ?");
    $stmt->execute([$id]);
}

$stmt = $pdo->query("SELECT * FROM books ORDER BY id DESC");
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>圖書管理</title>
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
            0% {background-position: 0% 50%;}
            50% {background-position: 100% 50%;}
            100% {background-position: 0% 50%;}
        }
        header {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(12px);
            padding: 20px 30px;
            border-radius: 0 0 20px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            border: 2px solid rgba(255,255,255,0.5);
            box-shadow: 0 0 20px rgba(255,255,255,0.3), 0 0 25px rgba(0,128,255,0.3);
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
            nav ul { flex-direction: column; width: 100%; }
            .menu-toggle { display: block; }
            nav { display: none; width: 100%; }
            nav.active { display: block; }
        }

        .container {
            max-width: 1200px;
            margin: auto;
            padding: 20px;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(18px);
            border-radius: 20px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 0 30px rgba(0,0,0,0.1);
            border: 1px solid rgba(255,255,255,0.4);
            position: relative;
        }
        .glass-card::before {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: 20px;
            padding: 2px;
            background: linear-gradient(135deg, #ffecd2, #fcb69f, #a1c4fd);
            z-index: -1;
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: destination-out;
            mask-composite: exclude;
        }
        .glass-card h2 {
            margin-top: 0;
            color: #222;
        }
        input[type="text"], input[type="file"] {
            width: 100%;
            margin: 8px 0;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 10px;
            background: rgba(255,255,255,0.5);
        }
        input[type="checkbox"] {
            margin-top: 10px;
        }
        button {
            margin-top: 10px;
            padding: 10px 20px;
            border: none;
            background: linear-gradient(to right, #667eea, #764ba2);
            color: white;
            border-radius: 10px;
            cursor: pointer;
        }
        button:hover {
            background: linear-gradient(to right, #5a67d8, #6b46c1);
        }
        .book-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }
        .book-card {
            background: rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(14px);
            border-radius: 20px;
            padding: 20px;
            position: relative;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .book-card img {
            max-width: 100px;
            display: block;
            margin-bottom: 10px;
            border-radius: 10px;
            border: 1px solid #ccc;
            transition: transform 0.3s ease;
        }
        .book-card img:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
        .book-card label {
            font-weight: bold;
            display: block;
            margin-top: 10px;
        }
        .book-card form {
            display: flex;
            flex-direction: column;
        }
        a {
            color: red;
            text-decoration: none;
            margin-top: 10px;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<header>
    <a href="index.php" class="logo">🌈 Library</a>
    <button class="menu-toggle" onclick="document.querySelector('nav').classList.toggle('active')">☰</button>
    <nav>
        <ul>
            <li><a href="index.php">首頁</a></li>
            <li><a href="books.php">圖書瀏覽</a></li>
            <li><a href="dashboard_admin.php">管理員主頁</a></li>
            <li><a href="logout.php">登出</a></li>
        </ul>
    </nav>
</header>

<div class="container">
    <h1>📘 圖書管理</h1>

    <div class="glass-card">
        <h2>➕ 新增書籍</h2>
        <form method="post" enctype="multipart/form-data">
            <input type="text" name="title" placeholder="書名" required>
            <input type="text" name="author" placeholder="作者" required>
            <input type="file" name="image" accept="image/*">
            <button type="submit" name="add">新增</button>
        </form>
    </div>

    <div class="glass-card">
        <h2>📚 書籍清單</h2>
        <div class="book-grid">
            <?php foreach ($books as $book): ?>
                <div class="book-card">
                    <form method="post" enctype="multipart/form-data">
                        <?php if ($book['image'] && file_exists($book['image'])): ?>
                            <img src="<?= htmlspecialchars($book['image']) ?>" alt="封面">
                        <?php else: ?>
                            <img src="assets/placeholder.png" alt="無封面">
                        <?php endif; ?>
                        <label>書名</label>
                        <input type="text" name="title" value="<?= htmlspecialchars($book['title']) ?>">

                        <label>作者</label>
                        <input type="text" name="author" value="<?= htmlspecialchars($book['author']) ?>">

                        <label>可借閱</label>
                        <input type="checkbox" name="available" <?= $book['available'] ? 'checked' : '' ?>>

                        <label>更新封面</label>
                        <input type="file" name="image" accept="image/*">

                        <input type="hidden" name="existing_image" value="<?= htmlspecialchars($book['image']) ?>">
                        <input type="hidden" name="id" value="<?= $book['id'] ?>">

                        <button type="submit" name="update">更新</button>
                        <a href="?delete=<?= $book['id'] ?>" onclick="return confirm('確定要刪除？')">🗑️ 刪除</a>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
</body>
</html>