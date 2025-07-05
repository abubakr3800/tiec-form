<?php
session_start();
require_once '../cache/db.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// جلب معرف المشرف
$admin_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$admin_id) {
    header('Location: admins.php');
    exit();
}

try {
    // جلب بيانات المشرف
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
    $stmt->execute([$admin_id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin) {
        header('Location: admins.php');
        exit();
    }
    
} catch (Exception $e) {
    header('Location: admins.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>عرض المشرف - لوحة التحكم</title>
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
        .role-badge {
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
        .admin-avatar {
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
                        عرض تفاصيل المشرف
                    </h2>
                    <a href="admins.php" class="back-btn">
                        <i class="fas fa-arrow-right"></i>
                        العودة للمشرفين
                    </a>
                </div>

                <!-- Admin Details Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="mb-0">
                            <i class="fas fa-user-shield"></i>
                            معلومات المشرف
                        </h4>
                    </div>
                    <div class="card-body text-center">
                        <!-- Admin Avatar -->
                        <div class="admin-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        
                        <h4 class="mb-3"><?php echo htmlspecialchars($admin['name']); ?></h4>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">اسم المستخدم:</div>
                                    <div class="info-value"><?php echo htmlspecialchars($admin['username']); ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">البريد الإلكتروني:</div>
                                    <div class="info-value"><?php echo htmlspecialchars($admin['email']); ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">الدور:</div>
                                    <div class="info-value">
                                        <?php if ($admin['role'] === 'super_admin'): ?>
                                            <span class="badge bg-danger role-badge">مشرف رئيسي</span>
                                        <?php else: ?>
                                            <span class="badge bg-primary role-badge">مشرف</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">الحالة:</div>
                                    <div class="info-value">
                                        <?php if (($admin['is_active'] ?? 1) == 1): ?>
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
                                    <div class="info-value"><?php echo date('Y-m-d H:i', strtotime($admin['created_at'])); ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">آخر تسجيل دخول:</div>
                                    <div class="info-value">
                                        <?php if (!empty($admin['last_login'])): ?>
                                            <?php echo date('Y-m-d H:i', strtotime($admin['last_login'])); ?>
                                        <?php else: ?>
                                            <span class="text-muted">لم يسجل دخول</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($admin['phone'])): ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">رقم الهاتف:</div>
                                    <div class="info-value"><?php echo htmlspecialchars($admin['phone']); ?></div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card">
                    <div class="card-body text-center">
                        <a href="admins.php" class="btn btn-secondary me-2">
                            <i class="fas fa-arrow-right"></i>
                            العودة للمشرفين
                        </a>
                        <button class="btn btn-warning me-2" onclick="editAdmin(<?php echo $admin_id; ?>)">
                            <i class="fas fa-edit"></i>
                            تعديل المشرف
                        </button>
                        <button class="btn btn-danger" onclick="deleteAdmin(<?php echo $admin_id; ?>)">
                            <i class="fas fa-trash"></i>
                            حذف المشرف
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        function editAdmin(id) {
            // Redirect to admins page with edit modal
            window.location.href = `admins.php?edit=${id}`;
        }
        
        function deleteAdmin(id) {
            Swal.fire({
                title: 'تأكيد الحذف',
                text: 'هل أنت متأكد من حذف هذا المشرف؟',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'نعم، احذف',
                cancelButtonText: 'إلغاء'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`delete_admin.php?id=${id}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire('نجح', 'تم حذف المشرف بنجاح', 'success');
                                window.location.href = 'admins.php';
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