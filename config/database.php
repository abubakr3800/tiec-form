<?php
// إعدادات قاعدة البيانات
define('DB_HOST', 'localhost');
define('DB_NAME', 'tiec_form');
define('DB_USER', 'root');
define('DB_PASS', '');

// إنشاء اتصال قاعدة البيانات
function getDBConnection() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        die("خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage());
    }
}

// دالة للتحقق من وجود الجداول
function checkTables() {
    $pdo = getDBConnection();
    $tables = ['participants', 'admins', 'trainers', 'services', 'service_options', 'service_questions', 'question_options'];
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() == 0) {
            return false;
        }
    }
    return true;
}
?> 