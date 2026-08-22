<?php
// config.php - Kết nối CSDL bằng PDO

$host = 'localhost';
$dbname = 'lien_he_db';
$user = 'root';      // đổi lại theo cấu hình Wampserver/XAMPP của bạn
$pass = '';           // đổi lại theo cấu hình Wampserver/XAMPP của bạn

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die('Lỗi kết nối CSDL: ' . $e->getMessage());
}
