<?php
session_start();
require_once '../config/database.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'غير مصرح']);
    exit();
}

$pdo = getDBConnection();
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'get_trainings':
            // جلب جميع التدريبات
            $stmt = $pdo->query("
                SELECT t.*, s.name_ar as service_name, tr.name as trainer_name
                FROM trainings t
                JOIN services s ON t.service_id = s.id
                JOIN trainers tr ON t.trainer_id = tr.id
                ORDER BY t.created_at DESC
            ");
            $trainings = $stmt->fetchAll();
            
            echo json_encode(['data' => $trainings]);
            break;
            
        case 'get_statistics':
            // جلب الإحصائيات
            $total_trainings = $pdo->query("SELECT COUNT(*) FROM trainings")->fetchColumn();
            $active_trainings = $pdo->query("SELECT COUNT(*) FROM trainings WHERE is_active = 1")->fetchColumn();
            $total_participants = $pdo->query("SELECT COUNT(*) FROM registrations WHERE status = 'confirmed'")->fetchColumn();
            $upcoming_trainings = $pdo->query("SELECT COUNT(*) FROM trainings WHERE start_date >= CURDATE() AND is_active = 1")->fetchColumn();
            
            echo json_encode([
                'total_trainings' => $total_trainings,
                'active_trainings' => $active_trainings,
                'total_participants' => $total_participants,
                'upcoming_trainings' => $upcoming_trainings
            ]);
            break;
            
        case 'add_training':
            // إضافة تدريب جديد
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('طريقة طلب غير صحيحة');
            }
            
            $required_fields = ['service_id', 'trainer_id', 'title_ar', 'title_en', 'start_date', 'end_date', 'start_time', 'end_time', 'max_participants'];
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception('يرجى ملء جميع الحقول المطلوبة');
                }
            }
            
            // التحقق من صحة التواريخ
            if (strtotime($_POST['start_date']) > strtotime($_POST['end_date'])) {
                throw new Exception('تاريخ البداية يجب أن يكون قبل تاريخ النهاية');
            }
            
            if (strtotime($_POST['start_time']) >= strtotime($_POST['end_time'])) {
                throw new Exception('وقت البداية يجب أن يكون قبل وقت النهاية');
            }
            
            $sql = "INSERT INTO trainings (service_id, trainer_id, title_ar, title_en, description_ar, description_en, 
                    start_date, end_date, start_time, end_time, max_participants, is_active) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                (int)$_POST['service_id'],
                (int)$_POST['trainer_id'],
                trim($_POST['title_ar']),
                trim($_POST['title_en']),
                !empty($_POST['description_ar']) ? trim($_POST['description_ar']) : null,
                !empty($_POST['description_en']) ? trim($_POST['description_en']) : null,
                $_POST['start_date'],
                $_POST['end_date'],
                $_POST['start_time'],
                $_POST['end_time'],
                (int)$_POST['max_participants'],
                isset($_POST['is_active']) ? 1 : 0
            ]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'تم إضافة التدريب بنجاح']);
            } else {
                throw new Exception('خطأ في حفظ البيانات');
            }
            break;
            
        case 'edit_training':
            // تعديل تدريب
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('طريقة طلب غير صحيحة');
            }
            
            if (empty($_POST['id'])) {
                throw new Exception('معرف التدريب مطلوب');
            }
            
            $required_fields = ['service_id', 'trainer_id', 'title_ar', 'title_en', 'start_date', 'end_date', 'start_time', 'end_time', 'max_participants'];
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception('يرجى ملء جميع الحقول المطلوبة');
                }
            }
            
            // التحقق من صحة التواريخ
            if (strtotime($_POST['start_date']) > strtotime($_POST['end_date'])) {
                throw new Exception('تاريخ البداية يجب أن يكون قبل تاريخ النهاية');
            }
            
            if (strtotime($_POST['start_time']) >= strtotime($_POST['end_time'])) {
                throw new Exception('وقت البداية يجب أن يكون قبل وقت النهاية');
            }
            
            $sql = "UPDATE trainings SET 
                    service_id = ?, trainer_id = ?, title_ar = ?, title_en = ?, 
                    description_ar = ?, description_en = ?, start_date = ?, end_date = ?, 
                    start_time = ?, end_time = ?, max_participants = ?, is_active = ? 
                    WHERE id = ?";
            
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                (int)$_POST['service_id'],
                (int)$_POST['trainer_id'],
                trim($_POST['title_ar']),
                trim($_POST['title_en']),
                !empty($_POST['description_ar']) ? trim($_POST['description_ar']) : null,
                !empty($_POST['description_en']) ? trim($_POST['description_en']) : null,
                $_POST['start_date'],
                $_POST['end_date'],
                $_POST['start_time'],
                $_POST['end_time'],
                (int)$_POST['max_participants'],
                isset($_POST['is_active']) ? 1 : 0,
                (int)$_POST['id']
            ]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'تم تحديث التدريب بنجاح']);
            } else {
                throw new Exception('خطأ في تحديث البيانات');
            }
            break;
            
        case 'delete_training':
            // حذف تدريب
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('طريقة طلب غير صحيحة');
            }
            
            if (empty($_POST['id'])) {
                throw new Exception('معرف التدريب مطلوب');
            }
            
            $training_id = (int)$_POST['id'];
            
            // التحقق من وجود تسجيلات مرتبطة
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE training_id = ?");
            $stmt->execute([$training_id]);
            $registrations_count = $stmt->fetchColumn();
            
            if ($registrations_count > 0) {
                throw new Exception('لا يمكن حذف التدريب لوجود تسجيلات مرتبطة به');
            }
            
            // التحقق من وجود سجلات حضور
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE training_id = ?");
            $stmt->execute([$training_id]);
            $attendance_count = $stmt->fetchColumn();
            
            if ($attendance_count > 0) {
                throw new Exception('لا يمكن حذف التدريب لوجود سجلات حضور مرتبطة به');
            }
            
            $stmt = $pdo->prepare("DELETE FROM trainings WHERE id = ?");
            $result = $stmt->execute([$training_id]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'تم حذف التدريب بنجاح']);
            } else {
                throw new Exception('خطأ في حذف التدريب');
            }
            break;
            
        case 'get_training':
            // جلب بيانات تدريب واحد
            if (empty($_GET['id'])) {
                throw new Exception('معرف التدريب مطلوب');
            }
            
            $training_id = (int)$_GET['id'];
            $stmt = $pdo->prepare("SELECT * FROM trainings WHERE id = ?");
            $stmt->execute([$training_id]);
            $training = $stmt->fetch();
            
            if ($training) {
                echo json_encode(['success' => true, 'training' => $training]);
            } else {
                throw new Exception('التدريب غير موجود');
            }
            break;
            
        case 'get_training_participants':
            // جلب المشاركين في تدريب معين
            if (empty($_GET['id'])) {
                throw new Exception('معرف التدريب مطلوب');
            }
            
            $training_id = (int)$_GET['id'];
            $stmt = $pdo->prepare("
                SELECT p.*, r.registration_date, r.status, r.answers
                FROM registrations r
                JOIN participants p ON r.participant_id = p.id
                WHERE r.training_id = ?
                ORDER BY r.created_at DESC
            ");
            $stmt->execute([$training_id]);
            $participants = $stmt->fetchAll();
            
            echo json_encode(['success' => true, 'participants' => $participants]);
            break;
            
        default:
            throw new Exception('إجراء غير معروف');
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 