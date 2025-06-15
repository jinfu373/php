<?php
$host = 'localhost';
$dbname = 'library';
$username = 'root';
$password = '';

try {
    
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
} catch (PDOException $e) {
    die("無法建立資料庫: " . $e->getMessage());
}

try {
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('user', 'admin') DEFAULT 'user'
    )");

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $adminPass = password_hash("admin123", PASSWORD_DEFAULT);
        $userPass = password_hash("user123", PASSWORD_DEFAULT);

        $pdo->exec("INSERT INTO users (username, password, role) VALUES 
            ('admin', '$adminPass', 'admin'),
            ('user1', '$userPass', 'user')");
    }

} catch (PDOException $e) {
    die("資料庫連線失敗：" . $e->getMessage());
}
?>
