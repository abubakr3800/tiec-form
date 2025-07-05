<?php
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'طريقة طلب غير صحيحة']);
    exit();
}

try {
    $pdo = getDBConnection();
    
    // التحقق من البيانات المطلوبة
    $required_fields = ['name', 'national_id', 'governorate', 'gender', 'age', 'phone', 'participant_type'];
    $missing_fields = [];
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $missing_fields[] = $field;
        }
    }
    
    if (!empty($missing_fields)) {
        echo json_encode(['success' => false, 'message' => 'يرجى ملء جميع الحقول المطلوبة']);
        exit();
    }
    
    // التحقق من عدم وجود تسجيل مسبق
    $stmt = $pdo->prepare("SELECT id FROM participants WHERE national_id = ? OR email = ?");
    $stmt->execute([$_POST['national_id'], $_POST['email']]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'تم التسجيل مسبقاً بهذا الرقم القومي أو البريد الإلكتروني']);
        exit();
    }
    
    // التحقق من صحة البريد الإلكتروني إذا تم إدخاله
    if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'البريد الإلكتروني غير صحيح']);
        exit();
    }
    
    // التحقق من العمر
    if ($_POST['age'] < 16 || $_POST['age'] > 100) {
        echo json_encode(['success' => false, 'message' => 'العمر يجب أن يكون بين 16 و 100 سنة']);
        exit();
    }
    
    // إنشاء رمز QR فريد
    $qr_code = uniqid('TIEC_', true);
    
    // إعداد البيانات للحفظ
    $data = [
        'name' => trim($_POST['name']),
        'national_id' => trim($_POST['national_id']),
        'governorate' => $_POST['governorate'],
        'gender' => $_POST['gender'],
        'age' => (int)$_POST['age'],
        'phone' => trim($_POST['phone']),
        'whatsapp' => !empty($_POST['whatsapp']) ? trim($_POST['whatsapp']) : null,
        'participant_type' => $_POST['participant_type'],
        'email' => !empty($_POST['email']) ? trim($_POST['email']) : null,
        'university' => !empty($_POST['university']) ? trim($_POST['university']) : null,
        'education_stage' => !empty($_POST['education_stage']) ? $_POST['education_stage'] : null,
        'faculty' => !empty($_POST['faculty']) ? trim($_POST['faculty']) : null,
        'work_employer' => !empty($_POST['work_employer']) ? trim($_POST['work_employer']) : null,
        'support_service' => !empty($_POST['support_service']) ? trim($_POST['support_service']) : null,
        'training_confirmation' => isset($_POST['training_confirmation']) ? 1 : 0,
        'qr_code' => $qr_code
    ];
    
    // حفظ البيانات في قاعدة البيانات
    $sql = "INSERT INTO participants (
        name, national_id, governorate, gender, age, phone, whatsapp, 
        participant_type, email, university, education_stage, faculty, 
        work_employer, support_service, training_confirmation, qr_code
    ) VALUES (
        :name, :national_id, :governorate, :gender, :age, :phone, :whatsapp,
        :participant_type, :email, :university, :education_stage, :faculty,
        :work_employer, :support_service, :training_confirmation, :qr_code
    )";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute($data);
    
    if ($result) {
        // حفظ الكوكي لمنع التسجيل المكرر
        setcookie('registered_user', $qr_code, time() + (86400 * 30), '/'); // 30 يوم
        
        echo json_encode([
            'success' => true, 
            'message' => 'تم التسجيل بنجاح',
            'qr_code' => $qr_code
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'خطأ في حفظ البيانات']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'خطأ في قاعدة البيانات: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'خطأ غير متوقع: ' . $e->getMessage()]);
}
?> 