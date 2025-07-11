<?php
session_start();
require_once 'config/database.php';
require_once 'navigation_helper.php';

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
    
    // جلب إحصائيات المشارك
    $stats = [];
    
    // عدد التسجيلات
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM registrations WHERE participant_id = ?");
    $stmt->execute([$participant_id]);
    $stats['total_registrations'] = $stmt->fetchColumn();
    
    // عدد الحضور
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total FROM attendance a 
        JOIN registrations r ON a.registration_id = r.id 
        WHERE r.participant_id = ?
    ");
    $stmt->execute([$participant_id]);
    $stats['total_attendance'] = $stmt->fetchColumn();
    
    // عدد الحضور الفعلي
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total FROM attendance a 
        JOIN registrations r ON a.registration_id = r.id 
        WHERE r.participant_id = ? AND a.status = 'present'
    ");
    $stmt->execute([$participant_id]);
    $stats['present_attendance'] = $stmt->fetchColumn();
    
    // آخر 3 تسجيلات
    $stmt = $pdo->prepare("
        SELECT r.*, s.name_ar as service_name, s.service_type
        FROM registrations r
        JOIN services s ON r.service_id = s.id
        WHERE r.participant_id = ?
        ORDER BY r.created_at DESC
        LIMIT 3
    ");
    $stmt->execute([$participant_id]);
    $recent_registrations = $stmt->fetchAll();
    
    // آخر 3 سجلات حضور
    $stmt = $pdo->prepare("
        SELECT a.*, s.name_ar as service_name
        FROM attendance a
        JOIN registrations r ON a.registration_id = r.id
        LEFT JOIN services s ON r.service_id = s.id
        WHERE r.participant_id = ?
        ORDER BY a.attendance_date DESC, a.check_in_time DESC
        LIMIT 3
    ");
    $stmt->execute([$participant_id]);
    $recent_attendance = $stmt->fetchAll();
    
    // الخدمات المتاحة للتسجيل
    $stmt = $pdo->prepare("
        SELECT s.* FROM services s
        WHERE s.is_active = 1 
        AND s.id NOT IN (
            SELECT service_id FROM registrations WHERE participant_id = ?
        )
        ORDER BY s.sort_order
        LIMIT 4
    ");
    $stmt->execute([$participant_id]);
    $available_services = $stmt->fetchAll();
    
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
    <title><?= getPageTitle('dashboard') ?></title>
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
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            margin-bottom: 20px;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .stats-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .stats-label {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .service-card {
            border: 2px solid #e9ecef;
            border-radius: 15px;
            transition: all 0.3s ease;
            margin-bottom: 15px;
        }
        .service-card:hover {
            border-color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
        }
        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
        }
        .btn-success {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
        }
        .recent-item {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            border-left: 4px solid #667eea;
        }
        .welcome-section {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
        }
        .quick-actions {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <?php renderNavigationBar('dashboard', $participant); ?>
    
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="main-container p-5">
                    <!-- رسائل الخطأ -->
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle"></i>
                            <?= htmlspecialchars($error_message) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- رسالة الترحيب -->
                    <div class="welcome-section text-center">
                        <h2 class="mb-3">
                            <i class="fas fa-tachometer-alt"></i> لوحة التحكم
                        </h2>
                        <h4 class="mb-2">مرحباً <?= htmlspecialchars($participant['name']) ?></h4>
                        <p class="mb-0 opacity-75">مرحباً بك في نظام TIEC - يمكنك إدارة جميع خدماتك من هنا</p>
                    </div>

                    <!-- الإحصائيات -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="stats-card">
                                <div class="stats-number text-primary"><?= $stats['total_registrations'] ?></div>
                                <div class="stats-label">إجمالي التسجيلات</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stats-card">
                                <div class="stats-number text-success"><?= $stats['total_attendance'] ?></div>
                                <div class="stats-label">إجمالي الحضور</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stats-card">
                                <div class="stats-number text-info"><?= $stats['present_attendance'] ?></div>
                                <div class="stats-label">الحضور الفعلي</div>
                            </div>
                        </div>
                    </div>

                    <!-- الإجراءات السريعة -->
                    <div class="quick-actions">
                        <h5 class="mb-3">
                            <i class="fas fa-bolt"></i> الإجراءات السريعة
                        </h5>
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <a href="register_new_service.php" class="btn btn-success w-100">
                                    <i class="fas fa-plus"></i> تسجيل في خدمة جديدة
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="view_my_registrations.php" class="btn btn-primary w-100">
                                    <i class="fas fa-list"></i> عرض تسجيلاتي
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="certificate_generator.php" class="btn btn-warning w-100">
                                    <i class="fas fa-certificate"></i> إنشاء شهادة
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="attendance_scanner.php" class="btn btn-info w-100">
                                    <i class="fas fa-qrcode"></i> تسجيل الحضور
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- آخر التسجيلات -->
                        <div class="col-lg-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-history"></i> آخر التسجيلات
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($recent_registrations)): ?>
                                        <p class="text-muted text-center">لا توجد تسجيلات بعد</p>
                                    <?php else: ?>
                                        <?php foreach ($recent_registrations as $registration): ?>
                                            <div class="recent-item">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <h6 class="mb-1"><?= htmlspecialchars($registration['service_name']) ?></h6>
                                                        <p class="mb-1 small text-muted">
                                                            <?= date('Y/m/d', strtotime($registration['registration_date'])) ?>
                                                        </p>
                                                    </div>
                                                    <span class="badge bg-<?= $registration['status'] === 'confirmed' ? 'success' : 
                                                        ($registration['status'] === 'pending' ? 'warning' : 'danger') ?>">
                                                        <?= $registration_statuses[$registration['status']] ?>
                                                    </span>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- آخر الحضور -->
                        <div class="col-lg-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-calendar-check"></i> آخر الحضور
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($recent_attendance)): ?>
                                        <p class="text-muted text-center">لا توجد سجلات حضور بعد</p>
                                    <?php else: ?>
                                        <?php foreach ($recent_attendance as $record): ?>
                                            <div class="recent-item">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <h6 class="mb-1"><?= htmlspecialchars($record['service_name']) ?></h6>
                                                        <p class="mb-1 small text-muted">
                                                            <?= date('Y/m/d', strtotime($record['attendance_date'])) ?>
                                                        </p>
                                                    </div>
                                                    <span class="badge bg-<?= $record['status'] === 'present' ? 'success' : 
                                                        ($record['status'] === 'late' ? 'warning' : 'danger') ?>">
                                                        <?= $attendance_statuses[$record['status']] ?>
                                                    </span>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- الخدمات المتاحة للتسجيل -->
                    <?php if (!empty($available_services)): ?>
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-star"></i> خدمات متاحة للتسجيل
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($available_services as $service): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="service-card p-3">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <h6 class="mb-2"><?= htmlspecialchars($service['name_ar']) ?></h6>
                                                <span class="badge bg-<?= $service['service_type'] === 'training' ? 'primary' : 
                                                    ($service['service_type'] === 'mentoring' ? 'info' : 
                                                    ($service['service_type'] === 'fablab' ? 'warning' : 'success')) ?>">
                                                    <?= $service_types[$service['service_type']] ?>
                                                </span>
                                            </div>
                                            
                                            <p class="text-muted mb-3"><?= htmlspecialchars($service['description_ar']) ?></p>
                                            
                                            <div class="text-center">
                                                <a href="register_new_service.php?service_id=<?= $service['id'] ?>" 
                                                   class="btn btn-primary btn-sm">
                                                    <i class="fas fa-plus"></i> تسجيل
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 