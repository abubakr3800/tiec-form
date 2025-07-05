<?php
session_start();
require_once '../cache/db.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// جلب معرف المدرب
$trainer_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$trainer_id) {
    header('Location: trainers.php');
    exit();
}

try {
    // جلب بيانات المدرب
    $stmt = $pdo->prepare("SELECT * FROM trainers WHERE id = ?");
    $stmt->execute([$trainer_id]);
    $trainer = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$trainer) {
        header('Location: trainers.php');
        exit();
    }
    
} catch (Exception $e) {
    header('Location: trainers.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>عرض المدرب - لوحة التحكم</title>
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
        .trainer-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2em;
            margin: 0 auto 20px;
        }
        .specialization-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            display: inline-block;
            margin: 10px 0;
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
                        عرض تفاصيل المدرب
                    </h2>
                    <a href="trainers.php" class="back-btn">
                        <i class="fas fa-arrow-right"></i>
                        العودة للمدربين
                    </a>
                </div>

                <!-- Trainer Details Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="mb-0">
                            <i class="fas fa-chalkboard-teacher"></i>
                            معلومات المدرب
                        </h4>
                    </div>
                    <div class="card-body text-center">
                        <!-- Trainer Avatar -->
                        <div class="trainer-avatar">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        
                        <h4 class="mb-3"><?php echo htmlspecialchars($trainer['name']); ?></h4>
                        
                        <?php if (!empty($trainer['specialization'])): ?>
                        <div class="specialization-badge">
                            <i class="fas fa-certificate"></i>
                            <?php echo htmlspecialchars($trainer['specialization']); ?>
                        </div>
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">اسم المستخدم:</div>
                                    <div class="info-value"><?php echo htmlspecialchars($trainer['username']); ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">البريد الإلكتروني:</div>
                                    <div class="info-value"><?php echo htmlspecialchars($trainer['email']); ?></div>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($trainer['phone'])): ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">رقم الهاتف:</div>
                                    <div class="info-value"><?php echo htmlspecialchars($trainer['phone']); ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">الحالة:</div>
                                    <div class="info-value">
                                        <?php if (($trainer['is_active'] ?? 1) == 1): ?>
                                            <span class="badge bg-success status-badge">نشط</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary status-badge">غير نشط</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">الحالة:</div>
                                    <div class="info-value">
                                        <?php if (($trainer['is_active'] ?? 1) == 1): ?>
                                            <span class="badge bg-success status-badge">نشط</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary status-badge">غير نشط</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($trainer['experience_years'])): ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">سنوات الخبرة:</div>
                                    <div class="info-value"><?php echo $trainer['experience_years']; ?> سنة</div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">تاريخ الإنشاء:</div>
                                    <div class="info-value"><?php echo date('Y-m-d H:i', strtotime($trainer['created_at'])); ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">آخر تسجيل دخول:</div>
                                    <div class="info-value">
                                        <?php if (!empty($trainer['last_login'])): ?>
                                            <?php echo date('Y-m-d H:i', strtotime($trainer['last_login'])); ?>
                                        <?php else: ?>
                                            <span class="text-muted">لم يسجل دخول</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card">
                    <div class="card-body text-center">
                        <a href="trainers.php" class="btn btn-secondary me-2">
                            <i class="fas fa-arrow-right"></i>
                            العودة للمدربين
                        </a>
                        <button class="btn btn-warning me-2" onclick="editTrainer(<?php echo $trainer_id; ?>)">
                            <i class="fas fa-edit"></i>
                            تعديل المدرب
                        </button>
                        <button class="btn btn-danger" onclick="deleteTrainer(<?php echo $trainer_id; ?>)">
                            <i class="fas fa-trash"></i>
                            حذف المدرب
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        function editTrainer(id) {
            // Redirect to trainers page with edit modal
            window.location.href = `trainers.php?edit=${id}`;
        }
        
        function deleteTrainer(id) {
            Swal.fire({
                title: 'تأكيد الحذف',
                text: 'هل أنت متأكد من حذف هذا المدرب؟',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'نعم، احذف',
                cancelButtonText: 'إلغاء'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`delete_trainer.php?id=${id}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire('نجح', 'تم حذف المدرب بنجاح', 'success');
                                window.location.href = 'trainers.php';
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