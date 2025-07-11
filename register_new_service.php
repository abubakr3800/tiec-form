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
    
    // جلب الخدمات النشطة
    $stmt = $pdo->query("SELECT * FROM services WHERE is_active = 1 ORDER BY sort_order");
    $services = $stmt->fetchAll();
    
    // جلب الخدمات المسجل فيها مسبقاً
    $stmt = $pdo->prepare("SELECT service_id FROM registrations WHERE participant_id = ?");
    $stmt->execute([$participant_id]);
    $registered_services = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
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
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل في خدمة جديدة - TIEC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .form-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
        }

        .form-control,
        .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .section-title {
            color: #667eea;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .service-card {
            border: 2px solid #e9ecef;
            border-radius: 15px;
            transition: all 0.3s ease;
            cursor: pointer;
            margin-bottom: 15px;
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

        .service-card.registered {
            border-color: #28a745;
            background-color: #f8fff9;
            opacity: 0.7;
        }

        .alert {
            border-radius: 10px;
            border: none;
        }

        .participant-info {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .hidden {
            display: none;
        }
    </style>
</head>

<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="form-container p-5">
                    <!-- العنوان -->
                    <div class="text-center mb-5">
                        <h1 class="section-title">
                            <i class="fas fa-plus-circle"></i> تسجيل في خدمة جديدة
                        </h1>
                        <p class="text-muted">اختر الخدمة التي تريد التسجيل فيها</p>
                    </div>

                    <!-- معلومات المشارك -->
                    <div class="participant-info">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5 class="mb-2">
                                    <i class="fas fa-user-check text-success"></i> 
                                    مرحباً <?= htmlspecialchars($participant['name']) ?>
                                </h5>
                                <p class="mb-0 text-muted">
                                    <i class="fas fa-id-card"></i> 
                                    <?= htmlspecialchars($participant['national_id']) ?> | 
                                    <i class="fas fa-map-marker-alt"></i> 
                                    <?= htmlspecialchars($participant['governorate']) ?>
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                <a href="dashboard.php" class="btn btn-primary me-2">
                                    <i class="fas fa-tachometer-alt"></i> لوحة التحكم
                                </a>
                                <a href="participant_profile.php" class="btn btn-outline-primary me-2">
                                    <i class="fas fa-user"></i> الملف الشخصي
                                </a>
                                <a href="view_my_registrations.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-list"></i> تسجيلاتي
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- رسائل النجاح/الخطأ -->
                    <div id="alert-container"></div>

                    <!-- الخدمات المتاحة -->
                    <div class="mb-4">
                        <h4 class="section-title">
                            <i class="fas fa-list"></i> الخدمات المتاحة
                        </h4>
                        
                        <?php if (empty($services)): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                لا توجد خدمات متاحة حالياً
                            </div>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($services as $service): ?>
                                    <?php 
                                    $isRegistered = in_array($service['id'], $registered_services);
                                    $cardClass = $isRegistered ? 'service-card registered' : 'service-card';
                                    ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="<?= $cardClass ?>" 
                                             onclick="<?= $isRegistered ? '' : 'selectService(' . $service['id'] . ')' ?>">
                                            <div class="p-4">
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <h5 class="mb-2"><?= htmlspecialchars($service['name_ar']) ?></h5>
                                                    <span class="badge bg-<?= $service['service_type'] === 'training' ? 'primary' : 
                                                        ($service['service_type'] === 'mentoring' ? 'info' : 
                                                        ($service['service_type'] === 'fablab' ? 'warning' : 'success')) ?>">
                                                        <?= $service_types[$service['service_type']] ?>
                                                    </span>
                                                </div>
                                                
                                                <p class="text-muted mb-3"><?= htmlspecialchars($service['description_ar']) ?></p>
                                                
                                                <?php if ($isRegistered): ?>
                                                    <div class="text-center">
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-check"></i> مسجل مسبقاً
                                                        </span>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="text-center">
                                                        <button class="btn btn-primary btn-sm" onclick="selectService(<?= $service['id'] ?>)">
                                                            <i class="fas fa-plus"></i> تسجيل
                                                        </button>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                                            <!-- نموذج التسجيل -->
                        <form id="registration-form" method="POST" action="process_additional_registration.php" enctype="multipart/form-data" class="hidden">
                            <input type="hidden" id="selected_service_id" name="service_id">

                            <!-- الأسئلة الديناميكية -->
                            <div id="service-questions-container"></div>

                        <!-- أزرار الإجراءات -->
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-check"></i> تأكيد التسجيل
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="cancelRegistration()">
                                <i class="fas fa-times"></i> إلغاء
                            </button>
                        </div>
                    </form>
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
            event.currentTarget.closest('.service-card').classList.add('selected');
            
            // تعيين معرف الخدمة
            document.getElementById('selected_service_id').value = serviceId;
            
            // إظهار النموذج
            document.getElementById('registration-form').classList.remove('hidden');
            
            // تحميل الأسئلة
            loadServiceQuestions(serviceId);
            
            // التمرير للنموذج
            document.getElementById('registration-form').scrollIntoView({ 
                behavior: 'smooth' 
            });
        }

        function cancelRegistration() {
            // إخفاء النموذج
            document.getElementById('registration-form').classList.add('hidden');
            
            // إزالة التحديد من البطاقات
            document.querySelectorAll('.service-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // مسح الأسئلة
            document.getElementById('service-questions-container').innerHTML = '';
        }

        function loadServiceQuestions(serviceId) {
            const questionsContainer = document.getElementById('service-questions-container');
            
            // إرسال طلب AJAX لجلب الأسئلة
            fetch('get_service_questions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'service_id=' + serviceId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    questionsContainer.innerHTML = data.html;
                } else {
                    questionsContainer.innerHTML = '<div class="alert alert-info">لا توجد أسئلة إضافية لهذه الخدمة</div>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                questionsContainer.innerHTML = '<div class="alert alert-danger">خطأ في تحميل الأسئلة</div>';
            });
        }

        // معالجة النموذج
        document.getElementById('registration-form').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('process_additional_registration.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const alertContainer = document.getElementById('alert-container');

                if (data.success) {
                    alertContainer.innerHTML = `
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> تم التسجيل في ${data.service_name} بنجاح!
                        </div>
                    `;

                    // إعادة توجيه بعد ثانيتين
                    setTimeout(() => {
                        window.location.href = 'view_my_registrations.php';
                    }, 2000);
                } else {
                    alertContainer.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i> ${data.message}
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('alert-container').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> خطأ في الاتصال بالخادم
                    </div>
                `;
            });
        });
    </script>
</body>
</html> 