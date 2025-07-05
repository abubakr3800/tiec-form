<?php
session_start();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>تسجيل مسؤول أو مدرب / Register Admin or Trainer</title>
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
        <div class="col-lg-7">
            <div class="card shadow">
                <div class="card-header bg-primary text-white text-center">
                    <h4>تسجيل مسؤول أو مدرب / Register Admin or Trainer</h4>
                </div>
                <div class="card-body">
                    <form method="post" autocomplete="off" action="save_register.php">
                        <div class="mb-3">
                            <label class="form-label required">الدور / Role</label>
                            <select name="role" id="role" class="form-select" required onchange="toggleTrainerFields()">
                                <option value="admin">مسؤول / Admin</option>
                                <option value="trainer">مدرب / Trainer</option>
                            </select>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label required">اسم المستخدم / Username</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">البريد الإلكتروني / Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">الاسم بالعربية / Full Name (AR)</label>
                                <input type="text" name="full_name_ar" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">الاسم بالإنجليزية / Full Name (EN)</label>
                                <input type="text" name="full_name_en" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">رقم الهاتف / Phone</label>
                                <input type="text" name="phone" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">كلمة المرور / Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">تأكيد كلمة المرور / Confirm Password</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                        </div>
                        <div id="trainerFields" style="display:none;">
                            <hr>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label required">التخصص / Specialty</label>
                                    <input type="text" name="specialty" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label required">الجامعة / University</label>
                                    <input type="text" name="university" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label required">سنوات الخبرة / Years of Experience</label>
                                    <input type="number" name="experience_years" class="form-control" min="0">
                                </div>
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
<script>
function toggleTrainerFields() {
    var role = document.getElementById('role').value;
    document.getElementById('trainerFields').style.display = (role === 'trainer') ? '' : 'none';
}
document.addEventListener('DOMContentLoaded', function() {
    toggleTrainerFields();
});
</script>
</body>
</html> 