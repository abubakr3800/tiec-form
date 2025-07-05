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
            // جلب قائمة الخدمات
            $stmt = $pdo->query("
                SELECT * FROM services 
                ORDER BY sort_order, name_ar
            ");
            $services = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'services' => $services
            ]);
            break;
            
        case 'add':
            // إضافة خدمة جديدة
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'طريقة طلب غير صحيحة']);
                exit();
            }
            
            $required_fields = ['name_ar', 'name_en'];
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    echo json_encode(['success' => false, 'message' => 'يرجى ملء جميع الحقول المطلوبة']);
                    exit();
                }
            }
            
            $sql = "INSERT INTO services (name_ar, name_en, description_ar, description_en, sort_order, is_active) 
                    VALUES (:name_ar, :name_en, :description_ar, :description_en, :sort_order, :is_active)";
            
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                'name_ar' => trim($_POST['name_ar']),
                'name_en' => trim($_POST['name_en']),
                'description_ar' => !empty($_POST['description_ar']) ? trim($_POST['description_ar']) : null,
                'description_en' => !empty($_POST['description_en']) ? trim($_POST['description_en']) : null,
                'sort_order' => !empty($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0,
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'تم إضافة الخدمة بنجاح']);
            } else {
                echo json_encode(['success' => false, 'message' => 'خطأ في حفظ البيانات']);
            }
            break;
            
        case 'edit':
            // تعديل خدمة
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'طريقة طلب غير صحيحة']);
                exit();
            }
            
            if (empty($_POST['id'])) {
                echo json_encode(['success' => false, 'message' => 'معرف الخدمة مطلوب']);
                exit();
            }
            
            $required_fields = ['name_ar', 'name_en'];
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    echo json_encode(['success' => false, 'message' => 'يرجى ملء جميع الحقول المطلوبة']);
                    exit();
                }
            }
            
            $sql = "UPDATE services SET 
                    name_ar = :name_ar, 
                    name_en = :name_en, 
                    description_ar = :description_ar, 
                    description_en = :description_en, 
                    sort_order = :sort_order, 
                    is_active = :is_active 
                    WHERE id = :id";
            
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                'id' => (int)$_POST['id'],
                'name_ar' => trim($_POST['name_ar']),
                'name_en' => trim($_POST['name_en']),
                'description_ar' => !empty($_POST['description_ar']) ? trim($_POST['description_ar']) : null,
                'description_en' => !empty($_POST['description_en']) ? trim($_POST['description_en']) : null,
                'sort_order' => !empty($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0,
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'تم تحديث الخدمة بنجاح']);
            } else {
                echo json_encode(['success' => false, 'message' => 'خطأ في تحديث البيانات']);
            }
            break;
            
        case 'delete':
            // حذف خدمة
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'طريقة طلب غير صحيحة']);
                exit();
            }
            
            if (empty($_POST['id'])) {
                echo json_encode(['success' => false, 'message' => 'معرف الخدمة مطلوب']);
                exit();
            }
            
            $service_id = (int)$_POST['id'];
            
            // التحقق من وجود أسئلة مرتبطة
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM service_questions WHERE service_id = ?");
            $stmt->execute([$service_id]);
            $questions_count = $stmt->fetchColumn();
            
            if ($questions_count > 0) {
                echo json_encode(['success' => false, 'message' => 'لا يمكن حذف الخدمة لوجود أسئلة مرتبطة بها']);
                exit();
            }
            
            $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
            $result = $stmt->execute([$service_id]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'تم حذف الخدمة بنجاح']);
            } else {
                echo json_encode(['success' => false, 'message' => 'خطأ في حذف الخدمة']);
            }
            break;

        case 'get_service':
            // جلب بيانات خدمة واحدة
            if (empty($_GET['id'])) {
                echo json_encode(['success' => false, 'message' => 'معرف الخدمة مطلوب']);
                exit();
            }
            
            $service_id = (int)$_GET['id'];
            $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
            $stmt->execute([$service_id]);
            $service = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($service) {
                echo json_encode(['success' => true, 'service' => $service]);
            } else {
                echo json_encode(['success' => false, 'message' => 'الخدمة غير موجودة']);
            }
            break;

        case 'update_service':
            // تحديث خدمة
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'طريقة طلب غير صحيحة']);
                exit();
            }
            
            if (empty($_POST['service_id'])) {
                echo json_encode(['success' => false, 'message' => 'معرف الخدمة مطلوب']);
                exit();
            }
            
            if (empty($_POST['service_name'])) {
                echo json_encode(['success' => false, 'message' => 'اسم الخدمة مطلوب']);
                exit();
            }
            
            $sql = "UPDATE services SET 
                    service_name = :service_name, 
                    description = :description, 
                    is_active = :is_active 
                    WHERE id = :id";
            
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                'id' => (int)$_POST['service_id'],
                'service_name' => trim($_POST['service_name']),
                'description' => !empty($_POST['description']) ? trim($_POST['description']) : null,
                'is_active' => (int)$_POST['is_active']
            ]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'تم تحديث الخدمة بنجاح']);
            } else {
                echo json_encode(['success' => false, 'message' => 'خطأ في تحديث البيانات']);
            }
            break;

        case 'get_services':
            // جلب جميع الخدمات للجدول
            $stmt = $pdo->query("
                SELECT s.id, s.name_ar AS service_name, s.description_ar AS description, s.is_active, COUNT(sq.id) as questions_count, s.created_at
                FROM services s
                LEFT JOIN service_questions sq ON s.id = sq.service_id
                GROUP BY s.id
                ORDER BY s.created_at DESC
            ");
            $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['data' => $services]);
            break;

        case 'add_service':
            // إضافة خدمة جديدة
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'طريقة طلب غير صحيحة']);
                exit();
            }
            
            if (empty($_POST['service_name'])) {
                echo json_encode(['success' => false, 'message' => 'اسم الخدمة مطلوب']);
                exit();
            }
            
            $sql = "INSERT INTO services (service_name, description, is_active) 
                    VALUES (:service_name, :description, :is_active)";
            
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                'service_name' => trim($_POST['service_name']),
                'description' => !empty($_POST['description']) ? trim($_POST['description']) : null,
                'is_active' => (int)$_POST['is_active']
            ]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'تم إضافة الخدمة بنجاح']);
            } else {
                echo json_encode(['success' => false, 'message' => 'خطأ في حفظ البيانات']);
            }
            break;

        case 'delete_service':
            // حذف خدمة
            if (empty($_GET['id'])) {
                echo json_encode(['success' => false, 'message' => 'معرف الخدمة مطلوب']);
                exit();
            }
            
            $service_id = (int)$_GET['id'];
            
            // التحقق من وجود أسئلة مرتبطة
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM service_questions WHERE service_id = ?");
            $stmt->execute([$service_id]);
            $questions_count = $stmt->fetchColumn();
            
            if ($questions_count > 0) {
                echo json_encode(['success' => false, 'message' => 'لا يمكن حذف الخدمة لوجود أسئلة مرتبطة بها']);
                exit();
            }
            
            $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
            $result = $stmt->execute([$service_id]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'تم حذف الخدمة بنجاح']);
            } else {
                echo json_encode(['success' => false, 'message' => 'خطأ في حذف الخدمة']);
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