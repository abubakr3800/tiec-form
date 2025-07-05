<?php
header('Content-Type: application/json');
require_once 'cache/db.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$token = $input['token'] ?? null;

if (!$token) {
    echo json_encode(['success' => false, 'message' => 'Token مطلوب']);
    exit();
}

try {
    // Get current date
    $current_date = date('Y-m-d');
    $current_time = date('H:i:s');
    
    // Get participant by token
    $stmt = $pdo->prepare("SELECT * FROM participants WHERE token = ?");
    $stmt->execute([$token]);
    $participant = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$participant) {
        echo json_encode(['success' => false, 'message' => 'المشارك غير موجود']);
        exit();
    }
    
    // Check if participant is registered for any training today
    $stmt = $pdo->prepare("
        SELECT t.*, tp.status as registration_status, tr.name as trainer_name
        FROM trainings t
        INNER JOIN training_participants tp ON t.id = tp.training_id
        INNER JOIN trainers tr ON t.trainer_id = tr.id
        WHERE tp.participant_id = ? 
        AND t.start_date <= ? 
        AND t.end_date >= ?
        AND t.status = 'active'
        AND tp.status IN ('registered', 'attended')
        ORDER BY t.start_time ASC
    ");
    $stmt->execute([$participant['id'], $current_date, $current_date]);
    $trainings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($trainings)) {
        echo json_encode([
            'success' => false, 
            'message' => 'أنت غير مسجل في أي تدريب اليوم. يرجى الاتصال بالمشرف للتسجيل.'
        ]);
        exit();
    }
    
    // Check if already attended today
    $stmt = $pdo->prepare("
        SELECT * FROM attendance 
        WHERE participant_id = ? 
        AND attendance_date = ?
    ");
    $stmt->execute([$participant['id'], $current_date]);
    $existing_attendance = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing_attendance) {
        echo json_encode([
            'success' => false, 
            'message' => 'تم تسجيل حضورك مسبقاً اليوم'
        ]);
        exit();
    }
    
    // Find the appropriate training (closest to current time)
    $selected_training = null;
    foreach ($trainings as $training) {
        if ($current_time >= $training['start_time'] && $current_time <= $training['end_time']) {
            $selected_training = $training;
            break;
        }
    }
    
    // If no exact match, take the first training of the day
    if (!$selected_training) {
        $selected_training = $trainings[0];
    }
    
    // Register attendance
    $stmt = $pdo->prepare("
        INSERT INTO attendance (training_id, participant_id, attendance_date, check_in_time, status)
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $attendance_status = 'present';
    if ($current_time > $selected_training['start_time']) {
        $attendance_status = 'late';
    }
    
    $stmt->execute([
        $selected_training['id'],
        $participant['id'],
        $current_date,
        $current_time,
        $attendance_status
    ]);
    
    // Update training participant status
    $stmt = $pdo->prepare("
        UPDATE training_participants 
        SET status = 'attended' 
        WHERE training_id = ? AND participant_id = ?
    ");
    $stmt->execute([$selected_training['id'], $participant['id']]);
    
    // Prepare response
    $training_info = [
        'title' => $selected_training['title_ar'] ?: $selected_training['title'],
        'date' => date('Y-m-d', strtotime($current_date)),
        'time' => $selected_training['start_time'] . ' - ' . $selected_training['end_time'],
        'trainer' => $selected_training['trainer_name']
    ];
    
    $message = "تم تسجيل حضورك بنجاح في تدريب: " . $training_info['title'];
    if ($attendance_status === 'late') {
        $message .= " (تأخرت في الحضور)";
    }
    
    echo json_encode([
        'success' => true,
        'message' => $message,
        'training_info' => $training_info,
        'attendance_status' => $attendance_status
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'حدث خطأ في تسجيل الحضور: ' . $e->getMessage()
    ]);
}
?> 