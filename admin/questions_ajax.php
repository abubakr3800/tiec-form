<?php
session_start();
require_once '../config/database.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'غير مصرح']);
    exit();
}

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

try {
    $pdo = getDBConnection();
    
    switch ($action) {
        case 'list':
            // جلب قائمة الأسئلة مع أسماء الخدمات
            $stmt = $pdo->query("
                SELECT sq.*, s.name_ar as service_name 
                FROM service_questions sq 
                LEFT JOIN services s ON sq.service_id = s.id 
                ORDER BY sq.sort_order, sq.question_text_ar
            ");
            $questions = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'questions' => $questions
            ]);
            break;
            
        case 'add':
            // إضافة سؤال جديد
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'طريقة طلب غير صحيحة']);
                exit();
            }
            
            $required_fields = ['service_id', 'question_text_ar', 'question_text_en', 'question_type'];
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    echo json_encode(['success' => false, 'message' => 'يرجى ملء جميع الحقول المطلوبة']);
                    exit();
                }
            }
            
            // التحقق من صحة نوع السؤال
            $valid_types = ['text', 'textarea', 'select', 'radio'];
            if (!in_array($_POST['question_type'], $valid_types)) {
                echo json_encode(['success' => false, 'message' => 'نوع السؤال غير صحيح']);
                exit();
            }
            
            $sql = "INSERT INTO service_questions (service_id, question_text_ar, question_text_en, question_type, is_required, sort_order, is_active) 
                    VALUES (:service_id, :question_text_ar, :question_text_en, :question_type, :is_required, :sort_order, :is_active)";
            
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                'service_id' => (int)$_POST['service_id'],
                'question_text_ar' => trim($_POST['question_text_ar']),
                'question_text_en' => trim($_POST['question_text_en']),
                'question_type' => $_POST['question_type'],
                'is_required' => isset($_POST['is_required']) ? 1 : 0,
                'sort_order' => !empty($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0,
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'تم إضافة السؤال بنجاح']);
            } else {
                echo json_encode(['success' => false, 'message' => 'خطأ في حفظ البيانات']);
            }
            break;
            
        case 'edit':
            // تعديل سؤال
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'طريقة طلب غير صحيحة']);
                exit();
            }
            
            if (empty($_POST['id'])) {
                echo json_encode(['success' => false, 'message' => 'معرف السؤال مطلوب']);
                exit();
            }
            
            $required_fields = ['service_id', 'question_text_ar', 'question_text_en', 'question_type'];
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    echo json_encode(['success' => false, 'message' => 'يرجى ملء جميع الحقول المطلوبة']);
                    exit();
                }
            }
            
            // التحقق من صحة نوع السؤال
            $valid_types = ['text', 'textarea', 'select', 'radio'];
            if (!in_array($_POST['question_type'], $valid_types)) {
                echo json_encode(['success' => false, 'message' => 'نوع السؤال غير صحيح']);
                exit();
            }
            
            $sql = "UPDATE service_questions SET 
                    service_id = :service_id, 
                    question_text_ar = :question_text_ar, 
                    question_text_en = :question_text_en, 
                    question_type = :question_type, 
                    is_required = :is_required, 
                    sort_order = :sort_order, 
                    is_active = :is_active 
                    WHERE id = :id";
            
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                'id' => (int)$_POST['id'],
                'service_id' => (int)$_POST['service_id'],
                'question_text_ar' => trim($_POST['question_text_ar']),
                'question_text_en' => trim($_POST['question_text_en']),
                'question_type' => $_POST['question_type'],
                'is_required' => isset($_POST['is_required']) ? 1 : 0,
                'sort_order' => !empty($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0,
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'تم تحديث السؤال بنجاح']);
            } else {
                echo json_encode(['success' => false, 'message' => 'خطأ في تحديث البيانات']);
            }
            break;
            
        case 'delete':
            // حذف سؤال
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'طريقة طلب غير صحيحة']);
                exit();
            }
            
            if (empty($_POST['id'])) {
                echo json_encode(['success' => false, 'message' => 'معرف السؤال مطلوب']);
                exit();
            }
            
            $question_id = (int)$_POST['id'];
            
            // التحقق من وجود خيارات مرتبطة
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM question_options WHERE question_id = ?");
            $stmt->execute([$question_id]);
            $options_count = $stmt->fetchColumn();
            
            if ($options_count > 0) {
                echo json_encode(['success' => false, 'message' => 'لا يمكن حذف السؤال لوجود خيارات مرتبطة به']);
                exit();
            }
            
            $stmt = $pdo->prepare("DELETE FROM service_questions WHERE id = ?");
            $result = $stmt->execute([$question_id]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'تم حذف السؤال بنجاح']);
            } else {
                echo json_encode(['success' => false, 'message' => 'خطأ في حذف السؤال']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'إجراء غير معروف']);
            break;
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'خطأ في قاعدة البيانات: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'خطأ غير متوقع: ' . $e->getMessage()]);
}
?> 