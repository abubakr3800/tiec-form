<?php
require_once __DIR__ . '/../cache/db.php';
$token = $_GET['token'] ?? '';
$msg = '';
if ($token) {
    $stmt = $pdo->prepare('DELETE FROM participants WHERE user_token = ?');
    $stmt->execute([$token]);
    if ($stmt->rowCount()) {
        $msg = 'تم الحذف بنجاح / Deleted successfully.';
    } else {
        $msg = 'لم يتم العثور على التسجيل / Registration not found.';
    }
}
header('Location: index.php?msg=' . urlencode($msg));
exit; 