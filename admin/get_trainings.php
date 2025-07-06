<?php
header('Content-Type: application/json');
require_once '../config/database.php';

try {
    $pdo = getDBConnection();
    
    // Get all trainings with trainer information
    $stmt = $pdo->prepare("
        SELECT t.*, tr.name_ar as trainer_name,
               (SELECT COUNT(*) FROM registrations r WHERE r.service_id = t.service_id AND r.status != 'cancelled') as participants_count
        FROM registrations t
        LEFT JOIN trainers tr ON t.trainer_id = tr.id
        LEFT JOIN services s ON t.service_id = s.id
        ORDER BY t.created_at DESC
    ");
    $stmt->execute();
    $trainings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format data for DataTable
    $formatted_trainings = [];
    foreach ($trainings as $training) {
        $formatted_trainings[] = [
            'id' => $training['id'],
            'title' => $training['service_name'] ?? 'خدمة غير محددة',
            'trainer_name' => $training['trainer_name'] ?? 'غير محدد',
            'start_date' => date('Y-m-d', strtotime($training['registration_date'])),
            'end_date' => date('Y-m-d', strtotime($training['registration_date'])),
            'time' => $training['start_time'] . ' - ' . $training['end_time'],
            'location' => 'TIEC',
            'participants_count' => $training['participants_count'] . '/50',
            'status' => $training['status']
        ];
    }
    
    echo json_encode([
        'data' => $formatted_trainings
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => 'حدث خطأ في جلب التدريبات: ' . $e->getMessage()
    ]);
}
?> 