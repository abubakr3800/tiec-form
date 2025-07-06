<?php
session_start();
require_once '../config/database.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$pdo = getDBConnection();

// جلب الخدمات والمدربين للقوائم المنسدلة
$services = $pdo->query("SELECT * FROM services WHERE service_type = 'training' AND is_active = 1 ORDER BY name_ar")->fetchAll();
$trainers = $pdo->query("SELECT * FROM trainers WHERE is_active = 1 ORDER BY name")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة التدريبات - TIEC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: white;
            position: fixed;
            top: 0;
            right: 0;
            width: 280px;
            z-index: 1000;
        }
        .main-content {
            margin-right: 280px;
            min-height: 100vh;
        }
        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 10px;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .nav-link {
            color: rgba(255, 255, 255, 0.8);
            transition: all 0.3s ease;
            padding: 12px 20px;
            border-radius: 8px;
            margin: 2px 10px;
        }
        .nav-link:hover, .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="p-4">
            <h4 class="text-white mb-4">
                <i class="fas fa-cogs"></i> لوحة التحكم
            </h4>
            
            <nav class="nav flex-column">
                <a class="nav-link" href="index.php">
                    <i class="fas fa-tachometer-alt"></i> الرئيسية
                </a>
                <a class="nav-link" href="participants.php">
                    <i class="fas fa-users"></i> المشاركين
                </a>
                <a class="nav-link" href="services_manager.php">
                    <i class="fas fa-cogs"></i> إدارة الخدمات
                </a>
                <a class="nav-link active" href="trainings_manager.php">
                    <i class="fas fa-graduation-cap"></i> إدارة التدريبات
                </a>
                <a class="nav-link" href="trainers.php">
                    <i class="fas fa-chalkboard-teacher"></i> المدربين
                </a>
                <a class="nav-link" href="attendance_manager.php">
                    <i class="fas fa-calendar-check"></i> إدارة الحضور
                </a>
                <hr class="text-white">
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt"></i> تسجيل الخروج
                </a>
            </nav>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid p-4">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">إدارة التدريبات</h2>
                    <p class="text-muted">إضافة وتعديل وإدارة التدريبات</p>
                </div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTrainingModal">
                    <i class="fas fa-plus"></i> إضافة تدريب جديد
                </button>
            </div>

            <!-- إحصائيات سريعة -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 id="totalTrainings">0</h4>
                                    <p class="mb-0">إجمالي التدريبات</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-graduation-cap fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 id="activeTrainings">0</h4>
                                    <p class="mb-0">التدريبات النشطة</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-play-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 id="totalParticipants">0</h4>
                                    <p class="mb-0">إجمالي المشاركين</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-users fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 id="upcomingTrainings">0</h4>
                                    <p class="mb-0">تدريبات قادمة</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-calendar fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- جدول التدريبات -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-list"></i> قائمة التدريبات
                    </h5>
                </div>
                <div class="card-body">
                    <table id="trainingsTable" class="table table-striped">
                        <thead>
                            <tr>
                                <th>الخدمة</th>
                                <th>التدريب</th>
                                <th>المدرب</th>
                                <th>التاريخ</th>
                                <th>الوقت</th>
                                <th>المشاركين</th>
                                <th>الحالة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- سيتم ملء البيانات بواسطة JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal إضافة تدريب جديد -->
    <div class="modal fade" id="addTrainingModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus"></i> إضافة تدريب جديد
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addTrainingForm">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="service_id" class="form-label">الخدمة *</label>
                                <select class="form-select" id="service_id" name="service_id" required>
                                    <option value="">اختر الخدمة</option>
                                    <?php foreach ($services as $service): ?>
                                        <option value="<?= $service['id'] ?>"><?= htmlspecialchars($service['name_ar']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="trainer_id" class="form-label">المدرب *</label>
                                <select class="form-select" id="trainer_id" name="trainer_id" required>
                                    <option value="">اختر المدرب</option>
                                    <?php foreach ($trainers as $trainer): ?>
                                        <option value="<?= $trainer['id'] ?>"><?= htmlspecialchars($trainer['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="title_ar" class="form-label">عنوان التدريب (عربي) *</label>
                                <input type="text" class="form-control" id="title_ar" name="title_ar" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="title_en" class="form-label">عنوان التدريب (إنجليزي) *</label>
                                <input type="text" class="form-control" id="title_en" name="title_en" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description_ar" class="form-label">وصف التدريب (عربي)</label>
                            <textarea class="form-control" id="description_ar" name="description_ar" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description_en" class="form-label">وصف التدريب (إنجليزي)</label>
                            <textarea class="form-control" id="description_en" name="description_en" rows="3"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label">تاريخ البداية *</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label">تاريخ النهاية *</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="start_time" class="form-label">وقت البداية *</label>
                                <input type="time" class="form-control" id="start_time" name="start_time" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="end_time" class="form-label">وقت النهاية *</label>
                                <input type="time" class="form-control" id="end_time" name="end_time" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="max_participants" class="form-label">الحد الأقصى للمشاركين *</label>
                            <input type="number" class="form-control" id="max_participants" name="max_participants" 
                                   min="1" max="100" value="20" required>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                                <label class="form-check-label" for="is_active">
                                    تدريب نشط
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> حفظ التدريب
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // تهيئة DataTable
        let trainingsTable = $('#trainingsTable').DataTable({
            ajax: {
                url: 'trainings_ajax.php?action=get_trainings',
                dataSrc: 'data'
            },
            columns: [
                { data: 'service_name' },
                { data: 'title_ar' },
                { data: 'trainer_name' },
                { 
                    data: null,
                    render: function(data) {
                        return data.start_date + ' - ' + data.end_date;
                    }
                },
                { 
                    data: null,
                    render: function(data) {
                        return data.start_time + ' - ' + data.end_time;
                    }
                },
                { 
                    data: null,
                    render: function(data) {
                        return data.current_participants + '/' + data.max_participants;
                    }
                },
                { 
                    data: 'is_active',
                    render: function(data) {
                        return data == 1 ? 
                            '<span class="badge bg-success">نشط</span>' : 
                            '<span class="badge bg-secondary">غير نشط</span>';
                    }
                },
                { 
                    data: null,
                    render: function(data) {
                        return `
                            <button class="btn btn-sm btn-primary" onclick="editTraining(${data.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-info" onclick="viewParticipants(${data.id})">
                                <i class="fas fa-users"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteTraining(${data.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        `;
                    }
                }
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/ar.json'
            }
        });

        // تحميل الإحصائيات
        function loadStatistics() {
            fetch('trainings_ajax.php?action=get_statistics')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('totalTrainings').textContent = data.total_trainings;
                    document.getElementById('activeTrainings').textContent = data.active_trainings;
                    document.getElementById('totalParticipants').textContent = data.total_participants;
                    document.getElementById('upcomingTrainings').textContent = data.upcoming_trainings;
                });
        }

        // إضافة تدريب جديد
        document.getElementById('addTrainingForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'add_training');
            
            fetch('trainings_ajax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('نجح', data.message, 'success');
                    $('#addTrainingModal').modal('hide');
                    this.reset();
                    trainingsTable.ajax.reload();
                    loadStatistics();
                } else {
                    Swal.fire('خطأ', data.message, 'error');
                }
            });
        });

        // تحميل البيانات عند تحميل الصفحة
        document.addEventListener('DOMContentLoaded', function() {
            loadStatistics();
        });
    </script>
</body>
</html> 