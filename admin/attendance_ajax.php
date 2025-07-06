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
        case 'get_attendance':
            // جلب سجل الحضور
            $date = $_GET['date'] ?? date('Y-m-d');
            $training_id = $_GET['training_id'] ?? '';
            
            $sql = "
                SELECT a.*, p.name as participant_name, p.national_id, p.phone,
                       t.title_ar as training_title, s.name_ar as service_name
                FROM attendance a
                JOIN participants p ON a.participant_id = p.id
                JOIN trainings t ON a.training_id = t.id
                JOIN services s ON t.service_id = s.id
                WHERE a.attendance_date = ?
            ";
            $params = [$date];
            
            if ($training_id) {
                $sql .= " AND a.training_id = ?";
                $params[] = $training_id;
            }
            
            $sql .= " ORDER BY a.check_in_time DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $attendance = $stmt->fetchAll();
            
            echo json_encode(['data' => $attendance]);
            break;
            
        case 'get_statistics':
            // جلب الإحصائيات
            $date = $_GET['date'] ?? date('Y-m-d');
            $training_id = $_GET['training_id'] ?? '';
            
            $sql = "SELECT COUNT(*) as total, 
                           SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_count,
                           SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_count,
                           SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_count
                    FROM attendance 
                    WHERE attendance_date = ?";
            $params = [$date];
            
            if ($training_id) {
                $sql .= " AND training_id = ?";
                $params[] = $training_id;
            }
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $stats = $stmt->fetch();
            
            echo json_encode([
                'total_attendance' => $stats['total'] ?? 0,
                'present_count' => $stats['present_count'] ?? 0,
                'late_count' => $stats['late_count'] ?? 0,
                'absent_count' => $stats['absent_count'] ?? 0
            ]);
            break;
            
        case 'add_attendance':
            // إضافة سجل حضور
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('طريقة طلب غير صحيحة');
            }
            
            $required_fields = ['participant_id', 'training_id', 'attendance_date'];
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception('يرجى ملء جميع الحقول المطلوبة');
                }
            }
            
            // التحقق من عدم وجود سجل حضور مسبق
            $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM attendance 
                WHERE participant_id = ? AND training_id = ? AND attendance_date = ?
            ");
            $stmt->execute([$_POST['participant_id'], $_POST['training_id'], $_POST['attendance_date']]);
            
            if ($stmt->fetchColumn() > 0) {
                throw new Exception('تم تسجيل الحضور مسبقاً لهذا اليوم');
            }
            
            $sql = "INSERT INTO attendance (participant_id, training_id, attendance_date, 
                    check_in_time, check_out_time, status) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                (int)$_POST['participant_id'],
                (int)$_POST['training_id'],
                $_POST['attendance_date'],
                $_POST['check_in_time'] ?? null,
                $_POST['check_out_time'] ?? null,
                $_POST['status'] ?? 'present'
            ]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'تم تسجيل الحضور بنجاح']);
            } else {
                throw new Exception('خطأ في حفظ البيانات');
            }
            break;
            
        case 'edit_attendance':
            // تعديل سجل حضور
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('طريقة طلب غير صحيحة');
            }
            
            if (empty($_POST['id'])) {
                throw new Exception('معرف سجل الحضور مطلوب');
            }
            
            $sql = "UPDATE attendance SET 
                    check_in_time = ?, check_out_time = ?, status = ? 
                    WHERE id = ?";
            
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                $_POST['check_in_time'] ?? null,
                $_POST['check_out_time'] ?? null,
                $_POST['status'] ?? 'present',
                (int)$_POST['id']
            ]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'تم تحديث سجل الحضور بنجاح']);
            } else {
                throw new Exception('خطأ في تحديث البيانات');
            }
            break;
            
        case 'delete_attendance':
            // حذف سجل حضور
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('طريقة طلب غير صحيحة');
            }
            
            if (empty($_POST['id'])) {
                throw new Exception('معرف سجل الحضور مطلوب');
            }
            
            $stmt = $pdo->prepare("DELETE FROM attendance WHERE id = ?");
            $result = $stmt->execute([(int)$_POST['id']]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'تم حذف سجل الحضور بنجاح']);
            } else {
                throw new Exception('خطأ في حذف البيانات');
            }
            break;
            
        case 'get_attendance_record':
            // جلب سجل حضور واحد
            if (empty($_GET['id'])) {
                throw new Exception('معرف سجل الحضور مطلوب');
            }
            
            $stmt = $pdo->prepare("
                SELECT a.*, p.name as participant_name, t.title_ar as training_title
                FROM attendance a
                JOIN participants p ON a.participant_id = p.id
                JOIN trainings t ON a.training_id = t.id
                WHERE a.id = ?
            ");
            $stmt->execute([(int)$_GET['id']]);
            $record = $stmt->fetch();
            
            if ($record) {
                echo json_encode(['success' => true, 'record' => $record]);
            } else {
                throw new Exception('سجل الحضور غير موجود');
            }
            break;
            
        case 'export':
            // تصدير تقرير الحضور
            $date = $_GET['date'] ?? date('Y-m-d');
            $training_id = $_GET['training_id'] ?? '';
            
            $sql = "
                SELECT p.name as participant_name, p.national_id, p.phone,
                       t.title_ar as training_title, s.name_ar as service_name,
                       a.attendance_date, a.check_in_time, a.check_out_time, a.status
                FROM attendance a
                JOIN participants p ON a.participant_id = p.id
                JOIN trainings t ON a.training_id = t.id
                JOIN services s ON t.service_id = s.id
                WHERE a.attendance_date = ?
            ";
            $params = [$date];
            
            if ($training_id) {
                $sql .= " AND a.training_id = ?";
                $params[] = $training_id;
            }
            
            $sql .= " ORDER BY a.check_in_time ASC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $records = $stmt->fetchAll();
            
            // إنشاء ملف CSV
            $filename = "attendance_report_" . $date . ".csv";
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            $output = fopen('php://output', 'w');
            
            // إضافة BOM للـ UTF-8
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // رؤوس الأعمدة
            fputcsv($output, [
                'اسم المشارك',
                'الرقم القومي',
                'رقم الهاتف',
                'التدريب',
                'الخدمة',
                'التاريخ',
                'وقت الحضور',
                'وقت الانصراف',
                'الحالة'
            ]);
            
            // البيانات
            foreach ($records as $record) {
                $status_map = [
                    'present' => 'حاضر',
                    'late' => 'متأخر',
                    'absent' => 'غائب'
                ];
                
                fputcsv($output, [
                    $record['participant_name'],
                    $record['national_id'],
                    $record['phone'],
                    $record['training_title'],
                    $record['service_name'],
                    $record['attendance_date'],
                    $record['check_in_time'],
                    $record['check_out_time'],
                    $status_map[$record['status']] ?? $record['status']
                ]);
            }
            
            fclose($output);
            break;
            
        default:
            throw new Exception('إجراء غير معروف');
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 