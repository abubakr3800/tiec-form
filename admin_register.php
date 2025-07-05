<?php
session_start();
require_once 'config/database.php';

// التحقق من وجود الكوكي لمنع التسجيل المكرر
if (isset($_COOKIE['admin_registered'])) {
    header('Location: admin_already_registered.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل المشرفين والمدربين - TIEC</title>
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
        .alert {
            border-radius: 10px;
            border: none;
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
                        <h1 class="section-title">
                            <i class="fas fa-user-shield"></i> تسجيل المشرفين والمدربين
                        </h1>
                        <p class="text-muted">سجل الآن كمدير أو مدرب في النظام</p>
                    </div>

                    <!-- رسائل النجاح/الخطأ -->
                    <div id="alert-container"></div>

                    <!-- نموذج التسجيل -->
                    <form id="admin-registration-form" method="POST" action="process_admin_registration.php">
                        <!-- نوع المستخدم -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h4 class="section-title">
                                    <i class="fas fa-users"></i> نوع المستخدم
                                </h4>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="user_type" class="form-label">نوع المستخدم *</label>
                                <select class="form-select" id="user_type" name="user_type" required>
                                    <option value="">اختر نوع المستخدم</option>
                                    <option value="admin">مشرف</option>
                                    <option value="trainer">مدرب</option>
                                </select>
                            </div>
                        </div>

                        <!-- معلومات الحساب -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h4 class="section-title">
                                    <i class="fas fa-key"></i> معلومات الحساب
                                </h4>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label">اسم المستخدم *</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">كلمة المرور *</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">تأكيد كلمة المرور *</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>

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
                                <label for="email" class="form-label">البريد الإلكتروني *</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            
                            <div class="col-md-6 mb-3" id="phone-field">
                                <label for="phone" class="form-label">رقم الهاتف</label>
                                <input type="tel" class="form-control" id="phone" name="phone">
                            </div>
                            
                            <div class="col-md-6 mb-3" id="specialization-field">
                                <label for="specialization" class="form-label">التخصص</label>
                                <input type="text" class="form-control" id="specialization" name="specialization">
                            </div>
                        </div>

                        <!-- زر التسجيل -->
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-user-plus"></i> تسجيل
                            </button>
                        </div>
                    </form>

                    <!-- رابط العودة -->
                    <div class="text-center mt-4">
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> العودة للتسجيل العادي
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // إدارة الحقول حسب نوع المستخدم
        document.getElementById('user_type').addEventListener('change', function() {
            const userType = this.value;
            const phoneField = document.getElementById('phone-field');
            const specializationField = document.getElementById('specialization-field');
            
            // إخفاء جميع الحقول أولاً
            phoneField.style.display = 'none';
            specializationField.style.display = 'none';
            
            // إظهار الحقول المناسبة
            if (userType === 'trainer') {
                phoneField.style.display = 'block';
                specializationField.style.display = 'block';
            }
        });

        // التحقق من تطابق كلمات المرور
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (password !== confirmPassword) {
                this.setCustomValidity('كلمات المرور غير متطابقة');
            } else {
                this.setCustomValidity('');
            }
        });

        // معالجة النموذج
        document.getElementById('admin-registration-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('process_admin_registration.php', {
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
                    
                    // إعادة توجيه بعد ثانيتين
                    setTimeout(() => {
                        window.location.href = 'admin/login.php';
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