<?php
session_start();
require_once 'config/database.php';

// الحصول على الخدمات النشطة
$pdo = getDBConnection();
$stmt = $pdo->query("SELECT * FROM services WHERE is_active = 1 ORDER BY sort_order");
$services = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>اختبار النموذج الرئيسي - TIEC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
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
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="form-container p-5">
                    <!-- العنوان -->
                    <div class="text-center mb-5">
                        <h1 class="section-title">اختبار النموذج الرئيسي - TIEC</h1>
                        <p class="text-muted">اختبار الأسئلة الديناميكية</p>
                    </div>

                    <!-- رسائل النجاح/الخطأ -->
                    <div id="alert-container"></div>

                    <!-- نموذج التسجيل -->
                    <form id="registration-form" method="POST" action="process_registration.php" enctype="multipart/form-data">
                        <!-- المعلومات الشخصية -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h4 class="section-title">
                                    <i class="fas fa-user"></i> المعلومات الشخصية
                                </h4>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">الاسم الكامل *</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="national_id" class="form-label">الرقم القومي *</label>
                                <input type="text" class="form-control" id="national_id" name="national_id" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="governorate" class="form-label">المحافظة *</label>
                                <select class="form-select" id="governorate" name="governorate" required>
                                    <option value="">اختر المحافظة</option>
                                    <option value="القاهرة">القاهرة</option>
                                    <option value="الإسكندرية">الإسكندرية</option>
                                    <option value="الجيزة">الجيزة</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="gender" class="form-label">الجنس *</label>
                                <select class="form-select" id="gender" name="gender" required>
                                    <option value="">اختر الجنس</option>
                                    <option value="male">ذكر</option>
                                    <option value="female">أنثى</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="age" class="form-label">العمر *</label>
                                <input type="number" class="form-control" id="age" name="age" min="16" max="100" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">رقم الهاتف *</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">البريد الإلكتروني</label>
                                <input type="email" class="form-control" id="email" name="email">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="participant_type" class="form-label">نوع المشارك *</label>
                                <select class="form-select" id="participant_type" name="participant_type" required>
                                    <option value="">اختر نوع المشارك</option>
                                    <option value="student">طالب</option>
                                    <option value="employee">موظف</option>
                                    <option value="other">أخرى</option>
                                </select>
                            </div>
                        </div>

                        <!-- الخدمات والأسئلة -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h4 class="section-title">
                                    <i class="fas fa-cogs"></i> الخدمات المطلوبة
                                </h4>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label for="service_id" class="form-label">اختر الخدمة *</label>
                                <select class="form-select" id="service_id" name="service_id" required>
                                    <option value="">اختر الخدمة</option>
                                    <?php foreach ($services as $service): ?>
                                    <option value="<?= $service['id'] ?>" data-service-id="<?= $service['id'] ?>">
                                        <?= $service['name_ar'] ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-12" id="service-questions-container">
                                <!-- الأسئلة ستظهر هنا ديناميكياً -->
                            </div>
                        </div>

                        <!-- زر التسجيل -->
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane"></i> 
                                <span>تسجيل</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // تحميل الأسئلة عند اختيار الخدمة
        document.getElementById('service_id').addEventListener('change', function() {
            const serviceId = this.value;
            const questionsContainer = document.getElementById('service-questions-container');
            
            if (serviceId) {
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
                        questionsContainer.innerHTML = '<div class="alert alert-warning">لا توجد أسئلة لهذه الخدمة</div>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    questionsContainer.innerHTML = '<div class="alert alert-danger">خطأ في تحميل الأسئلة</div>';
                });
            } else {
                questionsContainer.innerHTML = '';
            }
        });

        // معالجة النموذج
        document.getElementById('registration-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('process_registration.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const alertContainer = document.getElementById('alert-container');
                
                if (data.success) {
                    alertContainer.innerHTML = `
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> تم التسجيل بنجاح!
                        </div>
                    `;
                    
                    // إعادة تعيين النموذج
                    this.reset();
                    document.getElementById('service-questions-container').innerHTML = '';
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