<?php
session_start();
require_once __DIR__ . '/../cache/db.php'; 

$national_id = $_POST['national_id'] ?? '';
$email = $_POST['email'] ?? '';

// Check DB for existing national_id or email
$stmt = $pdo->prepare('SELECT id FROM participants WHERE national_id = ? OR email = ? LIMIT 1');
$stmt->execute([$national_id, $email]);
if ($stmt->fetch()) {
    die('<div class="alert alert-danger text-center">تم التسجيل مسبقاً بهذا الرقم القومي أو البريد الإلكتروني.<br>Already registered with this National ID or Email.</div>');
}

$user_token = bin2hex(random_bytes(16));
$cookie_token = md5($user_token . time());

// Check DB again for extra safety
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $stmt = $pdo->prepare('SELECT id FROM participants WHERE national_id = ? OR email = ? LIMIT 1');
    $stmt->execute([$national_id, $email]);
    if ($stmt->fetch()) {
        // Write cache for future
        if ($nidCache) file_put_contents($nidCache, '1');
        if ($emailCache) file_put_contents($emailCache, '1');
        die('<div class="alert alert-danger text-center">تم التسجيل مسبقاً بهذا الرقم القومي أو البريد الإلكتروني.<br>Already registered with this National ID or Email.</div>');
    }
    // Generate unique token
    // $user_token = bin2hex(random_bytes(16));
    // Insert user
    $stmt = $pdo->prepare('INSERT INTO participants (
        user_token, full_name_ar, full_name_en, national_id, governorate, gender, age, phone, whatsapp, participant_type, email, university, education_stage, faculty, work_employer, support_service, training_confirmation
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([
        // $user_token,
        $cookie_token,
        $_POST['full_name_ar'] ?? '',
        $_POST['full_name_en'] ?? '',
        $national_id,
        $_POST['governorate'] ?? '',
        $_POST['gender'] ?? '',
        $_POST['age'] ?? '',
        $_POST['phone'] ?? '',
        $_POST['whatsapp'] ?? '',
        $_POST['participant_type'] ?? '',
        $email,
        $_POST['university'] ?? '',
        $_POST['education_stage'] ?? '',
        $_POST['faculty'] ?? '',
        $_POST['work_employer'] ?? '',
        $_POST['support_service'] ?? '',
        $_POST['training_confirmation'] ?? ''
    ]);
    // Write cache
    if ($nidCache) file_put_contents($nidCache, $user_token);
    if ($emailCache) file_put_contents($emailCache, $user_token);
    // Redirect to success page
    header('Location: success.php?token=' . urlencode($cookie_token));
    setcookie('user_token', $cookie_token, time() + (86400 * 30), "/");
    exit;
} catch (Exception $e) {
    die('<div class="alert alert-danger text-center">حدث خطأ أثناء التسجيل: ' . htmlspecialchars($e->getMessage()) . '</div>');
} 