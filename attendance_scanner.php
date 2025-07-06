<?php
session_start();
require_once 'config/database.php';

// التحقق من وجود QR code أو رقم قومي
$qr_data = $_GET['qr'] ?? '';
$national_id = $_GET['national_id'] ?? '';

$pdo = getDBConnection();
$participant = null;
$training = null;
$message = '';
$message_type = '';

if ($qr_data || $national_id) {
    try {
        if ($qr_data) {
            // البحث بواسطة QR data
            $qr_decoded = json_decode($qr_data, true);
            if ($qr_decoded && isset($qr_decoded['national_id'])) {
                $stmt = $pdo->prepare("SELECT * FROM participants WHERE national_id = ?");
                $stmt->execute([$qr_decoded['national_id']]);
                $participant = $stmt->fetch();
            }
        } else {
            // البحث بواسطة الرقم القومي
            $stmt = $pdo->prepare("SELECT * FROM participants WHERE national_id = ?");
            $stmt->execute([$national_id]);
            $participant = $stmt->fetch();
        }
        
        if ($participant) {
            // البحث عن تدريب اليوم
            $today = date('Y-m-d');
            $stmt = $pdo->prepare("
                SELECT t.*, s.name_ar as service_name, tr.name as trainer_name
                FROM registrations r
                JOIN trainings t ON r.training_id = t.id
                JOIN services s ON t.service_id = s.id
                JOIN trainers tr ON t.trainer_id = tr.id
                WHERE r.participant_id = ? 
                AND r.status = 'confirmed'
                AND ? BETWEEN t.start_date AND t.end_date
                AND t.is_active = 1
            ");
            $stmt->execute([$participant['id'], $today]);
            $training = $stmt->fetch();
            
            if ($training) {
                // التحقق من عدم تسجيل الحضور مسبقاً
                $stmt = $pdo->prepare("
                    SELECT * FROM attendance 
                    WHERE participant_id = ? AND training_id = ? AND attendance_date = ?
                ");
                $stmt->execute([$participant['id'], $training['id'], $today]);
                $existing_attendance = $stmt->fetch();
                
                if (!$existing_attendance) {
                    // تسجيل الحضور
                    $stmt = $pdo->prepare("
                        INSERT INTO attendance (participant_id, training_id, attendance_date, check_in_time, status)
                        VALUES (?, ?, ?, NOW(), 'present')
                    ");
                    $stmt->execute([$participant['id'], $training['id'], $today]);
                    
                    $message = "تم تسجيل الحضور بنجاح!";
                    $message_type = "success";
                } else {
                    $message = "تم تسجيل الحضور مسبقاً لهذا اليوم";
                    $message_type = "warning";
                }
            } else {
                $message = "لا يوجد تدريب مسجل لهذا اليوم";
                $message_type = "info";
            }
        } else {
            $message = "لم يتم العثور على المشارك";
            $message_type = "error";
        }
    } catch (Exception $e) {
        $message = "خطأ في النظام: " . $e->getMessage();
        $message_type = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الحضور - TIEC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .scanner-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
        }
        .qr-scanner {
            width: 100%;
            max-width: 400px;
            height: 300px;
            border: 2px solid #ddd;
            border-radius: 10px;
            margin: 20px auto;
        }
        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
        .participant-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin: 15px 0;
        }
        .training-info {
            background: #e3f2fd;
            border-radius: 10px;
            padding: 15px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="scanner-container p-5">
                    <!-- العنوان -->
                    <div class="text-center mb-4">
                        <h2 class="mb-2">
                            <i class="fas fa-qrcode"></i> تسجيل الحضور
                        </h2>
                        <p class="text-muted">امسح رمز QR أو أدخل الرقم القومي</p>
                    </div>

                    <!-- رسائل النجاح/الخطأ -->
                    <?php if ($message): ?>
                        <div class="alert alert-<?= $message_type === 'success' ? 'success' : ($message_type === 'warning' ? 'warning' : ($message_type === 'info' ? 'info' : 'danger')) ?> alert-dismissible fade show" role="alert">
                            <i class="fas fa-<?= $message_type === 'success' ? 'check-circle' : ($message_type === 'warning' ? 'exclamation-triangle' : ($message_type === 'info' ? 'info-circle' : 'times-circle')) ?>"></i>
                            <?= htmlspecialchars($message) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- معلومات المشارك والتدريب -->
                    <?php if ($participant): ?>
                        <div class="participant-info">
                            <h5><i class="fas fa-user"></i> معلومات المشارك</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>الاسم:</strong> <?= htmlspecialchars($participant['name']) ?><br>
                                    <strong>الرقم القومي:</strong> <?= htmlspecialchars($participant['national_id']) ?>
                                </div>
                                <div class="col-md-6">
                                    <strong>رقم الهاتف:</strong> <?= htmlspecialchars($participant['phone']) ?><br>
                                    <strong>نوع المشارك:</strong> <?= $participant['participant_type'] === 'student' ? 'طالب' : ($participant['participant_type'] === 'employee' ? 'موظف' : 'آخر') ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($training): ?>
                        <div class="training-info">
                            <h5><i class="fas fa-graduation-cap"></i> معلومات التدريب</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>الخدمة:</strong> <?= htmlspecialchars($training['service_name']) ?><br>
                                    <strong>التدريب:</strong> <?= htmlspecialchars($training['title_ar']) ?>
                                </div>
                                <div class="col-md-6">
                                    <strong>المدرب:</strong> <?= htmlspecialchars($training['trainer_name']) ?><br>
                                    <strong>الوقت:</strong> <?= $training['start_time'] ?> - <?= $training['end_time'] ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- QR Scanner -->
                    <div class="text-center mb-4">
                        <div id="qr-reader" class="qr-scanner"></div>
                        <p class="text-muted mt-2">امسح رمز QR للمشارك</p>
                    </div>

                    <!-- إدخال الرقم القومي -->
                    <div class="text-center mb-4">
                        <h5><i class="fas fa-id-card"></i> أو أدخل الرقم القومي</h5>
                        <form method="GET" class="d-inline-block">
                            <div class="input-group mb-3" style="max-width: 300px;">
                                <input type="text" class="form-control" name="national_id" 
                                       placeholder="الرقم القومي" required 
                                       pattern="[0-9]{14}" title="الرقم القومي يجب أن يكون 14 رقم">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search"></i> بحث
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- أزرار إضافية -->
                    <div class="text-center">
                        <a href="index.php" class="btn btn-secondary me-2">
                            <i class="fas fa-home"></i> الرئيسية
                        </a>
                        <a href="admin/index.php" class="btn btn-outline-primary">
                            <i class="fas fa-cogs"></i> لوحة التحكم
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/html5-qrcode"></script>
    
    <script>
        // تهيئة QR Scanner
        function onScanSuccess(decodedText, decodedResult) {
            // إيقاف الماسح
            html5QrcodeScanner.clear();
            
            // إرسال البيانات إلى الصفحة
            window.location.href = 'attendance_scanner.php?qr=' + encodeURIComponent(decodedText);
        }

        function onScanFailure(error) {
            // معالجة أخطاء المسح
            console.warn(`خطأ في المسح: ${error}`);
        }

        // إنشاء QR Scanner
        let html5QrcodeScanner = new Html5QrcodeScanner(
            "qr-reader",
            { fps: 10, qrbox: {width: 250, height: 250} },
            false
        );
        html5QrcodeScanner.render(onScanSuccess, onScanFailure);

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