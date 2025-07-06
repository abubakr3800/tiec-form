<?php
session_start();
require_once 'config/database.php';

// التحقق من وجود الكوكي لمنع التسجيل المكرر
if (isset($_COOKIE['registered_user'])) {
    header('Location: already_registered.php');
    exit();
}

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
               <title>نظام التسجيل - TIEC</title>
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

               .language-switch {
                              position: fixed;
                              top: 20px;
                              right: 20px;
                              z-index: 1000;
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

               .qr-code-container {
                              text-align: center;
                              margin-top: 20px;
                              padding: 20px;
                              background: #f8f9fa;
                              border-radius: 10px;
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
               <!-- زر تبديل اللغة -->
               <div class="language-switch">
                              <button class="btn btn-outline-primary" onclick="toggleLanguage()">
                                             <i class="fas fa-language"></i>
                                             <span id="lang-text">English</span>
                              </button>
               </div>

               <div class="container py-5">
                              <div class="row justify-content-center">
                                             <div class="col-lg-8">
                                                            <div class="form-container p-5">
                                                                           <!-- العنوان -->
                                                                           <div class="text-center mb-5">
                                                                                          <h1 class="section-title"
                                                                                                         id="main-title">
                                                                                                         نظام التسجيل -
                                                                                                         TIEC</h1>
                                                                                          <p class="text-muted"
                                                                                                         id="subtitle">
                                                                                                         سجل الآن للحصول
                                                                                                         على خدماتنا
                                                                                                         المتميزة</p>
                                                                           </div>

                                                                           <!-- رسائل النجاح/الخطأ -->
                                                                           <div id="alert-container"></div>

                                                                           <!-- نموذج التسجيل -->
                                                                           <form id="registration-form" method="POST"
                                                                                          action="process_registration.php"
                                                                                          enctype="multipart/form-data">
                                                                                          <!-- المعلومات الشخصية -->
                                                                                          <div class="row mb-4">
                                                                                                         <div
                                                                                                                        class="col-12">
                                                                                                                        <h4 class="section-title"
                                                                                                                                       id="personal-info-title">
                                                                                                                                       <i
                                                                                                                                                      class="fas fa-user"></i>
                                                                                                                                       المعلومات
                                                                                                                                       الشخصية
                                                                                                                        </h4>
                                                                                                         </div>

                                                                                                         <div
                                                                                                                        class="col-md-6 mb-3">
                                                                                                                        <label for="name"
                                                                                                                                       class="form-label"
                                                                                                                                       id="name-label">الاسم
                                                                                                                                       الكامل
                                                                                                                                       *</label>
                                                                                                                        <input type="text"
                                                                                                                                       class="form-control"
                                                                                                                                       id="name"
                                                                                                                                       name="name"
                                                                                                                                       required>
                                                                                                         </div>

                                                                                                         <div
                                                                                                                        class="col-md-6 mb-3">
                                                                                                                        <label for="national_id"
                                                                                                                                       class="form-label"
                                                                                                                                       id="national-id-label">الرقم
                                                                                                                                       القومي
                                                                                                                                       *</label>
                                                                                                                        <input type="text"
                                                                                                                                       class="form-control"
                                                                                                                                       id="national_id"
                                                                                                                                       name="national_id"
                                                                                                                                       required>
                                                                                                         </div>

                                                                                                         <div
                                                                                                                        class="col-md-6 mb-3">
                                                                                                                        <label for="governorate"
                                                                                                                                       class="form-label"
                                                                                                                                       id="governorate-label">المحافظة
                                                                                                                                       *</label>
                                                                                                                        <select class="form-select"
                                                                                                                                       id="governorate"
                                                                                                                                       name="governorate"
                                                                                                                                       required>
                                                                                                                                       <option value=""
                                                                                                                                                      id="select-governorate">
                                                                                                                                                      اختر
                                                                                                                                                      المحافظة
                                                                                                                                       </option>
                                                                                                                                       <option
                                                                                                                                                      value="القاهرة">
                                                                                                                                                      القاهرة
                                                                                                                                       </option>
                                                                                                                                       <option
                                                                                                                                                      value="الإسكندرية">
                                                                                                                                                      الإسكندرية
                                                                                                                                       </option>
                                                                                                                                       <option
                                                                                                                                                      value="الجيزة">
                                                                                                                                                      الجيزة
                                                                                                                                       </option>
                                                                                                                                       <option
                                                                                                                                                      value="الشرقية">
                                                                                                                                                      الشرقية
                                                                                                                                       </option>
                                                                                                                                       <option
                                                                                                                                                      value="الغربية">
                                                                                                                                                      الغربية
                                                                                                                                       </option>
                                                                                                                                       <option
                                                                                                                                                      value="المنوفية">
                                                                                                                                                      المنوفية
                                                                                                                                       </option>
                                                                                                                                       <option
                                                                                                                                                      value="القليوبية">
                                                                                                                                                      القليوبية
                                                                                                                                       </option>
                                                                                                                                       <option
                                                                                                                                                      value="البحيرة">
                                                                                                                                                      البحيرة
                                                                                                                                       </option>
                                                                                                                                       <option
                                                                                                                                                      value="كفر الشيخ">
                                                                                                                                                      كفر
                                                                                                                                                      الشيخ
                                                                                                                                       </option>
                                                                                                                                       <option
                                                                                                                                                      value="دمياط">
                                                                                                                                                      دمياط
                                                                                                                                       </option>
                                                                                                                                       <option
                                                                                                                                                      value="الدقهلية">
                                                                                                                                                      الدقهلية
                                                                                                                                       </option>
                                                                                                                                       <option
                                                                                                                                                      value="المنيا">
                                                                                                                                                      المنيا
                                                                                                                                       </option>
                                                                                                                                       <option
                                                                                                                                                      value="أسيوط">
                                                                                                                                                      أسيوط
                                                                                                                                       </option>
                                                                                                                                       <option
                                                                                                                                                      value="سوهاج">
                                                                                                                                                      سوهاج
                                                                                                                                       </option>
                                                                                                                                       <option
                                                                                                                                                      value="قنا">
                                                                                                                                                      قنا
                                                                                                                                       </option>
                                                                                                                                       <option
                                                                                                                                                      value="الأقصر">
                                                                                                                                                      الأقصر
                                                                                                                                       </option>
                                                                                                                                       <option
                                                                                                                                                      value="أسوان">
                                                                                                                                                      أسوان
                                                                                                                                       </option>
                                                                                                                                       <option
                                                                                                                                                      value="بني سويف">
                                                                                                                                                      بني
                                                                                                                                                      سويف
                                                                                                                                       </option>
                                                                                                                                       <option
                                                                                                                                                      value="الفيوم">
                                                                                                                                                      الفيوم
                                                                                                                                       </option>
                                                                                                                                       <option
                                                                                                                                                      value="مطروح">
                                                                                                                                                      مطروح
                                                                                                                                       </option>
                                                                                                                                       <option
                                                                                                                                                      value="شمال سيناء">
                                                                                                                                                      شمال
                                                                                                                                                      سيناء
                                                                                                                                       </option>
                                                                                                                                       <option
                                                                                                                                                      value="جنوب سيناء">
                                                                                                                                                      جنوب
                                                                                                                                                      سيناء
                                                                                                                                       </option>
                                                                                                                                       <option
                                                                                                                                                      value="البحر الأحمر">
                                                                                                                                                      البحر
                                                                                                                                                      الأحمر
                                                                                                                                       </option>
                                                                                                                                       <option
                                                                                                                                                      value="الوادي الجديد">
                                                                                                                                                      الوادي
                                                                                                                                                      الجديد
                                                                                                                                       </option>
                                                                                                                        </select>
                                                                                                         </div>

                                                                                                         <div
                                                                                                                        class="col-md-6 mb-3">
                                                                                                                        <label for="gender"
                                                                                                                                       class="form-label"
                                                                                                                                       id="gender-label">الجنس
                                                                                                                                       *</label>
                                                                                                                        <select class="form-select"
                                                                                                                                       id="gender"
                                                                                                                                       name="gender"
                                                                                                                                       required>
                                                                                                                                       <option value=""
                                                                                                                                                      id="select-gender">
                                                                                                                                                      اختر
                                                                                                                                                      الجنس
                                                                                                                                       </option>
                                                                                                                                       <option value="male"
                                                                                                                                                      id="male-option">
                                                                                                                                                      ذكر
                                                                                                                                       </option>
                                                                                                                                       <option value="female"
                                                                                                                                                      id="female-option">
                                                                                                                                                      أنثى
                                                                                                                                       </option>
                                                                                                                        </select>
                                                                                                         </div>

                                                                                                         <div
                                                                                                                        class="col-md-6 mb-3">
                                                                                                                        <label for="age"
                                                                                                                                       class="form-label"
                                                                                                                                       id="age-label">العمر
                                                                                                                                       *</label>
                                                                                                                        <input type="number"
                                                                                                                                       class="form-control"
                                                                                                                                       id="age"
                                                                                                                                       name="age"
                                                                                                                                       min="16"
                                                                                                                                       max="100"
                                                                                                                                       required>
                                                                                                         </div>

                                                                                                         <div
                                                                                                                        class="col-md-6 mb-3">
                                                                                                                        <label for="phone"
                                                                                                                                       class="form-label"
                                                                                                                                       id="phone-label">رقم
                                                                                                                                       الهاتف
                                                                                                                                       *</label>
                                                                                                                        <input type="tel"
                                                                                                                                       class="form-control"
                                                                                                                                       id="phone"
                                                                                                                                       name="phone"
                                                                                                                                       required>
                                                                                                         </div>

                                                                                                         <div
                                                                                                                        class="col-md-6 mb-3">
                                                                                                                        <label for="whatsapp"
                                                                                                                                       class="form-label"
                                                                                                                                       id="whatsapp-label">رقم
                                                                                                                                       الواتساب</label>
                                                                                                                        <input type="tel"
                                                                                                                                       class="form-control"
                                                                                                                                       id="whatsapp"
                                                                                                                                       name="whatsapp">
                                                                                                         </div>

                                                                                                         <div
                                                                                                                        class="col-md-6 mb-3">
                                                                                                                        <label for="email"
                                                                                                                                       class="form-label"
                                                                                                                                       id="email-label">البريد
                                                                                                                                       الإلكتروني</label>
                                                                                                                        <input type="email"
                                                                                                                                       class="form-control"
                                                                                                                                       id="email"
                                                                                                                                       name="email">
                                                                                                         </div>
                                                                                          </div>

                                                                                          <!-- نوع المشارك والتعليم -->
                                                                                          <div class="row mb-4">
                                                                                                         <div
                                                                                                                        class="col-12">
                                                                                                                        <h4 class="section-title"
                                                                                                                                       id="education-title">
                                                                                                                                       <i
                                                                                                                                                      class="fas fa-graduation-cap"></i>
                                                                                                                                       المعلومات
                                                                                                                                       التعليمية
                                                                                                                                       والمهنية
                                                                                                                        </h4>
                                                                                                         </div>

                                                                                                         <div
                                                                                                                        class="col-md-6 mb-3">
                                                                                                                        <label for="participant_type"
                                                                                                                                       class="form-label"
                                                                                                                                       id="participant-type-label">نوع
                                                                                                                                       المشارك
                                                                                                                                       *</label>
                                                                                                                        <select class="form-select"
                                                                                                                                       id="participant_type"
                                                                                                                                       name="participant_type"
                                                                                                                                       required>
                                                                                                                                       <option value=""
                                                                                                                                                      id="select-participant-type">
                                                                                                                                                      اختر
                                                                                                                                                      نوع
                                                                                                                                                      المشارك
                                                                                                                                       </option>
                                                                                                                                       <option value="student"
                                                                                                                                                      id="student-option">
                                                                                                                                                      طالب
                                                                                                                                       </option>
                                                                                                                                       <option value="employee"
                                                                                                                                                      id="employee-option">
                                                                                                                                                      موظف
                                                                                                                                       </option>
                                                                                                                                       <option value="other"
                                                                                                                                                      id="other-option">
                                                                                                                                                      أخرى
                                                                                                                                       </option>
                                                                                                                        </select>
                                                                                                         </div>

                                                                                                         <div class="col-md-6 mb-3"
                                                                                                                        id="university-field">
                                                                                                                        <label for="university"
                                                                                                                                       class="form-label"
                                                                                                                                       id="university-label">الجامعة</label>
                                                                                                                        <input type="text"
                                                                                                                                       class="form-control"
                                                                                                                                       id="university"
                                                                                                                                       name="university">
                                                                                                         </div>

                                                                                                         <div class="col-md-6 mb-3"
                                                                                                                        id="education-stage-field">
                                                                                                                        <label for="education_stage"
                                                                                                                                       class="form-label"
                                                                                                                                       id="education-stage-label">المرحلة
                                                                                                                                       التعليمية</label>
                                                                                                                        <select class="form-select"
                                                                                                                                       id="education_stage"
                                                                                                                                       name="education_stage">
                                                                                                                                       <option value=""
                                                                                                                                                      id="select-education-stage">
                                                                                                                                                      اختر
                                                                                                                                                      المرحلة
                                                                                                                                       </option>
                                                                                                                                       <option
                                                                                                                                                      value="ثانوية عامة">
                                                                                                                                                      ثانوية
                                                                                                                                                      عامة
                                                                                                                                       </option>
                                                                                                                                       <option
                                                                                                                                                      value="سنة أولى">
                                                                                                                                                      سنة
                                                                                                                                                      أولى
                                                                                                                                       </option>
                                                                                                                                       <option
                                                                                                                                                      value="سنة ثانية">
                                                                                                                                                      سنة
                                                                                                                                                      ثانية
                                                                                                                                       </option>
                                                                                                                                       <option
                                                                                                                                                      value="سنة ثالثة">
                                                                                                                                                      سنة
                                                                                                                                                      ثالثة
                                                                                                                                       </option>
                                                                                                                                       <option
                                                                                                                                                      value="سنة رابعة">
                                                                                                                                                      سنة
                                                                                                                                                      رابعة
                                                                                                                                       </option>
                                                                                                                                       <option
                                                                                                                                                      value="ماجستير">
                                                                                                                                                      ماجستير
                                                                                                                                       </option>
                                                                                                                                       <option
                                                                                                                                                      value="دكتوراه">
                                                                                                                                                      دكتوراه
                                                                                                                                       </option>
                                                                                                                        </select>
                                                                                                         </div>

                                                                                                         <div class="col-md-6 mb-3"
                                                                                                                        id="faculty-field">
                                                                                                                        <label for="faculty"
                                                                                                                                       class="form-label"
                                                                                                                                       id="faculty-label">الكلية</label>
                                                                                                                        <input type="text"
                                                                                                                                       class="form-control"
                                                                                                                                       id="faculty"
                                                                                                                                       name="faculty">
                                                                                                         </div>

                                                                                                         <div class="col-md-6 mb-3"
                                                                                                                        id="work-employer-field">
                                                                                                                        <label for="work_employer"
                                                                                                                                       class="form-label"
                                                                                                                                       id="work-employer-label">جهة
                                                                                                                                       العمل</label>
                                                                                                                        <input type="text"
                                                                                                                                       class="form-control"
                                                                                                                                       id="work_employer"
                                                                                                                                       name="work_employer">
                                                                                                         </div>
                                                                                          </div>

                                                                                          <!-- الخدمات والأسئلة -->
                                                                                          <div class="row mb-4">
                                                                                                         <div
                                                                                                                        class="col-12">
                                                                                                                        <h4 class="section-title"
                                                                                                                                       id="services-title">
                                                                                                                                       <i
                                                                                                                                                      class="fas fa-cogs"></i>
                                                                                                                                       الخدمات
                                                                                                                                       المطلوبة
                                                                                                                        </h4>
                                                                                                         </div>

                                                                                                         <div
                                                                                                                        class="col-12 mb-3">
                                                                                                                        <label for="service_id"
                                                                                                                                       class="form-label"
                                                                                                                                       id="service-label">اختر
                                                                                                                                       الخدمة
                                                                                                                                       *</label>
                                                                                                                        <select class="form-select"
                                                                                                                                       id="service_id"
                                                                                                                                       name="service_id"
                                                                                                                                       required>
                                                                                                                                       <option value=""
                                                                                                                                                      id="select-service">
                                                                                                                                                      اختر
                                                                                                                                                      الخدمة
                                                                                                                                       </option>
                                                                                                                                       <?php foreach ($services as $service): ?>
                                                                                                                                       <option value="<?= $service['id'] ?>"
                                                                                                                                                      data-service-id="<?= $service['id'] ?>">
                                                                                                                                                      <?= $service['name_ar'] ?>
                                                                                                                                       </option>
                                                                                                                                       <?php endforeach; ?>
                                                                                                                        </select>
                                                                                                         </div>

                                                                                                         <div class="col-12"
                                                                                                                        id="service-questions-container">
                                                                                                                        <!-- الأسئلة ستظهر هنا ديناميكياً -->
                                                                                                         </div>
                                                                                          </div>

                                                                                          <!-- تأكيد التدريب -->
                                                                                          <div class="row mb-4">
                                                                                                         <div
                                                                                                                        class="col-12">
                                                                                                                        <h4 class="section-title"
                                                                                                                                       id="training-title">
                                                                                                                                       <i
                                                                                                                                                      class="fas fa-check-circle"></i>
                                                                                                                                       تأكيد
                                                                                                                                       التدريب
                                                                                                                        </h4>
                                                                                                         </div>

                                                                                                         <div
                                                                                                                        class="col-12 mb-3">
                                                                                                                        <div
                                                                                                                                       class="form-check">
                                                                                                                                       <input class="form-check-input"
                                                                                                                                                      type="checkbox"
                                                                                                                                                      id="training_confirmation"
                                                                                                                                                      name="training_confirmation"
                                                                                                                                                      value="1">
                                                                                                                                       <label class="form-check-label"
                                                                                                                                                      for="training_confirmation"
                                                                                                                                                      id="training-confirmation-label">
                                                                                                                                                      أوافق
                                                                                                                                                      على
                                                                                                                                                      المشاركة
                                                                                                                                                      في
                                                                                                                                                      التدريب
                                                                                                                                                      المطلوب
                                                                                                                                       </label>
                                                                                                                        </div>
                                                                                                         </div>
                                                                                          </div>

                                                                                          <!-- زر التسجيل -->
                                                                                          <div class="text-center">
                                                                                                         <button type="submit"
                                                                                                                        class="btn btn-primary btn-lg"
                                                                                                                        id="submit-btn">
                                                                                                                        <i
                                                                                                                                       class="fas fa-paper-plane"></i>
                                                                                                                        <span
                                                                                                                                       id="submit-text">تسجيل</span>
                                                                                                         </button>
                                                                                          </div>
                                                                           </form>

                                                                           <!-- رمز QR -->
                                                                           <div id="qr-container"
                                                                                          class="qr-code-container hidden">
                                                                                          <h5 id="qr-title">رمز QR
                                                                                                         للتسجيل</h5>
                                                                                          <div id="qr-code"></div>
                                                                                          <p class="text-muted mt-3"
                                                                                                         id="qr-description">
                                                                                                         احفظ هذا الرمز
                                                                                                         للوصول السريع
                                                                                                         لبياناتك</p>
                                                                           </div>
                                                            </div>
                                             </div>
                              </div>
               </div>

               <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
               <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
               <script>
               // متغيرات اللغة
               const translations = {
                              ar: {
                                             mainTitle: 'نظام التسجيل - TIEC',
                                             subtitle: 'سجل الآن للحصول على خدماتنا المتميزة',
                                             personalInfoTitle: 'المعلومات الشخصية',
                                             educationTitle: 'المعلومات التعليمية والمهنية',
                                             servicesTitle: 'الخدمات المطلوبة',
                                             trainingTitle: 'تأكيد التدريب',
                                             nameLabel: 'الاسم الكامل *',
                                             nationalIdLabel: 'الرقم القومي *',
                                             governorateLabel: 'المحافظة *',
                                             genderLabel: 'الجنس *',
                                             ageLabel: 'العمر *',
                                             phoneLabel: 'رقم الهاتف *',
                                             whatsappLabel: 'رقم الواتساب',
                                             emailLabel: 'البريد الإلكتروني',
                                             participantTypeLabel: 'نوع المشارك *',
                                             universityLabel: 'الجامعة',
                                             educationStageLabel: 'المرحلة التعليمية',
                                             facultyLabel: 'الكلية',
                                             workEmployerLabel: 'جهة العمل',
                                             serviceLabel: 'اختر الخدمة *',
                                             trainingConfirmationLabel: 'أوافق على المشاركة في التدريب المطلوب',
                                             submitText: 'تسجيل',
                                             selectGovernorate: 'اختر المحافظة',
                                             selectGender: 'اختر الجنس',
                                             maleOption: 'ذكر',
                                             femaleOption: 'أنثى',
                                             selectParticipantType: 'اختر نوع المشارك',
                                             studentOption: 'طالب',
                                             employeeOption: 'موظف',
                                             otherOption: 'أخرى',
                                             selectEducationStage: 'اختر المرحلة',
                                             selectService: 'اختر الخدمة',
                                             qrTitle: 'رمز QR للتسجيل',
                                             qrDescription: 'احفظ هذا الرمز للوصول السريع لبياناتك',
                                             langText: 'English'
                              },
                              en: {
                                             mainTitle: 'Registration System - TIEC',
                                             subtitle: 'Register now to get our premium services',
                                             personalInfoTitle: 'Personal Information',
                                             educationTitle: 'Educational and Professional Information',
                                             servicesTitle: 'Required Services',
                                             trainingTitle: 'Training Confirmation',
                                             nameLabel: 'Full Name *',
                                             nationalIdLabel: 'National ID *',
                                             governorateLabel: 'Governorate *',
                                             genderLabel: 'Gender *',
                                             ageLabel: 'Age *',
                                             phoneLabel: 'Phone Number *',
                                             whatsappLabel: 'WhatsApp Number',
                                             emailLabel: 'Email Address',
                                             participantTypeLabel: 'Participant Type *',
                                             universityLabel: 'University',
                                             educationStageLabel: 'Education Stage',
                                             facultyLabel: 'Faculty',
                                             workEmployerLabel: 'Work Employer',
                                             serviceLabel: 'Select Service *',
                                             trainingConfirmationLabel: 'I agree to participate in the required training',
                                             submitText: 'Register',
                                             selectGovernorate: 'Select Governorate',
                                             selectGender: 'Select Gender',
                                             maleOption: 'Male',
                                             femaleOption: 'Female',
                                             selectParticipantType: 'Select Participant Type',
                                             studentOption: 'Student',
                                             employeeOption: 'Employee',
                                             otherOption: 'Other',
                                             selectEducationStage: 'Select Education Stage',
                                             selectService: 'Select Service',
                                             qrTitle: 'QR Code for Registration',
                                             qrDescription: 'Save this code for quick access to your data',
                                             langText: 'العربية'
                              }
               };

               let currentLang = 'ar';

               // تبديل اللغة
               function toggleLanguage() {
                              currentLang = currentLang === 'ar' ? 'en' : 'ar';
                              updateLanguage();
               }

               // تحديث اللغة
               function updateLanguage() {
                              const t = translations[currentLang];

                              // تحديث النصوص
                              document.getElementById('main-title').textContent = t.mainTitle;
                              document.getElementById('subtitle').textContent = t.subtitle;
                              document.getElementById('personal-info-title').textContent = t.personalInfoTitle;
                              document.getElementById('education-title').textContent = t.educationTitle;
                              document.getElementById('services-title').textContent = t.servicesTitle;
                              document.getElementById('training-title').textContent = t.trainingTitle;

                              // تحديث التسميات
                              document.getElementById('name-label').textContent = t.nameLabel;
                              document.getElementById('national-id-label').textContent = t.nationalIdLabel;
                              document.getElementById('governorate-label').textContent = t.governorateLabel;
                              document.getElementById('gender-label').textContent = t.genderLabel;
                              document.getElementById('age-label').textContent = t.ageLabel;
                              document.getElementById('phone-label').textContent = t.phoneLabel;
                              document.getElementById('whatsapp-label').textContent = t.whatsappLabel;
                              document.getElementById('email-label').textContent = t.emailLabel;
                              document.getElementById('participant-type-label').textContent = t.participantTypeLabel;
                              document.getElementById('university-label').textContent = t.universityLabel;
                              document.getElementById('education-stage-label').textContent = t.educationStageLabel;
                              document.getElementById('faculty-label').textContent = t.facultyLabel;
                              document.getElementById('work-employer-label').textContent = t.workEmployerLabel;
                              document.getElementById('service-label').textContent = t.serviceLabel;
                              document.getElementById('training-confirmation-label').textContent = t
                                             .trainingConfirmationLabel;
                              document.getElementById('submit-text').textContent = t.submitText;

                              // تحديث الخيارات
                              document.getElementById('select-governorate').textContent = t.selectGovernorate;
                              document.getElementById('select-gender').textContent = t.selectGender;
                              document.getElementById('male-option').textContent = t.maleOption;
                              document.getElementById('female-option').textContent = t.femaleOption;
                              document.getElementById('select-participant-type').textContent = t.selectParticipantType;
                              document.getElementById('student-option').textContent = t.studentOption;
                              document.getElementById('employee-option').textContent = t.employeeOption;
                              document.getElementById('other-option').textContent = t.otherOption;
                              document.getElementById('select-education-stage').textContent = t.selectEducationStage;
                              document.getElementById('select-service').textContent = t.selectService;

                              // تحديث زر اللغة
                              document.getElementById('lang-text').textContent = t.langText;

                              // تحديث اتجاه الصفحة
                              document.documentElement.dir = currentLang === 'ar' ? 'rtl' : 'ltr';
                              document.documentElement.lang = currentLang;
               }

               // إدارة الحقول حسب نوع المشارك
               document.getElementById('participant_type').addEventListener('change', function() {
                              const participantType = this.value;
                              const universityField = document.getElementById(
                                             'university-field');
                              const educationStageField = document.getElementById(
                                             'education-stage-field');
                              const facultyField = document.getElementById(
                                             'faculty-field');
                              const workEmployerField = document.getElementById(
                                             'work-employer-field');

                              // إخفاء جميع الحقول أولاً
                              universityField.style.display = 'none';
                              educationStageField.style.display = 'none';
                              facultyField.style.display = 'none';
                              workEmployerField.style.display = 'none';

                              // إظهار الحقول المناسبة
                              if (participantType === 'student') {
                                             universityField.style.display = 'block';
                                             educationStageField.style.display = 'block';
                                             facultyField.style.display = 'block';
                              } else if (participantType === 'employee') {
                                             workEmployerField.style.display = 'block';
                              }
               });

               // تحميل الأسئلة عند اختيار الخدمة
               document.getElementById('service_id').addEventListener('change', function() {
                              const serviceId = this.value;
                              const questionsContainer = document.getElementById(
                                             'service-questions-container');

                              if (serviceId) {
                                             // إرسال طلب AJAX لجلب الأسئلة
                                             fetch('get_service_questions.php', {
                                                                           method: 'POST',
                                                                           headers: {
                                                                                          'Content-Type': 'application/x-www-form-urlencoded',
                                                                           },
                                                                           body: 'service_id=' +
                                                                                          serviceId
                                                            })
                                                            .then(response => response
                                                                           .json())
                                                            .then(data => {
                                                                           if (data
                                                                                          .success) {
                                                                                          questionsContainer
                                                                                                         .innerHTML =
                                                                                                         data
                                                                                                         .html;
                                                                           } else {
                                                                                          questionsContainer
                                                                                                         .innerHTML =
                                                                                                         '<div class="alert alert-warning">لا توجد أسئلة لهذه الخدمة</div>';
                                                                           }
                                                            })
                                                            .catch(error => {
                                                                           console.error('Error:',
                                                                                          error);
                                                                           questionsContainer
                                                                                          .innerHTML =
                                                                                          '<div class="alert alert-danger">خطأ في تحميل الأسئلة</div>';
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
                                                            const alertContainer =
                                                                           document
                                                                           .getElementById(
                                                                                          'alert-container'
                                                                                          );

                                                            if (data
                                                                           .success) {
                                                                           alertContainer.innerHTML = `
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> تم التسجيل بنجاح!
                        </div>
                    `;

                                                                           // إظهار رمز QR
                                                                           if (data
                                                                                          .qr_code) {
                                                                                          document.getElementById(
                                                                                                                        'qr-container')
                                                                                                         .classList
                                                                                                         .remove(
                                                                                                                        'hidden');
                                                                                          document.getElementById(
                                                                                                                        'qr-title')
                                                                                                         .textContent =
                                                                                                         currentLang ===
                                                                                                         'ar' ?
                                                                                                         'رمز QR للتسجيل' :
                                                                                                         'QR Code for Registration';
                                                                                          document.getElementById(
                                                                                                                        'qr-description')
                                                                                                         .textContent =
                                                                                                         currentLang ===
                                                                                                         'ar' ?
                                                                                                         'احفظ هذا الرمز للوصول السريع لبياناتك' :
                                                                                                         'Save this code for quick access to your data';

                                                                                          // إنشاء رمز QR
                                                                                          const qrCode = new QRCode(document
                                                                                                         .getElementById(
                                                                                                                        'qr-code'
                                                                                                                        ), {
                                                                                                                        text: data.qr_code,
                                                                                                                        width: 200,
                                                                                                                        height: 200
                                                                                                         }
                                                                                                         );
                                                                           }

                                                                           // إعادة تعيين النموذج
                                                                           this
                                                            .reset();
                                                            } else {
                                                                           alertContainer.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i> ${data.message}
                        </div>
                    `;
                                                            }
                                             })
                                             .catch(error => {
                                                            console.error('Error:',
                                                                           error);
                                                            document.getElementById(
                                                                                          'alert-container')
                                                                           .innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> خطأ في الاتصال بالخادم
                    </div>
                `;
                                             });
               });

               // تهيئة الصفحة
               document.addEventListener('DOMContentLoaded', function() {
                              updateLanguage();
               });
               </script>
</body>

</html>
