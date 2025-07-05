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
    <title>إدارة الخيارات - TIEC</title>
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
                <a class="nav-link" href="participants.php">
                    <i class="fas fa-users"></i> المشاركين
                </a>
                <a class="nav-link" href="services_manager.php">
                    <i class="fas fa-cogs"></i> إدارة الخدمات
                </a>
                <a class="nav-link active" href="options_manager.php">
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
                    <h2 class="mb-1">إدارة الخيارات</h2>
                    <p class="text-muted">إدارة خيارات الأسئلة والخدمات</p>
                </div>
                <div>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-right"></i> العودة للوحة التحكم
                    </a>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addOptionModal">
                        <i class="fas fa-plus"></i> إضافة خيار جديد
                    </button>
                </div>
            </div>

            <!-- Options Table -->
            <div class="table-container p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0">
                        <i class="fas fa-list"></i> قائمة الخيارات
                    </h4>
                    <div>
                        <button class="btn btn-primary" onclick="exportData()">
                            <i class="fas fa-download"></i> تصدير البيانات
                        </button>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table id="options-table" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>الخيار</th>
                                <th>الخدمة</th>
                                <th>السؤال</th>
                                <th>الحالة</th>
                                <th>تاريخ الإنشاء</th>
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

    <!-- Add Option Modal -->
    <div class="modal fade" id="addOptionModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">إضافة خيار جديد</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addOptionForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">الخدمة</label>
                                    <select class="form-select" name="service_id" id="serviceSelect" required>
                                        <option value="">اختر الخدمة</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">السؤال</label>
                                    <select class="form-select" name="question_id" id="questionSelect" required>
                                        <option value="">اختر السؤال</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">نص الخيار</label>
                                    <input type="text" class="form-control" name="option_text" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">الحالة</label>
                                    <select class="form-select" name="is_active">
                                        <option value="1">نشط</option>
                                        <option value="0">غير نشط</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-primary" onclick="saveOption()">حفظ الخيار</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Option Modal -->
    <div class="modal fade" id="editOptionModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">تعديل الخيار</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editOptionForm">
                        <input type="hidden" name="option_id" id="edit_option_id">
                        <input type="hidden" name="question_id" id="edit_question_id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">نص الخيار</label>
                                    <input type="text" class="form-control" name="option_text" id="edit_option_text" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">ترتيب الخيار</label>
                                    <input type="number" class="form-control" name="sort_order" id="edit_sort_order" value="0">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">الحالة</label>
                            <select class="form-select" name="is_active" id="edit_is_active">
                                <option value="1">نشط</option>
                                <option value="0">غير نشط</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-primary" onclick="updateOption()">تحديث الخيار</button>
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

        // Load services for dropdown
        function loadServices() {
            fetch('options_ajax.php?action=get_services')
                .then(response => response.json())
                .then(data => {
                    const serviceSelect = document.getElementById('serviceSelect');
                    serviceSelect.innerHTML = '<option value="">اختر الخدمة</option>';
                    
                    if (data && Array.isArray(data)) {
                        data.forEach(service => {
                            const option = document.createElement('option');
                            option.value = service.id;
                            option.textContent = service.service_name;
                            serviceSelect.appendChild(option);
                        });
                    }
                })
                .catch(error => {
                    console.error('Error loading services:', error);
                });
        }

        // Load questions when service is selected
        document.getElementById('serviceSelect').addEventListener('change', function() {
            const serviceId = this.value;
            const questionSelect = document.getElementById('questionSelect');
            
            if (serviceId) {
                fetch(`options_ajax.php?action=get_questions&service_id=${serviceId}`)
                    .then(response => response.json())
                    .then(data => {
                        questionSelect.innerHTML = '<option value="">اختر السؤال</option>';
                        
                        if (data && Array.isArray(data)) {
                            data.forEach(question => {
                                const option = document.createElement('option');
                                option.value = question.id;
                                option.textContent = question.question_text;
                                questionSelect.appendChild(option);
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error loading questions:', error);
                    });
            } else {
                questionSelect.innerHTML = '<option value="">اختر السؤال</option>';
            }
        });

        // DataTable initialization
        $(document).ready(function() {
            loadServices();
            
            $('#options-table').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: 'options_ajax.php?action=get_options',
                    type: 'GET',
                    dataSrc: function(json) {
                        // Debug: log the response
                        console.log('AJAX Response:', json);
                        
                        // Check if response is valid
                        if (!json || !json.data) {
                            console.error('Invalid response format:', json);
                            return [];
                        }
                        
                        return json.data;
                    },
                    error: function(xhr, error, thrown) {
                        console.error('AJAX Error:', error);
                        console.error('Response:', xhr.responseText);
                    }
                },
                columns: [
                    { data: 'option_text' },
                    { data: 'service_name' },
                    { data: 'question_text' },
                    { 
                        data: 'is_active',
                        render: function(data) {
                            return data == 1 ? 
                                '<span class="badge bg-success">نشط</span>' : 
                                '<span class="badge bg-secondary">غير نشط</span>';
                        }
                    },
                    { 
                        data: 'created_at',
                        render: function(data) {
                            return new Date(data).toLocaleDateString('ar-SA');
                        }
                    },
                    {
                        data: 'id',
                        render: function(data) {
                            return `
                                <div class="btn-group" role="group">
                                    <a href="view_option.php?id=${data}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> عرض
                                    </a>
                                    <button class="btn btn-sm btn-warning" onclick="editOption(${data})">
                                        <i class="fas fa-edit"></i> تعديل
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteOption(${data})">
                                        <i class="fas fa-trash"></i> حذف
                                    </button>
                                </div>
                            `;
                        }
                    }
                ],
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'copy',
                        text: '<i class="fas fa-copy"></i> نسخ',
                        className: 'btn btn-secondary'
                    },
                    {
                        extend: 'csv',
                        text: '<i class="fas fa-file-csv"></i> CSV',
                        className: 'btn btn-success'
                    },
                    {
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-success'
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn btn-danger'
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i> طباعة',
                        className: 'btn btn-info'
                    },
                    {
                        extend: 'colvis',
                        text: '<i class="fas fa-columns"></i> الأعمدة',
                        className: 'btn btn-warning'
                    }
                ],
                responsive: true,
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/ar.json'
                }
            });
        });

        // Save option function
        function saveOption() {
            const formData = new FormData(document.getElementById('addOptionForm'));
            
            fetch('options_ajax.php?action=add_option', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('نجح', 'تم إضافة الخيار بنجاح', 'success');
                    $('#addOptionModal').modal('hide');
                    $('#addOptionForm')[0].reset();
                    $('#options-table').DataTable().ajax.reload();
                } else {
                    Swal.fire('خطأ', data.message || 'حدث خطأ أثناء الإضافة', 'error');
                }
            })
            .catch(error => {
                Swal.fire('خطأ', 'حدث خطأ في الاتصال', 'error');
            });
        }

        // Edit option function
        function editOption(id) {
            // Fetch option data
            fetch(`options_ajax.php?action=get_option&id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const option = data.option;
                        document.getElementById('edit_option_id').value = option.id;
                        document.getElementById('edit_question_id').value = option.question_id;
                        document.getElementById('edit_option_text').value = option.option_text;
                        document.getElementById('edit_sort_order').value = option.sort_order || 0;
                        document.getElementById('edit_is_active').value = option.is_active;
                        
                        // Show edit modal
                        const editModal = new bootstrap.Modal(document.getElementById('editOptionModal'));
                        editModal.show();
                    } else {
                        Swal.fire('خطأ', data.message || 'حدث خطأ في جلب بيانات الخيار', 'error');
                    }
                })
                .catch(error => {
                    Swal.fire('خطأ', 'حدث خطأ في الاتصال', 'error');
                });
        }

        // Update option function
        function updateOption() {
            const formData = new FormData(document.getElementById('editOptionForm'));
            
            fetch('options_ajax.php?action=update_option', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('نجح', 'تم تحديث الخيار بنجاح', 'success');
                    $('#editOptionModal').modal('hide');
                    $('#options-table').DataTable().ajax.reload();
                } else {
                    Swal.fire('خطأ', data.message || 'حدث خطأ أثناء التحديث', 'error');
                }
            })
            .catch(error => {
                Swal.fire('خطأ', 'حدث خطأ في الاتصال', 'error');
            });
        }

        // Delete option function
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
                                $('#options-table').DataTable().ajax.reload();
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
            $('#options-table').DataTable().button('csv').trigger();
        }
    </script>
</body>
</html> 