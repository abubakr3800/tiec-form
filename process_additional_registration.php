<?php
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'طريقة طلب غير صحيحة']);
    exit();
}

// التحقق من تسجيل الدخول
if (!isset($_SESSION['participant_id'])) {
    echo json_encode(['success' => false, 'message' => 'يجب تسجيل الدخول أولاً']);
    exit();
}

try {
    $pdo = getDBConnection();
    $participant_id = $_SESSION['participant_id'];
    
    // التحقق من البيانات المطلوبة
    $required_fields = ['service_id'];
    $missing_fields = [];
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $missing_fields[] = $field;
        }
    }
    
    if (!empty($missing_fields)) {
        echo json_encode(['success' => false, 'message' => 'يرجى اختيار الخدمة']);
        exit();
    }
    
    // التحقق من وجود المشارك
    $stmt = $pdo->prepare("SELECT * FROM participants WHERE id = ?");
    $stmt->execute([$participant_id]);
    $participant = $stmt->fetch();
    
    if (!$participant) {
        echo json_encode(['success' => false, 'message' => 'المشارك غير موجود']);
        exit();
    }
    
    $service_id = (int)$_POST['service_id'];
    
    // التحقق من وجود الخدمة
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ? AND is_active = 1");
    $stmt->execute([$service_id]);
    $service = $stmt->fetch();
    
    if (!$service) {
        echo json_encode(['success' => false, 'message' => 'الخدمة غير موجودة أو غير متاحة']);
        exit();
    }
    
    // التحقق من عدم وجود تسجيل مسبق في نفس الخدمة
    $stmt = $pdo->prepare("SELECT id FROM registrations WHERE participant_id = ? AND service_id = ?");
    $stmt->execute([$participant_id, $service_id]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'أنت مسجل مسبقاً في هذه الخدمة']);
        exit();
    }
    
    // بدء المعاملة
    $pdo->beginTransaction();
    
    try {
        // إنشاء التسجيل
        $registration_data = [
            'participant_id' => $participant_id,
            'service_id' => $service_id,
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
        
        echo json_encode([
            'success' => true, 
            'message' => 'تم التسجيل في الخدمة بنجاح',
            'service_name' => $service['name_ar']
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