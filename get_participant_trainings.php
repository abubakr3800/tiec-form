<?php
header('Content-Type: application/json');
require_once 'cache/db.php';

$token = $_GET['token'] ?? null;

if (!$token) {
    echo json_encode(['success' => false, 'message' => 'Token مطلوب']);
    exit();
}

try {
    // Get participant by token
    $stmt = $pdo->prepare("SELECT * FROM participants WHERE token = ?");
    $stmt->execute([$token]);
    $participant = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$participant) {
        echo json_encode(['success' => false, 'message' => 'المشارك غير موجود']);
        exit();
    }
    
    // Get participant's trainings
    $stmt = $pdo->prepare("
        SELECT t.*, tp.status as registration_status, tr.name as trainer_name
        FROM trainings t
        INNER JOIN training_participants tp ON t.id = tp.training_id
        INNER JOIN trainers tr ON t.trainer_id = tr.id
        WHERE tp.participant_id = ? 
        AND t.status = 'active'
        ORDER BY t.start_date ASC, t.start_time ASC
    ");
    $stmt->execute([$participant['id']]);
    $trainings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format trainings for response
    $formatted_trainings = [];
    foreach ($trainings as $training) {
        $formatted_trainings[] = [
            'id' => $training['id'],
            'title' => $training['title_ar'] ?: $training['title'],
            'start_date' => date('Y-m-d', strtotime($training['start_date'])),
            'end_date' => date('Y-m-d', strtotime($training['end_date'])),
            'start_time' => $training['start_time'],
            'end_time' => $training['end_time'],
            'trainer_name' => $training['trainer_name'],
            'status' => $training['registration_status'] === 'attended' ? 'حاضر' : 'مسجل',
            'location' => $training['location_ar'] ?: $training['location']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'trainings' => $formatted_trainings
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'حدث خطأ في جلب التدريبات: ' . $e->getMessage()
    ]);
}
?> 