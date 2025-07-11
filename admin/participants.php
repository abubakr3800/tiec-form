<?php
session_start();
require_once '../config/database.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$pdo = getDBConnection();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المشاركين - TIEC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }
        
        /* Sidebar Styles */
        .sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: white;
            position: fixed;
            top: 0;
            right: 0;
            width: 280px;
            z-index: 1000;
            transition: transform 0.3s ease;
            box-shadow: -2px 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar.collapsed {
            transform: translateX(100%);
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            transition: all 0.3s ease;
            padding: 12px 20px;
            border-radius: 8px;
            margin: 2px 10px;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateX(-5px);
        }
        
        /* Main Content */
        .main-content {
            margin-right: 280px;
            transition: margin-right 0.3s ease;
            min-height: 100vh;
        }
        
        .main-content.expanded {
            margin-right: 0;
        }
        
        /* Toggle Button */
        .sidebar-toggle {
            position: fixed;
            top: 20px;
            right: 300px;
            z-index: 1001;
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            color: white;
            padding: 10px 15px;
            border-radius: 50px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }
        
        .sidebar-toggle:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }
        
        .sidebar-toggle.collapsed {
            right: 20px;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                transform: translateX(100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-right: 0;
            }
            
            .sidebar-toggle {
                right: 20px;
            }
        }
        
        /* Content Styles */
        .table-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 10px;
        }
        .dataTables_wrapper .dt-buttons {
            margin-bottom: 10px;
        }
        .dataTables_wrapper .dt-buttons .btn {
            margin-right: 5px;
            margin-bottom: 5px;
        }
        .btn-group .btn {
            margin-right: 2px;
        }
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }
        .badge {
            font-size: 0.8em;
            padding: 0.4em 0.8em;
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        /* Overlay for mobile */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            display: none;
        }
        
        .sidebar-overlay.show {
            /* display: block; */
        }
    </style>
</head>
<body>
    <!-- Sidebar Toggle Button -->
    <button class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()">
        <i class="fas fa-bars" id="toggleIcon"></i>
    </button>
    
    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="text-white mb-0">
                    <i class="fas fa-cogs"></i> لوحة التحكم
                </h4>
                <button class="btn btn-sm btn-outline-light d-md-none" onclick="toggleSidebar()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <nav class="nav flex-column">
                <a class="nav-link" href="index.php">
                    <i class="fas fa-tachometer-alt"></i> الرئيسية
                </a>
                <a class="nav-link active" href="participants.php">
                    <i class="fas fa-users"></i> المشاركين
                </a>
                <a class="nav-link" href="services_manager.php">
                    <i class="fas fa-cogs"></i> إدارة الخدمات
                </a>
                <a class="nav-link" href="options_manager.php">
                    <i class="fas fa-list"></i> إدارة الخيارات
                </a>
                <a class="nav-link" href="admins.php">
                    <i class="fas fa-user-shield"></i> المشرفين
                </a>
                <a class="nav-link" href="trainers.php">
                    <i class="fas fa-chalkboard-teacher"></i> المدربين
                </a>
                <hr class="text-white">
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt"></i> تسجيل الخروج
                </a>
            </nav>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <div class="container-fluid p-4">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">إدارة المشاركين</h2>
                    <p class="text-muted">عرض وإدارة جميع المشاركين المسجلين</p>
                </div>
                <div>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-right"></i> العودة للوحة التحكم
                    </a>
                </div>
            </div>

            <!-- Participants Table -->
            <div class="table-container p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0">
                        <i class="fas fa-users"></i> قائمة المشاركين
                    </h4>
                    <div>
                        <button class="btn btn-primary" onclick="exportData()">
                            <i class="fas fa-download"></i> تصدير البيانات
                        </button>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table id="participants-table" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>الاسم</th>
                                <th>الرقم القومي</th>
                                <th>المحافظة</th>
                                <th>نوع المشارك</th>
                                <th>الجنس</th>
                                <th>العمر</th>
                                <th>رقم الهاتف</th>
                                <th>تاريخ التسجيل</th>
                                <th>تأكيد التدريب</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- سيتم ملء البيانات عبر AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

    <script>
        // Sidebar Toggle Functionality
        let sidebarCollapsed = false;
        
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const toggleBtn = document.getElementById('sidebarToggle');
            const toggleIcon = document.getElementById('toggleIcon');
            const overlay = document.getElementById('sidebarOverlay');
            
            sidebarCollapsed = !sidebarCollapsed;
            
            if (sidebarCollapsed) {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('expanded');
                toggleBtn.classList.add('collapsed');
                toggleIcon.className = 'fas fa-bars';
                overlay.classList.add('show');
            } else {
                sidebar.classList.remove('collapsed');
                mainContent.classList.remove('expanded');
                toggleBtn.classList.remove('collapsed');
                toggleIcon.className = 'fas fa-times';
                overlay.classList.remove('show');
            }
        }
        
        // Close sidebar when clicking overlay on mobile
        document.getElementById('sidebarOverlay').addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                toggleSidebar();
            }
        });
        
        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                document.getElementById('sidebarOverlay').classList.remove('show');
            }
        });

        // DataTable initialization
        $(document).ready(function() {
            $('#participants-table').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: 'get_participants.php',
                    type: 'GET'
                },
                columns: [
                    { data: 'name' },
                    { data: 'national_id' },
                    { data: 'governorate' },
                    { 
                        data: 'participant_type',
                        render: function(data) {
                            const types = {
                                'student': 'طالب',
                                'employee': 'موظف',
                                'other': 'آخر'
                            };
                            return types[data] || data;
                        }
                    },
                    { 
                        data: 'gender',
                        render: function(data) {
                            return data === 'male' ? 'ذكر' : 'أنثى';
                        }
                    },
                    { data: 'age' },
                    { data: 'phone' },
                    { 
                        data: 'registration_date',
                        render: function(data) {
                            return new Date(data).toLocaleDateString('ar-SA');
                        }
                    },
                    // تم حذف عمود training_confirmation
                    {
                        data: 'id',
                        render: function(data) {
                            return `
                                <div class="btn-group" role="group">
                                    <a href="view.php?id=${data}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> عرض
                                    </a>
                                    <a href="view_participant_qr.php?id=${data}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-qrcode"></i> QR
                                    </a>
                                    <button class="btn btn-sm btn-danger" onclick="deleteParticipant(${data})">
                                        <i class="fas fa-trash"></i> حذف
                                    </button>
                                </div>
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
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/ar.json'
                }
            });
        });

        // Delete participant function
        function deleteParticipant(id) {
            Swal.fire({
                title: 'تأكيد الحذف',
                text: 'هل أنت متأكد من حذف هذا المشارك؟',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'نعم، احذف',
                cancelButtonText: 'إلغاء'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`delete_participant.php?id=${id}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire('نجح', 'تم حذف المشارك بنجاح', 'success');
                                $('#participants-table').DataTable().ajax.reload();
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

        // Export data function
        function exportData() {
            $('#participants-table').DataTable().button('csv').trigger();
        }
    </script>
</body>
</html> 