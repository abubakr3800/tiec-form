<?php
session_start();

// حذف جميع متغيرات الجلسة
session_unset();

// تدمير الجلسة
session_destroy();

// حذف الكوكيز إذا وجدت
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// إعادة توجيه لصفحة تسجيل الدخول
header('Location: login.php');
exit();
?> 