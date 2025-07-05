<?php
header('Content-Type: application/json');
require_once '../cache/db.php';

try {
    // Get all trainings with trainer information
    $stmt = $pdo->prepare("
        SELECT t.*, tr.name as trainer_name,
               (SELECT COUNT(*) FROM training_participants tp WHERE tp.training_id = t.id AND tp.status != 'cancelled') as participants_count
        FROM trainings t
        LEFT JOIN trainers tr ON t.trainer_id = tr.id
        ORDER BY t.created_at DESC
    ");
    $stmt->execute();
    $trainings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format data for DataTable
    $formatted_trainings = [];
    foreach ($trainings as $training) {
        $formatted_trainings[] = [
            'id' => $training['id'],
            'title' => $training['title_ar'] ?: $training['title'],
            'trainer_name' => $training['trainer_name'],
            'start_date' => date('Y-m-d', strtotime($training['start_date'])),
            'end_date' => date('Y-m-d', strtotime($training['end_date'])),
            'time' => $training['start_time'] . ' - ' . $training['end_time'],
            'location' => $training['location_ar'] ?: $training['location'],
            'participants_count' => $training['participants_count'] . '/' . $training['max_participants'],
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