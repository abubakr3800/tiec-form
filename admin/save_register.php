<?php
session_start();
require_once __DIR__ . '/../cache/db.php';

function showMsg($msg, $success = false) {
    echo '<div style="max-width:600px;margin:40px auto;">';
    echo '<div class="alert alert-' . ($success ? 'success' : 'danger') . ' text-center">' . $msg . '</div>';
    echo '<a href="register.php" class="btn btn-primary">عودة / Back</a>';
    echo '</div>';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    showMsg('طريقة الطلب غير صحيحة / Invalid request method.');
}

$role = $_POST['role'] ?? '';
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$full_name_ar = trim($_POST['full_name_ar'] ?? '');
$full_name_en = trim($_POST['full_name_en'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if (!$role || !$username || !$email || !$full_name_ar || !$full_name_en || !$phone || !$password || !$confirm_password) {
    showMsg('جميع الحقول مطلوبة / All fields are required.');
}
if ($password !== $confirm_password) {
    showMsg('كلمتا المرور غير متطابقتين / Passwords do not match.');
}

// Check for duplicate username or email
$table = ($role === 'trainer') ? 'trainers' : 'admins';
$stmt = $pdo->prepare("SELECT id FROM $table WHERE username = ? OR email = ? LIMIT 1");
$stmt->execute([$username, $email]);
if ($stmt->fetch()) {
    showMsg('اسم المستخدم أو البريد الإلكتروني مستخدم بالفعل / Username or email already in use.');
}

$password_hash = password_hash($password, PASSWORD_DEFAULT);

if ($role === 'admin') {
    $stmt = $pdo->prepare('INSERT INTO admins (username, password_hash, full_name_ar, full_name_en, email, phone, role) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$username, $password_hash, $full_name_ar, $full_name_en, $email, $phone, 'admin']);
    showMsg('تم تسجيل المسؤول بنجاح / Admin registered successfully.', true);
} elseif ($role === 'trainer') {
    $specialty = trim($_POST['specialty'] ?? '');
    $university = trim($_POST['university'] ?? '');
    $experience_years = intval($_POST['experience_years'] ?? 0);
    if (!$specialty || !$university) {
        showMsg('جميع الحقول مطلوبة للمدرب / All trainer fields are required.');
    }
    $stmt = $pdo->prepare('INSERT INTO trainers (username, password_hash, full_name_ar, full_name_en, email, phone, specialty, university, experience_years, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$username, $password_hash, $full_name_ar, $full_name_en, $email, $phone, $specialty, $university, $experience_years, 'trainer']);
    showMsg('تم تسجيل المدرب بنجاح / Trainer registered successfully.', true);
} else {
    showMsg('دور غير صالح / Invalid role.');
} 