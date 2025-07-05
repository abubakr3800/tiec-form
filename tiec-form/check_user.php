<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../cache/db.php'; 

$exists = false;
if (!empty($_POST['national_id']) || !empty($_POST['email'])) {
    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
        $stmt = $pdo->prepare('SELECT id FROM participants WHERE national_id = ? OR email = ? LIMIT 1');
        $stmt->execute([
            $_POST['national_id'] ?? '',
            $_POST['email'] ?? ''
        ]);
        if ($stmt->fetch()) {
            $exists = true;
        }
    } catch (Exception $e) {
        // Optionally log error
    }
}
echo json_encode(['exists' => $exists]); 