<?php
session_start();
require_once '../config/database.php';

// إذا كان المستخدم مسجل دخول بالفعل
if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل دخول المشرفين - TIEC</title>
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
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        .form-control:focus {
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
        .alert {
            border-radius: 10px;
            border: none;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-5">
                <div class="login-container p-5">
                    <!-- العنوان -->
                    <div class="text-center mb-5">
                        <h1 class="h3 mb-3">
                            <i class="fas fa-user-shield text-primary"></i>
                            تسجيل دخول المشرفين
                        </h1>
                        <p class="text-muted">أدخل بياناتك للوصول لوحة التحكم</p>
                    </div>

                    <!-- رسائل الخطأ -->
                    <div id="alert-container">
                        <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            <?= htmlspecialchars($_GET['error']) ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- نموذج تسجيل الدخول -->
                    <form id="login-form" method="POST" action="process_login.php">
                        <div class="mb-3">
                            <label for="username" class="form-label">اسم المستخدم</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-user"></i>
                                </span>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="password" class="form-label">كلمة المرور</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="user_type" class="form-label">نوع المستخدم</label>
                            <select class="form-select" id="user_type" name="user_type" required>
                                <option value="">اختر نوع المستخدم</option>
                                <option value="admin">مشرف</option>
                                <option value="trainer">مدرب</option>
                            </select>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt"></i> تسجيل الدخول
                            </button>
                        </div>
                    </form>

                    <!-- روابط إضافية -->
                    <div class="text-center mt-4">
                        <a href="../admin_register.php" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-user-plus"></i> تسجيل حساب جديد
                        </a>
                        
                        <a href="../index.php" class="btn btn-outline-secondary btn-sm ms-2">
                            <i class="fas fa-home"></i> العودة للصفحة الرئيسية
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // معالجة النموذج
        document.getElementById('login-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('process_login.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const alertContainer = document.getElementById('alert-container');
                
                if (data.success) {
                    alertContainer.innerHTML = `
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> تم تسجيل الدخول بنجاح!
                        </div>
                    `;
                    
                    // إعادة توجيه للوحة التحكم
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 1000);
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