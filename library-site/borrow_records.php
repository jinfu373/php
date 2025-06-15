<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=library;charset=utf8mb4", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sql = "
    SELECT br.id, u.username, b.title, b.image, br.borrow_date, br.return_date, br.returned
    FROM borrow_records br
    JOIN users u ON br.user_id = u.id
    JOIN books b ON br.book_id = b.id
    ORDER BY br.borrow_date DESC
";
$stmt = $pdo->query($sql);
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

$loggedIn = isset($_SESSION['username']);
$role = $_SESSION['role'] ?? 'guest';
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8" />
  <title>借閱紀錄管理</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
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
      .menu-toggle {
        display: block;
      }

      nav ul {
        flex-direction: column;
        width: 100%;
        display: none;
        background: rgba(255, 255, 255, 0.3);
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
      max-width: 1100px;
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

    img {
      max-width: 60px;
      border-radius: 6px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    }

    a {
      color: #007bff;
      text-decoration: none;
      font-weight: bold;
    }

    a:hover {
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
      document.querySelector("nav ul").classList.toggle("active");
    }
  </script>
</head>
<body>
  <header>
    <a href="index.php" class="logo">📘 Library</a>
    <button class="menu-toggle" onclick="toggleMenu()">☰</button>
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

  <h1>📝 借閱紀錄管理</h1>
  <p style="padding: 0 30px;"><a href="dashboard_admin.php">⬅ 返回後台</a></p>

  <div class="glass">
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>封面</th>
          <th>書名</th>
          <th>使用者</th>
          <th>借出時間</th>
          <th>歸還時間</th>
          <th>狀態</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($records as $r): ?>
        <tr>
          <td data-label="ID"><?= $r['id'] ?></td>
          <td data-label="封面">
            <?php if (!empty($r['image']) && file_exists($r['image'])): ?>
              <img src="<?= htmlspecialchars($r['image']) ?>" alt="封面" />
            <?php else: ?>無圖<?php endif; ?>
          </td>
          <td data-label="書名"><?= htmlspecialchars($r['title']) ?></td>
          <td data-label="使用者"><?= htmlspecialchars($r['username']) ?></td>
          <td data-label="借出"><?= $r['borrow_date'] ?></td>
          <td data-label="歸還"><?= $r['return_date'] ?? '—' ?></td>
          <td data-label="狀態"><?= $r['returned'] ? '✅ 已歸還' : '⏳ 借出中' ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
