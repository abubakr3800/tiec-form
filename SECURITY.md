# سياسة الأمان

نحن نأخذ أمان نظام التسجيل - TIEC على محمل الجد. إذا اكتشفت ثغرة أمنية، يرجى إبلاغنا فوراً.

## الإبلاغ عن الثغرات الأمنية

### الطريقة المفضلة
- إرسال بريد إلكتروني إلى: security@tiec.com
- عنوان الموضوع: `[SECURITY] وصف مختصر للثغرة`

### معلومات مطلوبة
- وصف مفصل للثغرة
- خطوات لتكرار المشكلة
- التأثير المحتمل
- اقتراحات للإصلاح (اختياري)

### ما نتعهد به
- الرد خلال 48 ساعة
- التحقيق في الثغرة المبلغ عنها
- إصلاح الثغرات الحرجة في أقرب وقت ممكن
- إعلام المبلغ عن حالة الإصلاح

## إرشادات الأمان للمطورين

### 1. التحقق من المدخلات
```php
// دائماً تحقق من المدخلات
$user_input = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
if (!$user_input) {
    // معالجة الخطأ
}
```

### 2. استخدام Prepared Statements
```php
// استخدم Prepared Statements لمنع SQL Injection
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
```

### 3. تشفير كلمات المرور
```php
// استخدم password_hash() دائماً
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
```

### 4. التحقق من الجلسات
```php
// تحقق من صحة الجلسة
if (!isset($_SESSION['user_id']) || !is_numeric($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
```

### 5. حماية من XSS
```php
// استخدم htmlspecialchars() للمخرجات
echo htmlspecialchars($user_data, ENT_QUOTES, 'UTF-8');
```

## إعدادات الأمان الموصى بها

### 1. إعدادات PHP
```ini
; php.ini
display_errors = Off
log_errors = On
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT
session.cookie_httponly = 1
session.cookie_secure = 1
session.use_strict_mode = 1
```

### 2. إعدادات Apache
```apache
# .htaccess
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
```

### 3. إعدادات قاعدة البيانات
```sql
-- إنشاء مستخدم محدود الصلاحيات
CREATE USER 'tiec_user'@'localhost' IDENTIFIED BY 'strong_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON tiec_form.* TO 'tiec_user'@'localhost';
FLUSH PRIVILEGES;
```

## فحص الأمان

### أدوات موصى بها
- OWASP ZAP للفحص التلقائي
- PHP Security Checker لفحص التبعيات
- SonarQube لتحليل الكود

### فحص دوري
- فحص التبعيات أسبوعياً
- فحص الثغرات الشائعة شهرياً
- مراجعة شاملة ربع سنوياً

## تحديثات الأمان

### إصدارات الأمان
- الإصدارات الحرجة: خلال 24 ساعة
- الإصدارات المهمة: خلال أسبوع
- الإصدارات العادية: حسب الجدول الزمني

### إشعارات الأمان
- إشعار فوري للثغرات الحرجة
- إشعار أسبوعي للتحديثات المهمة
- إشعار شهري للتحديثات العادية

## أفضل الممارسات

### 1. التطوير
- استخدم HTTPS دائماً
- تحقق من جميع المدخلات
- استخدم مكتبات محدثة
- اكتب اختبارات أمان

### 2. النشر
- استخدم بيئة منفصلة للاختبار
- اختبر التحديثات قبل النشر
- احتفظ بنسخ احتياطية
- راقب السجلات

### 3. الصيانة
- حدث النظام بانتظام
- راقب السجلات للأحداث المشبوهة
- احتفظ بتوثيق الأمان
- درب الفريق على الأمان

## التواصل

- للثغرات الأمنية: security@tiec.com
- للأسئلة العامة: info@tiec.com
- للدعم التقني: support@tiec.com

---

**ملاحظة:** هذه السياسة قابلة للتحديث حسب الحاجة. يرجى مراجعتها بانتظام. 