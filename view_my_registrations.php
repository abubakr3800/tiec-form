<?php
session_start();
require_once 'config/database.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['participant_id'])) {
    header('Location: token_login.php');
    exit();
}

$pdo = getDBConnection();
$participant_id = $_SESSION['participant_id'];

try {
    // جلب بيانات المشارك
    $stmt = $pdo->prepare("SELECT * FROM participants WHERE id = ?");
    $stmt->execute([$participant_id]);
    $participant = $stmt->fetch();
    
    if (!$participant) {
        session_destroy();
        header('Location: token_login.php');
        exit();
    }
    
    // جلب تسجيلات المشارك
    $stmt = $pdo->prepare("
        SELECT r.*, s.name_ar as service_name, s.service_type,
               tr.name_ar as trainer_name
        FROM registrations r
        JOIN services s ON r.service_id = s.id
        LEFT JOIN trainers tr ON r.trainer_id = tr.id
        WHERE r.participant_id = ?
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$participant_id]);
    $registrations = $stmt->fetchAll();
    
    // جلب سجل الحضور
    $stmt = $pdo->prepare("
        SELECT a.*, s.name_ar as service_name
        FROM attendance a
        JOIN registrations r ON a.registration_id = r.id
        LEFT JOIN services s ON r.service_id = s.id
        WHERE r.participant_id = ?
        ORDER BY a.attendance_date DESC, a.check_in_time DESC
    ");
    $stmt->execute([$participant_id]);
    $attendance_records = $stmt->fetchAll();
    
} catch (Exception $e) {
    $error_message = "خطأ في جلب البيانات: " . $e->getMessage();
}

// تحويل أنواع الخدمات للعربية
$service_types = [
    'training' => 'تدريب',
    'mentoring' => 'استشارة',
    'fablab' => 'معمل رقمي',
    'coworking' => 'مساحة عمل'
];

// تحويل حالات التسجيل للعربية
$registration_statuses = [
    'pending' => 'قيد الانتظار',
    'confirmed' => 'مؤكد',
    'cancelled' => 'ملغي'
];

// تحويل حالات الحضور للعربية
$attendance_statuses = [
    'present' => 'حاضر',
    'absent' => 'غائب',
    'late' => 'متأخر'
];
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيلاتي - TIEC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .main-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
        }
        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
        }
        .registration-card {
            border: 2px solid #e9ecef;
            border-radius: 15px;
            transition: all 0.3s ease;
            margin-bottom: 15px;
        }
        .registration-card:hover {
            border-color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
        }
        .status-badge {
            font-size: 0.8em;
            padding: 0.4em 0.8em;
        }
        .attendance-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
        }
        .nav-tabs .nav-link {
            border-radius: 10px 10px 0 0;
            border: none;
            color: #6c757d;
        }
        .nav-tabs .nav-link.active {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border: none;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="main-container p-5">
                    <!-- العنوان -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2 class="mb-1">
                                <i class="fas fa-list"></i> تسجيلاتي
                            </h2>
                            <p class="text-muted">
                                مرحباً <?= htmlspecialchars($participant['name']) ?>
                                <span class="badge bg-success ms-2">مسجل دخول</span>
                            </p>
                        </div>
                        <div>
                            <a href="index.php" class="btn btn-primary me-2">
                                <i class="fas fa-plus"></i> تسجيل جديد
                            </a>
                            <a href="logout.php" class="btn btn-outline-secondary">
                                <i class="fas fa-sign-out-alt"></i> تسجيل خروج
                            </a>
                        </div>
                    </div>

                    <!-- رسائل الخطأ -->
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle"></i>
                            <?= htmlspecialchars($error_message) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- تبويبات -->
                    <ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="registrations-tab" data-bs-toggle="tab" 
                                    data-bs-target="#registrations" type="button" role="tab">
                                <i class="fas fa-clipboard-list"></i> تسجيلاتي (<?= count($registrations) ?>)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="attendance-tab" data-bs-toggle="tab" 
                                    data-bs-target="#attendance" type="button" role="tab">
                                <i class="fas fa-calendar-check"></i> سجل الحضور (<?= count($attendance_records) ?>)
                            </button>
                        </li>
                    </ul>

                    <!-- محتوى التبويبات -->
                    <div class="tab-content" id="myTabContent">
                        <!-- تبويب التسجيلات -->
                        <div class="tab-pane fade show active" id="registrations" role="tabpanel">
                            <?php if (empty($registrations)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">لا توجد تسجيلات بعد</h5>
                                    <p class="text-muted">قم بالتسجيل في إحدى خدماتنا</p>
                                    <a href="index.php" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> تسجيل جديد
                                    </a>
                                </div>
                            <?php else: ?>
                                <?php foreach ($registrations as $registration): ?>
                                    <div class="registration-card p-4">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <h5 class="mb-2">
                                                    <i class="fas fa-<?= $registration['service_type'] === 'training' ? 'graduation-cap' : 
                                                        ($registration['service_type'] === 'mentoring' ? 'user-tie' : 
                                                        ($registration['service_type'] === 'fablab' ? 'cogs' : 'building')) ?>"></i>
                                                    <?= htmlspecialchars($registration['service_name']) ?>
                                                </h5>
                                                
                                                <?php if ($registration['trainer_name']): ?>
                                                    <p class="mb-2">
                                                        <strong>المدرب:</strong> <?= htmlspecialchars($registration['trainer_name']) ?>
                                                    </p>
                                                <?php endif; ?>
                                                
                                                <p class="mb-2">
                                                    <strong>تاريخ التسجيل:</strong> 
                                                    <?= date('Y/m/d', strtotime($registration['registration_date'])) ?>
                                                </p>
                                                
                                                <p class="mb-0">
                                                    <strong>تاريخ التسجيل:</strong> 
                                                    <?= date('Y/m/d H:i', strtotime($registration['created_at'])) ?>
                                                </p>
                                                
                                                <?php if ($registration['notes']): ?>
                                                    <p class="mb-0 mt-2">
                                                        <strong>ملاحظات:</strong> 
                                                        <small class="text-muted"><?= htmlspecialchars($registration['notes']) ?></small>
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="col-md-4 text-end">
                                                <span class="badge bg-<?= $registration['status'] === 'confirmed' ? 'success' : 
                                                    ($registration['status'] === 'pending' ? 'warning' : 'danger') ?> status-badge">
                                                    <?= $registration_statuses[$registration['status']] ?>
                                                </span>
                                                
                                                <span class="badge bg-info status-badge ms-2">
                                                    <?= $service_types[$registration['service_type']] ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <!-- تبويب الحضور -->
                        <div class="tab-pane fade" id="attendance" role="tabpanel">
                            <?php if (empty($attendance_records)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-calendar-check fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">لا توجد سجلات حضور بعد</h5>
                                    <p class="text-muted">سيظهر هنا سجل حضورك للتدريبات</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($attendance_records as $record): ?>
                                    <div class="attendance-card">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <h6 class="mb-2"><?= htmlspecialchars($record['service_name']) ?></h6>
                                                <p class="mb-1">
                                                    <strong>التاريخ:</strong> <?= date('Y/m/d', strtotime($record['attendance_date'])) ?>
                                                </p>
                                                <?php if ($record['check_in_time']): ?>
                                                    <p class="mb-1">
                                                        <strong>وقت الحضور:</strong> <?= $record['check_in_time'] ?>
                                                    </p>
                                                <?php endif; ?>
                                                <?php if ($record['check_out_time']): ?>
                                                    <p class="mb-0">
                                                        <strong>وقت الانصراف:</strong> <?= $record['check_out_time'] ?>
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="col-md-4 text-end">
                                                <span class="badge bg-<?= $record['status'] === 'present' ? 'success' : 
                                                    ($record['status'] === 'late' ? 'warning' : 'danger') ?> status-badge">
                                                    <?= $attendance_statuses[$record['status']] ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 