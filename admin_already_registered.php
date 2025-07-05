<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تم التسجيل مسبقاً - TIEC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .message-container {
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
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="message-container p-5 text-center">
                    <div class="mb-4">
                        <i class="fas fa-user-shield text-primary" style="font-size: 4rem;"></i>
                    </div>
                    
                    <h2 class="mb-4">تم التسجيل مسبقاً</h2>
                    
                    <p class="text-muted mb-4">
                        يبدو أنك قمت بتسجيل حساب مشرف أو مدرب مسبقاً في نظامنا. 
                        لا يمكن التسجيل مرة أخرى من نفس المتصفح.
                    </p>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-lightbulb"></i>
                        إذا كنت ترغب في التسجيل مرة أخرى، يرجى:
                        <ul class="mt-2 mb-0 text-start">
                            <li>مسح بيانات التصفح (Cookies)</li>
                            <li>أو استخدام متصفح آخر</li>
                            <li>أو الاتصال بنا للمساعدة</li>
                        </ul>
                    </div>
                    
                    <div class="mt-4">
                        <a href="admin/login.php" class="btn btn-primary me-2">
                            <i class="fas fa-sign-in-alt"></i> تسجيل الدخول
                        </a>
                        
                        <button class="btn btn-outline-primary" onclick="clearCookies()">
                            <i class="fas fa-cookie-bite"></i> مسح الكوكيز
                        </button>
                    </div>
                    
                    <div class="mt-3">
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="fas fa-home"></i> العودة للصفحة الرئيسية
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function clearCookies() {
            // مسح الكوكيز
            document.cookie.split(";").forEach(function(c) { 
                document.cookie = c.replace(/^ +/, "").replace(/=.*/, "=;expires=" + new Date().toUTCString() + ";path=/"); 
            });
            
            // إعادة توجيه لصفحة تسجيل المشرفين
            window.location.href = 'admin_register.php';
        }
    </script>
</body>
</html> 