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
    $required_fields = ['name', 'national_id', 'governorate', 'gender', 'age', 'phone', 'participant_type', 'service_id'];
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
    
    // بدء المعاملة
    $pdo->beginTransaction();
    
    try {
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
            'qr_code' => $qr_code
        ];
        
        // حفظ البيانات في قاعدة البيانات
        $sql = "INSERT INTO participants (
            name, national_id, governorate, gender, age, phone, whatsapp, 
            participant_type, email, university, education_stage, faculty, 
            work_employer, qr_code
        ) VALUES (
            :name, :national_id, :governorate, :gender, :age, :phone, :whatsapp,
            :participant_type, :email, :university, :education_stage, :faculty,
            :work_employer, :qr_code
        )";
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute($data);
        
        if (!$result) {
            throw new Exception('خطأ في حفظ بيانات المشارك');
        }
        
        $participant_id = $pdo->lastInsertId();
        
        // إنشاء التسجيل
        $registration_data = [
            'participant_id' => $participant_id,
            'service_id' => (int)$_POST['service_id'],
            'registration_date' => date('Y-m-d'),
            'status' => 'pending'
        ];
        
        $sql = "INSERT INTO registrations (participant_id, service_id, registration_date, status) 
                VALUES (:participant_id, :service_id, :registration_date, :status)";
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute($registration_data);
        
        if (!$result) {
            throw new Exception('خطأ في إنشاء التسجيل');
        }
        
        $registration_id = $pdo->lastInsertId();
        
        // معالجة الأسئلة الديناميكية
        $questions = [];
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'question_') === 0) {
                $question_id = (int)str_replace('question_', '', $key);
                $questions[$question_id] = $value;
            }
        }
        
        // حفظ إجابات الأسئلة
        if (!empty($questions)) {
            $sql = "INSERT INTO registration_answers (registration_id, question_id, answer_text) 
                    VALUES (:registration_id, :question_id, :answer_text)";
            $stmt = $pdo->prepare($sql);
            
            foreach ($questions as $question_id => $answer) {
                $stmt->execute([
                    'registration_id' => $registration_id,
                    'question_id' => $question_id,
                    'answer_text' => $answer
                ]);
            }
        }
        
        // معالجة الملفات المرفوعة
        if (!empty($_FILES)) {
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $sql = "UPDATE registration_answers SET answer_file = :file_path 
                    WHERE registration_id = :registration_id AND question_id = :question_id";
            $stmt = $pdo->prepare($sql);
            
            foreach ($_FILES as $key => $file) {
                if (strpos($key, 'question_') === 0 && $file['error'] === UPLOAD_ERR_OK) {
                    $question_id = (int)str_replace('question_', '', $key);
                    
                    // التحقق من نوع الملف
                    $allowed_types = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'txt'];
                    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    
                    if (!in_array($file_extension, $allowed_types)) {
                        throw new Exception('نوع الملف غير مسموح به');
                    }
                    
                    // التحقق من حجم الملف (10MB كحد أقصى)
                    if ($file['size'] > 10 * 1024 * 1024) {
                        throw new Exception('حجم الملف كبير جداً (الحد الأقصى 10MB)');
                    }
                    
                    // إنشاء اسم فريد للملف
                    $filename = uniqid() . '_' . $file['name'];
                    $filepath = $upload_dir . $filename;
                    
                    if (move_uploaded_file($file['tmp_name'], $filepath)) {
                        // تحديث قاعدة البيانات باسم الملف
                        $stmt->execute([
                            'file_path' => $filepath,
                            'registration_id' => $registration_id,
                            'question_id' => $question_id
                        ]);
                    }
                }
            }
        }
        
        // تأكيد المعاملة
        $pdo->commit();
        
        // حفظ الكوكي لمنع التسجيل المكرر
        setcookie('registered_user', $qr_code, time() + (86400 * 30), '/'); // 30 يوم
        
        echo json_encode([
            'success' => true, 
            'message' => 'تم التسجيل بنجاح',
            'qr_code' => $qr_code
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'خطأ في قاعدة البيانات: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'خطأ غير متوقع: ' . $e->getMessage()]);
}
?> 