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
    
    // جلب آخر 5 تسجيلات
    $stmt = $pdo->prepare("
        SELECT r.*, s.name_ar as service_name, s.service_type,
               tr.name_ar as trainer_name
        FROM registrations r
        JOIN services s ON r.service_id = s.id
        LEFT JOIN trainers tr ON r.trainer_id = tr.id
        WHERE r.participant_id = ?
        ORDER BY r.created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$participant_id]);
    $recent_registrations = $stmt->fetchAll();
    
    // جلب آخر 5 سجلات حضور
    $stmt = $pdo->prepare("
        SELECT a.*, s.name_ar as service_name
        FROM attendance a
        JOIN registrations r ON a.registration_id = r.id
        LEFT JOIN services s ON r.service_id = s.id
        WHERE r.participant_id = ?
        ORDER BY a.attendance_date DESC, a.check_in_time DESC
        LIMIT 5
    ");
    $stmt->execute([$participant_id]);
    $recent_attendance = $stmt->fetchAll();
    
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

// تحويل الجنس للعربية
$gender_types = [
    'male' => 'ذكر',
    'female' => 'أنثى'
];

// تحويل نوع المشارك للعربية
$participant_types = [
    'student' => 'طالب',
    'employee' => 'موظف',
    'other' => 'آخر'
];
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الملف الشخصي - <?= htmlspecialchars($participant['name']) ?> - TIEC</title>
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
        .profile-header {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border-radius: 20px 20px 0 0;
            padding: 30px;
        }
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            margin: 0 auto 20px;
        }
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .stats-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #667eea;
        }
        .stats-label {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .info-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f8f9fa;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #495057;
        }
        .info-value {
            color: #6c757d;
        }
        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
        }
        .btn-outline-primary {
            border-color: #667eea;
            color: #667eea;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
        }
        .btn-outline-primary:hover {
            background: #667eea;
            border-color: #667eea;
        }
        .recent-item {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            border-left: 4px solid #667eea;
        }
        .qr-code-section {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
        }
        .qr-code {
            background: white;
            padding: 20px;
            border-radius: 10px;
            display: inline-block;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="main-container">
                    <!-- رأس الملف الشخصي -->
                    <div class="profile-header">
                        <div class="text-center">
                            <div class="profile-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <h2 class="mb-2"><?= htmlspecialchars($participant['name']) ?></h2>
                            <p class="mb-0 opacity-75">
                                <i class="fas fa-id-card"></i> 
                                <?= htmlspecialchars($participant['national_id']) ?>
                            </p>
                        </div>
                    </div>

                    <!-- أزرار التنقل -->
                    <div class="p-4 border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <a href="dashboard.php" class="btn btn-primary me-2">
                                    <i class="fas fa-tachometer-alt"></i> لوحة التحكم
                                </a>
                                <a href="view_my_registrations.php" class="btn btn-outline-primary me-2">
                                    <i class="fas fa-list"></i> تسجيلاتي
                                </a>
                                <a href="register_new_service.php" class="btn btn-outline-primary me-2">
                                    <i class="fas fa-plus"></i> تسجيل في خدمة جديدة
                                </a>
                                <a href="certificate_generator.php" class="btn btn-outline-primary">
                                    <i class="fas fa-certificate"></i> إنشاء شهادة
                                </a>
                            </div>
                            <div>
                                <a href="logout.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-sign-out-alt"></i> تسجيل خروج
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- رسائل الخطأ -->
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger alert-dismissible fade show m-4" role="alert">
                            <i class="fas fa-exclamation-triangle"></i>
                            <?= htmlspecialchars($error_message) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="p-4">
                        <!-- الإحصائيات -->
                        <div class="row mb-4">
                            <div class="col-md-4 mb-3">
                                <div class="stats-card">
                                    <div class="stats-number"><?= $stats['total_registrations'] ?></div>
                                    <div class="stats-label">إجمالي التسجيلات</div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="stats-card">
                                    <div class="stats-number"><?= $stats['total_attendance'] ?></div>
                                    <div class="stats-label">إجمالي الحضور</div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="stats-card">
                                    <div class="stats-number"><?= $stats['present_attendance'] ?></div>
                                    <div class="stats-label">الحضور الفعلي</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- المعلومات الشخصية -->
                            <div class="col-lg-6 mb-4">
                                <div class="info-card">
                                    <h5 class="mb-3">
                                        <i class="fas fa-user-circle text-primary"></i> المعلومات الشخصية
                                    </h5>
                                    
                                    <div class="info-row">
                                        <span class="info-label">الاسم الكامل:</span>
                                        <span class="info-value"><?= htmlspecialchars($participant['name']) ?></span>
                                    </div>
                                    
                                    <div class="info-row">
                                        <span class="info-label">الرقم القومي:</span>
                                        <span class="info-value"><?= htmlspecialchars($participant['national_id']) ?></span>
                                    </div>
                                    
                                    <div class="info-row">
                                        <span class="info-label">المحافظة:</span>
                                        <span class="info-value"><?= htmlspecialchars($participant['governorate']) ?></span>
                                    </div>
                                    
                                    <div class="info-row">
                                        <span class="info-label">الجنس:</span>
                                        <span class="info-value"><?= $gender_types[$participant['gender']] ?? $participant['gender'] ?></span>
                                    </div>
                                    
                                    <div class="info-row">
                                        <span class="info-label">العمر:</span>
                                        <span class="info-value"><?= $participant['age'] ?> سنة</span>
                                    </div>
                                    
                                    <div class="info-row">
                                        <span class="info-label">رقم الهاتف:</span>
                                        <span class="info-value"><?= htmlspecialchars($participant['phone']) ?></span>
                                    </div>
                                    
                                    <?php if ($participant['whatsapp']): ?>
                                    <div class="info-row">
                                        <span class="info-label">رقم الواتساب:</span>
                                        <span class="info-value"><?= htmlspecialchars($participant['whatsapp']) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($participant['email']): ?>
                                    <div class="info-row">
                                        <span class="info-label">البريد الإلكتروني:</span>
                                        <span class="info-value"><?= htmlspecialchars($participant['email']) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="info-row">
                                        <span class="info-label">نوع المشارك:</span>
                                        <span class="info-value"><?= $participant_types[$participant['participant_type']] ?? $participant['participant_type'] ?></span>
                                    </div>
                                    
                                    <?php if ($participant['participant_type'] === 'student'): ?>
                                        <?php if ($participant['university']): ?>
                                        <div class="info-row">
                                            <span class="info-label">الجامعة:</span>
                                            <span class="info-value"><?= htmlspecialchars($participant['university']) ?></span>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($participant['education_stage']): ?>
                                        <div class="info-row">
                                            <span class="info-label">المرحلة الدراسية:</span>
                                            <span class="info-value"><?= htmlspecialchars($participant['education_stage']) ?></span>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($participant['faculty']): ?>
                                        <div class="info-row">
                                            <span class="info-label">الكلية:</span>
                                            <span class="info-value"><?= htmlspecialchars($participant['faculty']) ?></span>
                                        </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    
                                    <?php if ($participant['participant_type'] === 'employee' && $participant['work_employer']): ?>
                                    <div class="info-row">
                                        <span class="info-label">جهة العمل:</span>
                                        <span class="info-value"><?= htmlspecialchars($participant['work_employer']) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="info-row">
                                        <span class="info-label">تاريخ التسجيل:</span>
                                        <span class="info-value"><?= date('Y/m/d', strtotime($participant['created_at'])) ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- الرمز المميز -->
                            <div class="col-lg-6 mb-4">
                                <div class="info-card">
                                    <h5 class="mb-3">
                                        <i class="fas fa-qrcode text-primary"></i> الرمز المميز
                                    </h5>
                                    
                                    <div class="qr-code-section">
                                        <p class="text-muted mb-3">استخدم هذا الرمز للوصول السريع لبياناتك</p>
                                        
                                        <div class="qr-code">
                                            <div id="qr-code"></div>
                                        </div>
                                        
                                        <div class="mt-3">
                                            <code class="bg-light p-2 rounded"><?= htmlspecialchars($participant['qr_code']) ?></code>
                                        </div>
                                        
                                        <p class="text-muted mt-3 small">
                                            <i class="fas fa-info-circle"></i>
                                            احفظ هذا الرمز أو التقط صورة له للوصول السريع
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- آخر التسجيلات -->
                        <div class="row">
                            <div class="col-lg-6 mb-4">
                                <div class="info-card">
                                    <h5 class="mb-3">
                                        <i class="fas fa-history text-primary"></i> آخر التسجيلات
                                    </h5>
                                    
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
                                                        <?php if ($registration['trainer_name']): ?>
                                                            <p class="mb-0 small text-muted">
                                                                المدرب: <?= htmlspecialchars($registration['trainer_name']) ?>
                                                            </p>
                                                        <?php endif; ?>
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

                            <!-- آخر الحضور -->
                            <div class="col-lg-6 mb-4">
                                <div class="info-card">
                                    <h5 class="mb-3">
                                        <i class="fas fa-calendar-check text-primary"></i> آخر الحضور
                                    </h5>
                                    
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
                                                        <?php if ($record['check_in_time']): ?>
                                                            <p class="mb-0 small text-muted">
                                                                وقت الحضور: <?= $record['check_in_time'] ?>
                                                            </p>
                                                        <?php endif; ?>
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
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    
    <script>
        // إنشاء رمز QR
        document.addEventListener('DOMContentLoaded', function() {
            const qrCode = new QRCode(document.getElementById('qr-code'), {
                text: '<?= $participant['qr_code'] ?>',
                width: 128,
                height: 128,
                colorDark: '#000000',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.H
            });
        });
    </script>
</body>
</html> 