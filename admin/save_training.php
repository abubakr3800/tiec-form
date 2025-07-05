<?php
header('Content-Type: application/json');
require_once '../cache/db.php';

// التحقق من تسجيل الدخول
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'غير مصرح']);
    exit();
}

try {
    $training_id = $_POST['training_id'] ?? null;
    $title = $_POST['title'] ?? '';
    $title_ar = $_POST['title_ar'] ?? '';
    $description = $_POST['description'] ?? '';
    $description_ar = $_POST['description_ar'] ?? '';
    $trainer_id = $_POST['trainer_id'] ?? '';
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $start_time = $_POST['start_time'] ?? '';
    $end_time = $_POST['end_time'] ?? '';
    $max_participants = $_POST['max_participants'] ?? 50;
    $location = $_POST['location'] ?? '';
    $location_ar = $_POST['location_ar'] ?? '';
    $status = $_POST['status'] ?? 'active';
    
    // Validation
    if (empty($title) || empty($trainer_id) || empty($start_date) || empty($end_date)) {
        echo json_encode(['success' => false, 'message' => 'جميع الحقول المطلوبة يجب ملؤها']);
        exit();
    }
    
    if ($start_date > $end_date) {
        echo json_encode(['success' => false, 'message' => 'تاريخ البداية يجب أن يكون قبل تاريخ النهاية']);
        exit();
    }
    
    if ($start_time >= $end_time) {
        echo json_encode(['success' => false, 'message' => 'وقت البداية يجب أن يكون قبل وقت النهاية']);
        exit();
    }
    
    if ($training_id) {
        // Update existing training
        $stmt = $pdo->prepare("
            UPDATE trainings 
            SET title = ?, title_ar = ?, description = ?, description_ar = ?, 
                trainer_id = ?, start_date = ?, end_date = ?, start_time = ?, 
                end_time = ?, max_participants = ?, location = ?, location_ar = ?, 
                status = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        
        $stmt->execute([
            $title, $title_ar, $description, $description_ar, $trainer_id,
            $start_date, $end_date, $start_time, $end_time, $max_participants,
            $location, $location_ar, $status, $training_id
        ]);
        
        echo json_encode(['success' => true, 'message' => 'تم تحديث التدريب بنجاح']);
    } else {
        // Create new training
        $stmt = $pdo->prepare("
            INSERT INTO trainings (title, title_ar, description, description_ar, 
                                 trainer_id, start_date, end_date, start_time, 
                                 end_time, max_participants, location, location_ar, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $title, $title_ar, $description, $description_ar, $trainer_id,
            $start_date, $end_date, $start_time, $end_time, $max_participants,
            $location, $location_ar, $status
        ]);
        
        echo json_encode(['success' => true, 'message' => 'تم إضافة التدريب بنجاح']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'حدث خطأ: ' . $e->getMessage()]);
}
?> 