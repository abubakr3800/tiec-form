<?php
session_start();
require_once 'config/database.php';

$pdo = getDBConnection();
$participant = null;
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['token'])) {
    $token = trim($_POST['token']);
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM participants WHERE qr_code = ?");
        $stmt->execute([$token]);
        $participant = $stmt->fetch();
        
        if ($participant) {
            // حفظ بيانات المشارك في الجلسة
            $_SESSION['participant_id'] = $participant['id'];
            $_SESSION['participant_name'] = $participant['name'];
            $_SESSION['participant_token'] = $participant['qr_code'];
            
            $message = "تم تسجيل الدخول بنجاح! مرحباً " . htmlspecialchars($participant['name']);
            $message_type = "success";
        } else {
            $message = "الرمز غير صحيح أو غير موجود";
            $message_type = "error";
        }
    } catch (Exception $e) {
        $message = "خطأ في النظام: " . $e->getMessage();
        $message_type = "error";
    }
}

// جلب الخدمات المتاحة
$services = [];
try {
    $stmt = $pdo->query("SELECT * FROM services WHERE is_active = 1 ORDER BY sort_order");
    $services = $stmt->fetchAll();
} catch (Exception $e) {
    $message = "خطأ في جلب الخدمات: " . $e->getMessage();
    $message_type = "error";
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول بالرمز - TIEC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
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
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
        .participant-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        .service-card {
            border: 2px solid #e9ecef;
            border-radius: 15px;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .service-card:hover {
            border-color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
        }
        .service-card.selected {
            border-color: #667eea;
            background-color: #f8f9ff;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="login-container p-5">
                    <!-- العنوان -->
                    <div class="text-center mb-4">
                        <h2 class="mb-2">
                            <i class="fas fa-key"></i> تسجيل الدخول بالرمز
                        </h2>
                        <p class="text-muted">أدخل الرمز المميز الخاص بك</p>
                    </div>

                    <!-- رسائل النجاح/الخطأ -->
                    <?php if ($message): ?>
                        <div class="alert alert-<?= $message_type === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
                            <i class="fas fa-<?= $message_type === 'success' ? 'check-circle' : 'times-circle' ?>"></i>
                            <?= htmlspecialchars($message) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (!$participant): ?>
                        <!-- نموذج إدخال الرمز -->
                        <form method="POST" class="mb-4">
                            <div class="mb-3">
                                <label for="token" class="form-label">
                                    <i class="fas fa-qrcode"></i> الرمز المميز
                                </label>
                                <input type="text" class="form-control" id="token" name="token" 
                                       placeholder="أدخل الرمز المميز" required>
                                <div class="form-text">
                                    يمكنك العثور على الرمز في رسالة التأكيد أو صفحة التسجيل
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-sign-in-alt"></i> تسجيل الدخول
                                </button>
                            </div>
                        </form>

                        <!-- روابط إضافية -->
                        <div class="text-center">
                            <p class="mb-2">ليس لديك رمز؟</p>
                            <a href="index.php" class="btn btn-outline-primary me-2">
                                <i class="fas fa-user-plus"></i> تسجيل جديد
                            </a>
                            <a href="attendance_scanner.php" class="btn btn-outline-secondary">
                                <i class="fas fa-qrcode"></i> تسجيل الحضور
                            </a>
                        </div>
                    <?php else: ?>
                        <!-- معلومات المشارك -->
                        <div class="participant-info">
                            <h4 class="mb-3">
                                <i class="fas fa-user-check"></i> مرحباً <?= htmlspecialchars($participant['name']) ?>
                            </h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>الرقم القومي:</strong> <?= htmlspecialchars($participant['national_id']) ?><br>
                                    <strong>رقم الهاتف:</strong> <?= htmlspecialchars($participant['phone']) ?>
                                </div>
                                <div class="col-md-6">
                                    <strong>المحافظة:</strong> <?= htmlspecialchars($participant['governorate']) ?><br>
                                    <strong>نوع المشارك:</strong> 
                                    <?= $participant['participant_type'] === 'student' ? 'طالب' : 
                                        ($participant['participant_type'] === 'employee' ? 'موظف' : 'آخر') ?>
                                </div>
                            </div>
                        </div>

                        <!-- الخدمات المتاحة -->
                        <div class="mb-4">
                            <h5 class="mb-3">
                                <i class="fas fa-list"></i> الخدمات المتاحة
                            </h5>
                            <div class="row">
                                <?php foreach ($services as $service): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="service-card p-3" onclick="selectService(<?= $service['id'] ?>)">
                                            <h6 class="mb-2"><?= htmlspecialchars($service['name_ar']) ?></h6>
                                            <p class="text-muted small mb-2"><?= htmlspecialchars($service['description_ar']) ?></p>
                                            <span class="badge bg-primary">
                                                <?= $service['service_type'] === 'training' ? 'تدريب' : 
                                                    ($service['service_type'] === 'mentoring' ? 'استشارة' : 
                                                    ($service['service_type'] === 'fablab' ? 'معمل رقمي' : 'مساحة عمل')) ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- أزرار الإجراءات -->
                        <div class="text-center">
                            <a href="dashboard.php" class="btn btn-primary me-2">
                                <i class="fas fa-tachometer-alt"></i> لوحة التحكم
                            </a>
                            <a href="dashboard.php" class="btn btn-outline-primary me-2">
                                <i class="fas fa-user"></i> لوحه التحكم 
                            </a>
                            <a href="register_new_service.php" class="btn btn-outline-primary me-2">
                                <i class="fas fa-plus"></i> تسجيل في خدمة جديدة
                            </a>
                            <a href="view_my_registrations.php" class="btn btn-outline-primary me-2">
                                <i class="fas fa-list"></i> تسجيلاتي
                            </a>
                            <a href="attendance_scanner.php" class="btn btn-outline-secondary">
                                <i class="fas fa-qrcode"></i> تسجيل الحضور
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function selectService(serviceId) {
            // إزالة التحديد من جميع البطاقات
            document.querySelectorAll('.service-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // تحديد البطاقة المختارة
            event.currentTarget.classList.add('selected');
            
            // الانتقال إلى صفحة التسجيل مع الخدمة المختارة
            setTimeout(() => {
                window.location.href = `index.php?service_id=${serviceId}&token=<?= $participant['qr_code'] ?? '' ?>`;
            }, 500);
        }

        // إخفاء الرسائل بعد 5 ثواني
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html> 