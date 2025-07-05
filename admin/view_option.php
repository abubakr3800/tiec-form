<?php
session_start();
require_once '../cache/db.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// جلب معرف الخيار
$option_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$option_id) {
    header('Location: options_manager.php');
    exit();
}

try {
    // جلب بيانات الخيار مع معلومات السؤال والخدمة
    $stmt = $pdo->prepare("
        SELECT qo.*, sq.question_text_ar as question_text, s.name_ar as service_name
        FROM question_options qo
        LEFT JOIN service_questions sq ON qo.question_id = sq.id
        LEFT JOIN services s ON sq.service_id = s.id
        WHERE qo.id = ?
    ");
    $stmt->execute([$option_id]);
    $option = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$option) {
        header('Location: options_manager.php');
        exit();
    }
    
} catch (Exception $e) {
    header('Location: options_manager.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>عرض الخيار - لوحة التحكم</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
        }
        .info-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        .info-item:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: bold;
            color: #495057;
        }
        .info-value {
            color: #6c757d;
        }
        .status-badge {
            font-size: 0.9em;
            padding: 8px 15px;
        }
        .back-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 25px;
            padding: 10px 25px;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            color: white;
        }
        .option-text {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 15px 0;
            border-left: 4px solid #667eea;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0">
                        <i class="fas fa-eye text-primary"></i>
                        عرض تفاصيل الخيار
                    </h2>
                    <a href="options_manager.php" class="back-btn">
                        <i class="fas fa-arrow-right"></i>
                        العودة للخيارات
                    </a>
                </div>

                <!-- Option Details Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="mb-0">
                            <i class="fas fa-list-ul"></i>
                            تفاصيل الخيار
                        </h4>
                    </div>
                    <div class="card-body">
                        <!-- Option Text -->
                        <div class="option-text">
                            <h5 class="text-primary mb-2">
                                <i class="fas fa-quote-right"></i>
                                نص الخيار:
                            </h5>
                            <p class="mb-0 fs-5"><?php echo htmlspecialchars($option['option_text_ar'] ?? $option['option_text'] ?? ''); ?></p>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">الخدمة:</div>
                                    <div class="info-value"><?php echo htmlspecialchars($option['service_name'] ?? ''); ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">السؤال:</div>
                                    <div class="info-value"><?php echo htmlspecialchars($option['question_text'] ?? ''); ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">ترتيب الخيار:</div>
                                    <div class="info-value"><?php echo $option['sort_order'] ?? 0; ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">الحالة:</div>
                                    <div class="info-value">
                                        <?php if (($option['is_active'] ?? 1) == 1): ?>
                                            <span class="badge bg-success status-badge">نشط</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary status-badge">غير نشط</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">تاريخ الإنشاء:</div>
                                    <div class="info-value"><?php echo date('Y-m-d H:i', strtotime($option['created_at'])); ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">آخر تحديث:</div>
                                    <div class="info-value"><?php echo date('Y-m-d H:i', strtotime($option['updated_at'] ?? $option['created_at'])); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card">
                    <div class="card-body text-center">
                        <a href="options_manager.php" class="btn btn-secondary me-2">
                            <i class="fas fa-arrow-right"></i>
                            العودة للخيارات
                        </a>
                        <button class="btn btn-warning me-2" onclick="editOption(<?php echo $option_id; ?>)">
                            <i class="fas fa-edit"></i>
                            تعديل الخيار
                        </button>
                        <button class="btn btn-danger" onclick="deleteOption(<?php echo $option_id; ?>)">
                            <i class="fas fa-trash"></i>
                            حذف الخيار
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        function editOption(id) {
            // Redirect to options manager with edit modal
            window.location.href = `options_manager.php?edit=${id}`;
        }
        
        function deleteOption(id) {
            Swal.fire({
                title: 'تأكيد الحذف',
                text: 'هل أنت متأكد من حذف هذا الخيار؟',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'نعم، احذف',
                cancelButtonText: 'إلغاء'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`options_ajax.php?action=delete_option&id=${id}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire('نجح', 'تم حذف الخيار بنجاح', 'success');
                                window.location.href = 'options_manager.php';
                            } else {
                                Swal.fire('خطأ', data.message || 'حدث خطأ أثناء الحذف', 'error');
                            }
                        })
                        .catch(error => {
                            Swal.fire('خطأ', 'حدث خطأ في الاتصال', 'error');
                        });
                }
            });
        }
    </script>
</body>
</html> 