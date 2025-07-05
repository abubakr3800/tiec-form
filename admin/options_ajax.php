<?php
session_start();
require_once '../cache/db.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'غير مصرح']);
    exit();
}

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

try {
    // $pdo is already available from cache/db.php
    
    switch ($action) {
        case 'list':
            // جلب قائمة الخيارات مع أسماء الأسئلة
            $stmt = $pdo->query("
                SELECT qo.*, sq.question_text_ar as question_text 
                FROM question_options qo 
                LEFT JOIN service_questions sq ON qo.question_id = sq.id 
                ORDER BY qo.sort_order, qo.option_text_ar
            ");
            $options = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'options' => $options
            ]);
            break;
            
        case 'add':
            // إضافة خيار جديد
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'طريقة طلب غير صحيحة']);
                exit();
            }
            
            $required_fields = ['question_id', 'option_text_ar', 'option_text_en'];
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    echo json_encode(['success' => false, 'message' => 'يرجى ملء جميع الحقول المطلوبة']);
                    exit();
                }
            }
            
            // التحقق من وجود السؤال
            $stmt = $pdo->prepare("SELECT id FROM service_questions WHERE id = ?");
            $stmt->execute([(int)$_POST['question_id']]);
            if ($stmt->rowCount() === 0) {
                echo json_encode(['success' => false, 'message' => 'السؤال غير موجود']);
                exit();
            }
            
            $sql = "INSERT INTO question_options (question_id, option_text_ar, option_text_en, sort_order, is_active) 
                    VALUES (:question_id, :option_text_ar, :option_text_en, :sort_order, :is_active)";
            
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                'question_id' => (int)$_POST['question_id'],
                'option_text_ar' => trim($_POST['option_text_ar']),
                'option_text_en' => trim($_POST['option_text_en']),
                'sort_order' => !empty($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0,
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'تم إضافة الخيار بنجاح']);
            } else {
                echo json_encode(['success' => false, 'message' => 'خطأ في حفظ البيانات']);
            }
            break;
            
        case 'edit':
            // تعديل خيار
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'طريقة طلب غير صحيحة']);
                exit();
            }
            
            if (empty($_POST['id'])) {
                echo json_encode(['success' => false, 'message' => 'معرف الخيار مطلوب']);
                exit();
            }
            
            $required_fields = ['question_id', 'option_text_ar', 'option_text_en'];
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    echo json_encode(['success' => false, 'message' => 'يرجى ملء جميع الحقول المطلوبة']);
                    exit();
                }
            }
            
            // التحقق من وجود السؤال
            $stmt = $pdo->prepare("SELECT id FROM service_questions WHERE id = ?");
            $stmt->execute([(int)$_POST['question_id']]);
            if ($stmt->rowCount() === 0) {
                echo json_encode(['success' => false, 'message' => 'السؤال غير موجود']);
                exit();
            }
            
            $sql = "UPDATE question_options SET 
                    question_id = :question_id, 
                    option_text_ar = :option_text_ar, 
                    option_text_en = :option_text_en, 
                    sort_order = :sort_order, 
                    is_active = :is_active 
                    WHERE id = :id";
            
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                'id' => (int)$_POST['id'],
                'question_id' => (int)$_POST['question_id'],
                'option_text_ar' => trim($_POST['option_text_ar']),
                'option_text_en' => trim($_POST['option_text_en']),
                'sort_order' => !empty($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0,
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'تم تحديث الخيار بنجاح']);
            } else {
                echo json_encode(['success' => false, 'message' => 'خطأ في تحديث البيانات']);
            }
            break;
            
        case 'delete':
            // حذف خيار
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'طريقة طلب غير صحيحة']);
                exit();
            }
            
            if (empty($_POST['id'])) {
                echo json_encode(['success' => false, 'message' => 'معرف الخيار مطلوب']);
                exit();
            }
            
            $option_id = (int)$_POST['id'];
            
            $stmt = $pdo->prepare("DELETE FROM question_options WHERE id = ?");
            $result = $stmt->execute([$option_id]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'تم حذف الخيار بنجاح']);
            } else {
                echo json_encode(['success' => false, 'message' => 'خطأ في حذف الخيار']);
            }
            break;

        case 'get_option':
            // جلب بيانات خيار واحد
            if (empty($_GET['id'])) {
                echo json_encode(['success' => false, 'message' => 'معرف الخيار مطلوب']);
                exit();
            }
            
            $option_id = (int)$_GET['id'];
            $stmt = $pdo->prepare("SELECT * FROM question_options WHERE id = ?");
            $stmt->execute([$option_id]);
            $option = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($option) {
                echo json_encode(['success' => true, 'option' => $option]);
            } else {
                echo json_encode(['success' => false, 'message' => 'الخيار غير موجود']);
            }
            break;

        case 'update_option':
            // تحديث خيار
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'طريقة طلب غير صحيحة']);
                exit();
            }
            
            if (empty($_POST['option_id'])) {
                echo json_encode(['success' => false, 'message' => 'معرف الخيار مطلوب']);
                exit();
            }
            
            if (empty($_POST['option_text'])) {
                echo json_encode(['success' => false, 'message' => 'نص الخيار مطلوب']);
                exit();
            }
            
            $sql = "UPDATE question_options SET 
                    option_text = :option_text, 
                    sort_order = :sort_order, 
                    is_active = :is_active 
                    WHERE id = :id";
            
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                'id' => (int)$_POST['option_id'],
                'option_text' => trim($_POST['option_text']),
                'sort_order' => !empty($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0,
                'is_active' => (int)$_POST['is_active']
            ]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'تم تحديث الخيار بنجاح']);
            } else {
                echo json_encode(['success' => false, 'message' => 'خطأ في تحديث البيانات']);
            }
            break;

        case 'get_options':
            // جلب جميع الخيارات للجدول
            $stmt = $pdo->query("
                SELECT qo.id, qo.option_text_ar as option_text, qo.is_active, qo.created_at, sq.question_text_ar as question_text, s.name_ar as service_name
                FROM question_options qo
                LEFT JOIN service_questions sq ON qo.question_id = sq.id
                LEFT JOIN services s ON sq.service_id = s.id
                ORDER BY qo.created_at DESC
            ");
            $options = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['data' => $options]);
            break;

        case 'add_option':
            // إضافة خيار جديد
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'طريقة طلب غير صحيحة']);
                exit();
            }
            
            if (empty($_POST['question_id'])) {
                echo json_encode(['success' => false, 'message' => 'السؤال مطلوب']);
                exit();
            }
            
            if (empty($_POST['option_text'])) {
                echo json_encode(['success' => false, 'message' => 'نص الخيار مطلوب']);
                exit();
            }
            
            $sql = "INSERT INTO question_options (question_id, option_text, sort_order, is_active) 
                    VALUES (:question_id, :option_text, :sort_order, :is_active)";
            
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                'question_id' => (int)$_POST['question_id'],
                'option_text' => trim($_POST['option_text']),
                'sort_order' => !empty($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0,
                'is_active' => (int)$_POST['is_active']
            ]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'تم إضافة الخيار بنجاح']);
            } else {
                echo json_encode(['success' => false, 'message' => 'خطأ في حفظ البيانات']);
            }
            break;

        case 'delete_option':
            // حذف خيار
            if (empty($_GET['id'])) {
                echo json_encode(['success' => false, 'message' => 'معرف الخيار مطلوب']);
                exit();
            }
            
            $option_id = (int)$_GET['id'];
            
            $stmt = $pdo->prepare("DELETE FROM question_options WHERE id = ?");
            $result = $stmt->execute([$option_id]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'تم حذف الخيار بنجاح']);
            } else {
                echo json_encode(['success' => false, 'message' => 'خطأ في حذف الخيار']);
            }
            break;

        case 'get_services':
            // جلب الخدمات للقائمة المنسدلة
            $stmt = $pdo->query("SELECT id, name_ar as service_name FROM services WHERE is_active = 1 ORDER BY name_ar");
            $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode($services);
            break;

        case 'get_questions':
            // جلب الأسئلة حسب الخدمة
            if (empty($_GET['service_id'])) {
                echo json_encode([]);
                exit();
            }
            
            $service_id = (int)$_GET['service_id'];
            $stmt = $pdo->prepare("SELECT id, question_text_ar as question_text FROM service_questions WHERE service_id = ? AND is_active = 1 ORDER BY sort_order, question_text_ar");
            $stmt->execute([$service_id]);
            $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode($questions);
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