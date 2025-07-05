<?php
session_start();
require_once '../cache/db.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// جلب معرف التدريب
$training_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$training_id) {
    header('Location: trainings.php');
    exit();
}

try {
    // جلب بيانات التدريب مع معلومات المدرب
    $stmt = $pdo->prepare("
        SELECT t.*, tr.name as trainer_name, tr.email as trainer_email, tr.phone as trainer_phone
        FROM trainings t
        LEFT JOIN trainers tr ON t.trainer_id = tr.id
        WHERE t.id = ?
    ");
    $stmt->execute([$training_id]);
    $training = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$training) {
        header('Location: trainings.php');
        exit();
    }
    
    // جلب المشاركين في التدريب
    $stmt = $pdo->prepare("
        SELECT p.*, tp.status as registration_status, tp.registration_date
        FROM participants p
        INNER JOIN training_participants tp ON p.id = tp.participant_id
        WHERE tp.training_id = ?
        ORDER BY tp.registration_date ASC
    ");
    $stmt->execute([$training_id]);
    $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // جلب سجل الحضور
    $stmt = $pdo->prepare("
        SELECT a.*, p.name as participant_name
        FROM attendance a
        INNER JOIN participants p ON a.participant_id = p.id
        WHERE a.training_id = ?
        ORDER BY a.attendance_date DESC, a.check_in_time DESC
    ");
    $stmt->execute([$training_id]);
    $attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    header('Location: trainings.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تفاصيل التدريب - TIEC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
        }
        .back-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 25px;
            padding: 10px 25px;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            color: white;
        }
        .info-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        .info-item:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: bold;
            color: #495057;
        }
        .info-value {
            color: #6c757d;
        }
        .status-badge {
            font-size: 0.9em;
            padding: 8px 15px;
        }
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }
        .participant-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2em;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0">
                        <i class="fas fa-graduation-cap text-primary"></i>
                        تفاصيل التدريب
                    </h2>
                    <a href="trainings.php" class="back-btn">
                        <i class="fas fa-arrow-right"></i>
                        العودة للتدريبات
                    </a>
                </div>

                <!-- Training Details Card -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">
                            <i class="fas fa-info-circle"></i>
                            معلومات التدريب
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">عنوان التدريب:</div>
                                    <div class="info-value"><?php echo htmlspecialchars($training['title_ar'] ?: $training['title']); ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">الحالة:</div>
                                    <div class="info-value">
                                        <?php
                                        $status_map = [
                                            'active' => '<span class="badge bg-success status-badge">نشط</span>',
                                            'inactive' => '<span class="badge bg-secondary status-badge">غير نشط</span>',
                                            'completed' => '<span class="badge bg-info status-badge">مكتمل</span>',
                                            'cancelled' => '<span class="badge bg-danger status-badge">ملغي</span>'
                                        ];
                                        echo $status_map[$training['status']] ?? $training['status'];
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">المدرب:</div>
                                    <div class="info-value"><?php echo htmlspecialchars($training['trainer_name']); ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">تاريخ البداية:</div>
                                    <div class="info-value"><?php echo date('Y-m-d', strtotime($training['start_date'])); ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">تاريخ النهاية:</div>
                                    <div class="info-value"><?php echo date('Y-m-d', strtotime($training['end_date'])); ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">الوقت:</div>
                                    <div class="info-value"><?php echo $training['start_time'] . ' - ' . $training['end_time']; ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">المكان:</div>
                                    <div class="info-value"><?php echo htmlspecialchars($training['location_ar'] ?: $training['location']); ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">عدد المشاركين:</div>
                                    <div class="info-value"><?php echo $training['current_participants'] . ' / ' . $training['max_participants']; ?></div>
                                </div>
                            </div>
                        </div>

                        <?php if ($training['description'] || $training['description_ar']): ?>
                        <div class="row">
                            <div class="col-12">
                                <div class="info-item">
                                    <div class="info-label">الوصف:</div>
                                    <div class="info-value"><?php echo htmlspecialchars($training['description_ar'] ?: $training['description']); ?></div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Participants Card -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-users"></i>
                            المشاركين (<?php echo count($participants); ?>)
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($participants)): ?>
                            <p class="text-muted text-center">لا يوجد مشاركين مسجلين في هذا التدريب</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>المشارك</th>
                                            <th>البريد الإلكتروني</th>
                                            <th>رقم الهاتف</th>
                                            <th>تاريخ التسجيل</th>
                                            <th>الحالة</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($participants as $participant): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="participant-avatar me-3">
                                                        <i class="fas fa-user"></i>
                                                    </div>
                                                    <?php echo htmlspecialchars($participant['name']); ?>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($participant['email']); ?></td>
                                            <td><?php echo htmlspecialchars($participant['phone']); ?></td>
                                            <td><?php echo date('Y-m-d', strtotime($participant['registration_date'])); ?></td>
                                            <td>
                                                <?php
                                                $status_map = [
                                                    'registered' => '<span class="badge bg-warning">مسجل</span>',
                                                    'attended' => '<span class="badge bg-success">حاضر</span>',
                                                    'absent' => '<span class="badge bg-danger">غائب</span>',
                                                    'cancelled' => '<span class="badge bg-secondary">ملغي</span>'
                                                ];
                                                echo $status_map[$participant['registration_status']] ?? $participant['registration_status'];
                                                ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Attendance Card -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-calendar-check"></i>
                            سجل الحضور (<?php echo count($attendance); ?>)
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($attendance)): ?>
                            <p class="text-muted text-center">لا يوجد سجل حضور لهذا التدريب</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>المشارك</th>
                                            <th>التاريخ</th>
                                            <th>وقت الحضور</th>
                                            <th>الحالة</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($attendance as $record): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($record['participant_name']); ?></td>
                                            <td><?php echo date('Y-m-d', strtotime($record['attendance_date'])); ?></td>
                                            <td><?php echo $record['check_in_time']; ?></td>
                                            <td>
                                                <?php
                                                $attendance_status_map = [
                                                    'present' => '<span class="badge bg-success">حاضر</span>',
                                                    'absent' => '<span class="badge bg-danger">غائب</span>',
                                                    'late' => '<span class="badge bg-warning">متأخر</span>',
                                                    'excused' => '<span class="badge bg-info">معذور</span>'
                                                ];
                                                echo $attendance_status_map[$record['status']] ?? $record['status'];
                                                ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 