<?php
session_start();
require_once '../config/database.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$pdo = getDBConnection();

// جلب التدريبات النشطة
$trainings = $pdo->query("
    SELECT t.*, s.name_ar as service_name, tr.name as trainer_name
    FROM trainings t
    JOIN services s ON t.service_id = s.id
    JOIN trainers tr ON t.trainer_id = tr.id
    WHERE t.is_active = 1 AND t.end_date >= CURDATE()
    ORDER BY t.start_date ASC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الحضور - TIEC</title>
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
        .attendance-card {
            border: 2px solid #e9ecef;
            border-radius: 15px;
            transition: all 0.3s ease;
            margin-bottom: 15px;
        }
        .attendance-card:hover {
            border-color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
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
                <a class="nav-link" href="trainings_manager.php">
                    <i class="fas fa-graduation-cap"></i> إدارة التدريبات
                </a>
                <a class="nav-link" href="trainers.php">
                    <i class="fas fa-chalkboard-teacher"></i> المدربين
                </a>
                <a class="nav-link active" href="attendance_manager.php">
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
                    <h2 class="mb-1">إدارة الحضور</h2>
                    <p class="text-muted">تتبع وإدارة حضور المشاركين</p>
                </div>
                <div>
                    <a href="../attendance_scanner.php" class="btn btn-primary me-2" target="_blank">
                        <i class="fas fa-qrcode"></i> QR Scanner
                    </a>
                    <button class="btn btn-success" onclick="exportAttendance()">
                        <i class="fas fa-download"></i> تصدير التقرير
                    </button>
                </div>
            </div>

            <!-- إحصائيات سريعة -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 id="totalAttendance">0</h4>
                                    <p class="mb-0">إجمالي الحضور اليوم</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-calendar-check fa-2x"></i>
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
                                    <h4 id="presentCount">0</h4>
                                    <p class="mb-0">الحضور</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-check-circle fa-2x"></i>
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
                                    <h4 id="lateCount">0</h4>
                                    <p class="mb-0">متأخر</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-clock fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 id="absentCount">0</h4>
                                    <p class="mb-0">غائب</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-times-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- فلتر التاريخ والتدريب -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="attendance_date" class="form-label">التاريخ</label>
                            <input type="date" class="form-control" id="attendance_date" 
                                   value="<?= date('Y-m-d') ?>" onchange="loadAttendance()">
                        </div>
                        <div class="col-md-4">
                            <label for="training_filter" class="form-label">التدريب</label>
                            <select class="form-select" id="training_filter" onchange="loadAttendance()">
                                <option value="">جميع التدريبات</option>
                                <?php foreach ($trainings as $training): ?>
                                    <option value="<?= $training['id'] ?>">
                                        <?= htmlspecialchars($training['title_ar']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <button class="btn btn-primary" onclick="loadAttendance()">
                                    <i class="fas fa-search"></i> بحث
                                </button>
                                <button class="btn btn-outline-secondary" onclick="resetFilters()">
                                    <i class="fas fa-undo"></i> إعادة تعيين
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- جدول الحضور -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-list"></i> سجل الحضور
                    </h5>
                </div>
                <div class="card-body">
                    <table id="attendanceTable" class="table table-striped">
                        <thead>
                            <tr>
                                <th>المشارك</th>
                                <th>التدريب</th>
                                <th>التاريخ</th>
                                <th>وقت الحضور</th>
                                <th>وقت الانصراف</th>
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

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        let attendanceTable;

        // تهيئة DataTable
        $(document).ready(function() {
            attendanceTable = $('#attendanceTable').DataTable({
                ajax: {
                    url: 'attendance_ajax.php?action=get_attendance',
                    data: function(d) {
                        d.date = $('#attendance_date').val();
                        d.training_id = $('#training_filter').val();
                    }
                },
                columns: [
                    { data: 'participant_name' },
                    { data: 'training_title' },
                    { data: 'attendance_date' },
                    { data: 'check_in_time' },
                    { data: 'check_out_time' },
                    { 
                        data: 'status',
                        render: function(data) {
                            const statusMap = {
                                'present': '<span class="badge bg-success">حاضر</span>',
                                'late': '<span class="badge bg-warning">متأخر</span>',
                                'absent': '<span class="badge bg-danger">غائب</span>'
                            };
                            return statusMap[data] || data;
                        }
                    },
                    { 
                        data: null,
                        render: function(data) {
                            return `
                                <button class="btn btn-sm btn-primary" onclick="editAttendance(${data.id})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteAttendance(${data.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            `;
                        }
                    }
                ],
                dom: 'Bfrtip',
                scrollY: '400px',
                scrollX: true,
                scrollCollapse: true,
                buttons: [
                    {
                        extend: 'copy',
                        text: '<i class="fas fa-copy"></i> \u0646\u0633\u062e',
                        className: 'btn btn-secondary',
                        exportOptions: { modifier: { page: 'all' } }
                    },
                    {
                        extend: 'csv',
                        text: '<i class="fas fa-file-csv"></i> CSV',
                        className: 'btn btn-success',
                        exportOptions: { modifier: { page: 'all' } }
                    },
                    {
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-success',
                        exportOptions: { modifier: { page: 'all' } }
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn btn-danger',
                        exportOptions: { modifier: { page: 'all' } }
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i> \u0637\u0628\u0627\u0639\u0629',
                        className: 'btn btn-info',
                        exportOptions: { modifier: { page: 'all' } }
                    },
                    {
                        extend: 'colvis',
                        text: '<i class="fas fa-columns"></i> \u0627\u0644\u0623\u0639\u0645\u062f\u0629',
                        className: 'btn btn-warning'
                    }
                ],
                responsive: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/ar.json'
                }
            });
        });

        // تحميل الحضور
        function loadAttendance() {
            attendanceTable.ajax.reload();
            loadStatistics();
        }

        // تحميل الإحصائيات
        function loadStatistics() {
            const date = $('#attendance_date').val();
            const training_id = $('#training_filter').val();
            
            fetch(`attendance_ajax.php?action=get_statistics&date=${date}&training_id=${training_id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('totalAttendance').textContent = data.total_attendance;
                    document.getElementById('presentCount').textContent = data.present_count;
                    document.getElementById('lateCount').textContent = data.late_count;
                    document.getElementById('absentCount').textContent = data.absent_count;
                });
        }

        // إعادة تعيين الفلاتر
        function resetFilters() {
            $('#attendance_date').val('<?= date('Y-m-d') ?>');
            $('#training_filter').val('');
            loadAttendance();
        }

        // تصدير التقرير
        function exportAttendance() {
            const date = $('#attendance_date').val();
            const training_id = $('#training_filter').val();
            
            window.open(`attendance_ajax.php?action=export&date=${date}&training_id=${training_id}`, '_blank');
        }

        // تحميل البيانات عند تحميل الصفحة
        document.addEventListener('DOMContentLoaded', function() {
            loadStatistics();
        });
    </script>
</body>
</html> 