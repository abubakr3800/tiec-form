# نظام TIEC - دليل سريع

## 🚀 ما هو النظام؟

نظام TIEC عبارة عن منصة شاملة لإدارة الخدمات التدريبية والاستشارية في Technology Innovation and Entrepreneurship Center (TIEC).

## 🎯 الميزات الرئيسية

- ✅ **نظام تسجيل ذكي** مع نموذج ثنائي اللغة
- ✅ **4 أنواع خدمات**: تدريبات، استشارات، فاب لاب، مساحة عمل
- ✅ **QR Scanner** لتسجيل الحضور
- ✅ **نظام Token** للتسجيل السريع
- ✅ **لوحة تحكم متقدمة** للمديرين
- ✅ **تقارير وإحصائيات** شاملة

## 🛠️ التثبيت السريع

### 1. إعداد قاعدة البيانات
```bash
php create_complete_database.php
```

### 2. تعديل إعدادات قاعدة البيانات
```php
// config/database.php
$host = '127.0.0.1';
$db   = 'tiec_form';
$user = 'root';
$pass = '';
```

### 3. إنشاء مجلد uploads
```bash
mkdir uploads
chmod 755 uploads
```

## 🎯 الروابط المهمة

### للمشاركين
- **التسجيل الجديد**: `http://localhost/Tiec/index.php`
- **تسجيل الدخول بالـ Token**: `http://localhost/Tiec/token_login.php`
- **عرض التسجيلات**: `http://localhost/Tiec/view_my_registrations.php`
- **تسجيل الحضور**: `http://localhost/Tiec/attendance_scanner.php`

### للمديرين
- **لوحة التحكم**: `http://localhost/Tiec/admin/index.php`
- **إدارة الخدمات**: `http://localhost/Tiec/admin/services_manager.php`
- **إدارة المدربين**: `http://localhost/Tiec/admin/trainers.php`
- **إدارة التدريبات**: `http://localhost/Tiec/admin/trainings_manager.php`
- **إدارة الحضور**: `http://localhost/Tiec/admin/attendance_manager.php`

## 🗺️ مسارات التنقل السريعة

### مسار المشارك الجديد:
```
index.php → process_registration.php → (QR Code) → view_my_registrations.php
```

### مسار المشارك المسجل:
```
token_login.php → (Token) → view_my_registrations.php → attendance_scanner.php
```

### مسار المدير:
```
admin/login.php → admin/index.php → admin/services_manager.php → admin/trainers.php → admin/trainings_manager.php → admin/attendance_manager.php
```

### مسار تسجيل الحضور:
```
attendance_scanner.php → (QR/National ID) → (Attendance Confirmation)
```

## 📊 الجداول الرئيسية

- `participants` - المشاركين
- `services` - الخدمات
- `trainers` - المدربين
- `trainings` - التدريبات
- `registrations` - التسجيلات
- `attendance` - الحضور
- `admins` - المشرفين

## 🔧 استكشاف الأخطاء

### مشاكل شائعة
1. **خطأ قاعدة البيانات**: شغل `php create_complete_database.php`
2. **خطأ QR Scanner**: تأكد من HTTPS أو استخدم localhost
3. **خطأ رفع ملفات**: تأكد من صلاحيات مجلد uploads/

## 📞 الدعم

- **ملف السيناريو**: `system_scenario.txt`
- **دليل النظام الجديد**: `README_NEW_SYSTEM.md`
- **دليل مفصل**: `README.md`
- **دليل إنجليزي**: `README_EN.md`

---

**الإصدار**: 2.0 | **الحالة**: مستقر | **آخر تحديث**: ديسمبر 2024 