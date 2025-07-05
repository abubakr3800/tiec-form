<?php
// Start session for cache (if needed)
session_start();
require_once __DIR__ . '/../cache/db.php'; 
if (isset($_COOKIE['user_token']) && !empty($_COOKIE['user_token'])) {
    header('Location: success.php?token=' . urlencode($_COOKIE['user_token']));
    exit;
} 
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registration Form / استمارة التسجيل</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .form-label { font-weight: 500; }
        .required:after { content: "*"; color: red; margin-right: 2px; }
    </style>
</head>
<body>
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white text-center">
                    <h4>Registration Form / استمارة التسجيل</h4>
                </div>
                <div class="card-body">
                    <div id="alert-box"></div>
                    <form id="regForm" method="post" action="save_user.php">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label required">Full name in Arabic / الاسم (بالغة العربية)</label>
                                <input type="text" name="full_name_ar" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">Full name in English / الاسم (بالغة الأنجليزية)</label>
                                <input type="text" name="full_name_en" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">National ID / الرقم القومي</label>
                                <input type="text" name="national_id" class="form-control" required maxlength="20">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">Governorate / المحافظة</label>
                                <input type="text" name="governorate" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">Gender / النوع</label>
                                <select name="gender" class="form-select" required>
                                    <option value="">اختر / Select</option>
                                    <option value="Male">ذكر / Male</option>
                                    <option value="Female">أنثى / Female</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">Age / السن</label>
                                <input type="number" name="age" class="form-control" required min="10" max="100">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">Phone Number / رقم الهاتف</label>
                                <input type="text" name="phone" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">WhatsApp Number / (رقم الواتس اب)</label>
                                <input type="text" name="whatsapp" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">Participant type / نوع المشارك</label>
                                <select name="participant_type" class="form-select" required>
                                    <option value="">اختر / Select</option>
                                    <option>School student</option>
                                    <option>University student</option>
                                    <option>Graduate</option>
                                    <option>Academic</option>
                                    <option>Entrepreneur</option>
                                    <option>Start-Up</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">Email / البريد الإلكتروني</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">University / الجامعة</label>
                                <select name="university" class="form-select" required>
                                    <option value="">اختر / Select</option>
                                    <option>جامعة سوهاج</option>
                                    <option>جامعة ميريت</option>
                                    <option>معهد عالى</option>
                                    <option>معهد متوسط</option>
                                    <option>أخرى</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">Current or last educational stage / المرحلة التعليمية الحالية او اخر مستوى تعليمي</label>
                                <select name="education_stage" class="form-select" required>
                                    <option value="">اختر / Select</option>
                                    <option>جامعى</option>
                                    <option>ثانوي</option>
                                    <option>دبلوم</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">Faculty / الكلية</label>
                                <select name="faculty" class="form-select" required>
                                    <option value="">اختر / Select</option>
                                    <option>كلية التجارة</option>
                                    <option>كلية الحقوق</option>
                                    <option>كلية الزراعة</option>
                                    <option>كلية الألسن</option>
                                    <option>كلية الاداب</option>
                                    <option>كلية التربية</option>
                                    <option>كلية التربية الرياضية</option>
                                    <option>كلية التربية التوعية</option>
                                    <option>كلية الهندسة</option>
                                    <option>كلية الحاسبات والذكاء الأصطناعي</option>
                                    <option>كلية العلوم</option>
                                    <option>كلية الاسنان</option>
                                    <option>كلية الطب البيطري</option>
                                    <option>كلية التمريض</option>
                                    <option>كلية تكنولوجيا تعليم</option>
                                    <option>كلية الطب البشري</option>
                                    <option>كلية الصيدلة</option>
                                    <option>معهد التمريض</option>
                                    <option>المعهد الهندسي بالكوثر</option>
                                    <option>معهد التجارة الألكترونية بالكوثر</option>
                                    <option>المعهد العالي للعلوم الإدارية</option>
                                    <option>أخرى</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Work employer if you are graduate / جهة العمل إذا كنت خريجًا</label>
                                <input type="text" name="work_employer" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">Service you want for support / الخدمة التي ترغب بها لدعمك</label>
                                <select name="support_service" class="form-select" required>
                                    <option value="">اختر / Select</option>
                                    <option>تدريب</option>
                                    <option>استشارة</option>
                                    <option>التاكد من موعد التدريب</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">التدريب المستهدف / Target Training </label>
                                <select name="support_service" class="form-select" required>
                                    <option value="">اختر / Select</option>
                                    <option>Career Camp 6-7 to 8-7</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label required">Confirmation about training date / يتم ارسال ايميل مسبق بالتاكيد على موعد التدريب</label>
                                <select name="training_confirmation" class="form-select" required>
                                    <option value="">اختر / Select</option>
                                    <option>28 الى 3 يوليو</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-4 text-center">
                            <button type="submit" class="btn btn-success px-5">تسجيل / Register</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(function() {
    $('#regForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var national_id = form.find('[name="national_id"]').val();
        var email = form.find('[name="email"]').val();
        $.post('check_user.php', { national_id: national_id, email: email }, function(data) {
            if (data.exists) {
                $('#alert-box').html('<div class="alert alert-danger text-center">المستخدم مسجل بالفعل أو البريد الإلكتروني مستخدم بالفعل.<br>User already registered or email in use.</div>');
            } else {
                form.off('submit');
                form.submit();
            }
        }, 'json');
    });
});

// // var token = '<?= htmlspecialchars($_COOKIE['PHPSESSID'] ?? '') ?>';

// if (token) {
//     window.location.href = 'success.php?token=' + token;
// }

// if (document.cookie != null) {
//     window.history.back();
// }
</script>
</body>
</html> 