<?php
session_start();
require_once '../cache/db.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// جلب معرف الخدمة
$service_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$service_id) {
    header('Location: services_manager.php');
    exit();
}

try {
    // جلب بيانات الخدمة
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$service_id]);
    $service = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$service) {
        header('Location: services_manager.php');
        exit();
    }
    
    // جلب الأسئلة المرتبطة بالخدمة
    $stmt = $pdo->prepare("SELECT * FROM service_questions WHERE service_id = ? ORDER BY sort_order");
    $stmt->execute([$service_id]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    header('Location: services_manager.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>عرض الخدمة - لوحة التحكم</title>
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
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
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
        .question-card {
            background: #f8f9fa;
            border-radius: 10px;
            margin-bottom: 15px;
            padding: 15px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0">
                        <i class="fas fa-eye text-primary"></i>
                        عرض تفاصيل الخدمة
                    </h2>
                    <a href="services_manager.php" class="back-btn">
                        <i class="fas fa-arrow-right"></i>
                        العودة للخدمات
                    </a>
                </div>

                <!-- Service Details Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="mb-0">
                            <i class="fas fa-cogs"></i>
                            <?php echo htmlspecialchars($service['name_ar'] ?? $service['service_name'] ?? 'الخدمة'); ?>
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">اسم الخدمة:</div>
                                    <div class="info-value"><?php echo htmlspecialchars($service['name_ar'] ?? $service['service_name'] ?? ''); ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">الحالة:</div>
                                    <div class="info-value">
                                        <?php if (($service['is_active'] ?? 1) == 1): ?>
                                            <span class="badge bg-success status-badge">نشط</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary status-badge">غير نشط</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (!empty($service['description_ar'] ?? $service['description'] ?? '')): ?>
                        <div class="info-item">
                            <div class="info-label">وصف الخدمة:</div>
                            <div class="info-value"><?php echo htmlspecialchars($service['description_ar'] ?? $service['description'] ?? ''); ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">تاريخ الإنشاء:</div>
                                    <div class="info-value"><?php echo date('Y-m-d H:i', strtotime($service['created_at'])); ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">عدد الأسئلة:</div>
                                    <div class="info-value"><?php echo count($questions); ?> سؤال</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Questions Section -->
                <?php if (!empty($questions)): ?>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-question-circle"></i>
                            الأسئلة المرتبطة بالخدمة
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($questions as $index => $question): ?>
                        <div class="question-card">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-2">
                                        <span class="badge bg-primary me-2"><?php echo $index + 1; ?></span>
                                        <?php echo htmlspecialchars($question['question_text_ar'] ?? $question['question_text'] ?? ''); ?>
                                    </h6>
                                    <?php if (!empty($question['description_ar'] ?? $question['description'] ?? '')): ?>
                                    <p class="text-muted mb-0">
                                        <?php echo htmlspecialchars($question['description_ar'] ?? $question['description'] ?? ''); ?>
                                    </p>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <?php if (($question['is_active'] ?? 1) == 1): ?>
                                        <span class="badge bg-success">نشط</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">غير نشط</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php else: ?>
                <div class="card">
                    <div class="card-body text-center text-muted">
                        <i class="fas fa-info-circle fa-2x mb-3"></i>
                        <h5>لا توجد أسئلة مرتبطة بهذه الخدمة</h5>
                        <p>يمكنك إضافة أسئلة من صفحة إدارة الأسئلة</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 