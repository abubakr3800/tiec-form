<?php
session_start();
require_once '../config/database.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// التحقق من وجود معرف المشارك
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php?error=معرف المشارك غير صحيح');
    exit();
}

$pdo = getDBConnection();
$participant_id = $_GET['id'];

try {
    // جلب بيانات المشارك
    $stmt = $pdo->prepare("SELECT * FROM participants WHERE id = ?");
    $stmt->execute([$participant_id]);
    $participant = $stmt->fetch();
    
    if (!$participant) {
        header('Location: index.php?error=المشارك غير موجود');
        exit();
    }
    
} catch (Exception $e) {
    header('Location: index.php?error=خطأ في جلب البيانات');
    exit();
}

// تحويل نوع المشارك للعربية
$participant_types = [
    'student' => 'طالب',
    'employee' => 'موظف',
    'other' => 'آخر'
];

// تحويل الجنس للعربية
$gender_types = [
    'male' => 'ذكر',
    'female' => 'أنثى'
];

// إنشاء رمز QR فريد للمشارك
$qr_data = json_encode([
    'id' => $participant['id'],
    'name' => $participant['name'],
    'national_id' => $participant['national_id'],
    'registration_date' => $participant['registration_date']
]);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>عرض المشارك - TIEC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
        .profile-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .qr-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 10px;
        }
        
        .info-row {
            border-bottom: 1px solid #e9ecef;
            padding: 15px 0;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #495057;
        }
        
        .info-value {
            color: #6c757d;
        }
        
        .qr-code-container {
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            margin: 20px 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .token-display {
            background: #e9ecef;
            padding: 10px;
            border-radius: 5px;
            font-family: monospace;
            word-break: break-all;
            font-size: 0.9em;
        }
        
        .badge-custom {
            font-size: 0.9em;
            padding: 0.5em 1em;
        }
        
        #qrcode {
            display: inline-block;
        }
        
        .qr-loading {
            text-align: center;
            padding: 40px;
            color: #6c757d;
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
                    <h2 class="mb-1">تفاصيل المشارك</h2>
                    <p class="text-muted">عرض معلومات المشارك والرمز المميز</p>
                </div>
                <div>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-right"></i> العودة للوحة التحكم
                    </a>
                    <button class="btn btn-primary" onclick="printParticipant()">
                        <i class="fas fa-print"></i> طباعة
                    </button>
                </div>
            </div>

            <div class="row">
                <!-- معلومات المشارك -->
                <div class="col-lg-8">
                    <div class="profile-card p-4 mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="mb-0">
                                <i class="fas fa-user"></i> المعلومات الشخصية
                            </h4>
                            <span class="badge bg-primary badge-custom">
                                <?= $participant_types[$participant['participant_type']] ?? $participant['participant_type'] ?>
                            </span>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-row">
                                    <div class="info-label">الاسم الكامل</div>
                                    <div class="info-value"><?= htmlspecialchars($participant['name']) ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">الرقم القومي</div>
                                    <div class="info-value"><?= htmlspecialchars($participant['national_id']) ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">المحافظة</div>
                                    <div class="info-value"><?= htmlspecialchars($participant['governorate']) ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">الجنس</div>
                                    <div class="info-value"><?= $gender_types[$participant['gender']] ?? $participant['gender'] ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">العمر</div>
                                    <div class="info-value"><?= $participant['age'] ?> سنة</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-row">
                                    <div class="info-label">رقم الهاتف</div>
                                    <div class="info-value"><?= htmlspecialchars($participant['phone']) ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">رقم الواتساب</div>
                                    <div class="info-value"><?= $participant['whatsapp'] ? htmlspecialchars($participant['whatsapp']) : 'غير محدد' ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">البريد الإلكتروني</div>
                                    <div class="info-value"><?= $participant['email'] ? htmlspecialchars($participant['email']) : 'غير محدد' ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">تاريخ التسجيل</div>
                                    <div class="info-value"><?= date('Y/m/d H:i', strtotime($participant['registration_date'])) ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">تأكيد التدريب</div>
                                    <div class="info-value">
                                        <?php if ($participant['training_confirmation']): ?>
                                            <span class="badge bg-success">مؤكد</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">غير مؤكد</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- معلومات إضافية حسب نوع المشارك -->
                        <?php if ($participant['participant_type'] === 'student'): ?>
                        <hr>
                        <h5 class="mb-3"><i class="fas fa-graduation-cap"></i> المعلومات التعليمية</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-row">
                                    <div class="info-label">الجامعة</div>
                                    <div class="info-value"><?= $participant['university'] ? htmlspecialchars($participant['university']) : 'غير محدد' ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-row">
                                    <div class="info-label">الكلية</div>
                                    <div class="info-value"><?= $participant['faculty'] ? htmlspecialchars($participant['faculty']) : 'غير محدد' ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-row">
                                    <div class="info-label">المرحلة الدراسية</div>
                                    <div class="info-value"><?= $participant['education_stage'] ? htmlspecialchars($participant['education_stage']) : 'غير محدد' ?></div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if ($participant['participant_type'] === 'employee'): ?>
                        <hr>
                        <h5 class="mb-3"><i class="fas fa-briefcase"></i> المعلومات المهنية</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-row">
                                    <div class="info-label">جهة العمل</div>
                                    <div class="info-value"><?= $participant['work_employer'] ? htmlspecialchars($participant['work_employer']) : 'غير محدد' ?></div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if ($participant['support_service']): ?>
                        <hr>
                        <h5 class="mb-3"><i class="fas fa-hand-holding-heart"></i> الخدمة المطلوبة</h5>
                        <div class="info-row">
                            <div class="info-value"><?= htmlspecialchars($participant['support_service']) ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- رمز QR والرمز المميز -->
                <div class="col-lg-4">
                    <div class="qr-card p-4">
                        <h4 class="mb-4">
                            <i class="fas fa-qrcode"></i> الرمز المميز
                        </h4>
                        
                        <!-- رمز QR -->
                        <div class="qr-code-container">
                            <div id="qrcode"></div>
                            <div class="qr-loading" id="qr-loading">
                                <i class="fas fa-spinner fa-spin fa-2x mb-2"></i>
                                <p>جاري إنشاء رمز QR...</p>
                            </div>
                        </div>

                        <!-- الرمز المميز -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">الرمز المميز:</label>
                            <div class="token-display" id="token-display">
                                <?= htmlspecialchars($qr_data) ?>
                            </div>
                        </div>

                        <!-- أزرار الإجراءات -->
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary" onclick="downloadQR()">
                                <i class="fas fa-download"></i> تحميل رمز QR
                            </button>
                            <button class="btn btn-success" onclick="copyToken()">
                                <i class="fas fa-copy"></i> نسخ الرمز
                            </button>
                            <button class="btn btn-info" onclick="shareParticipant()">
                                <i class="fas fa-share"></i> مشاركة
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../assets/qr/qrcode.min.js"></script>

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

        // بيانات QR للمشارك
        const qrData = <?= json_encode($qr_data) ?>;
        const participantName = '<?= htmlspecialchars($participant['name']) ?>';
        const participantId = <?= $participant['id'] ?>;

        // إنشاء رمز QR عند تحميل الصفحة
        document.addEventListener('DOMContentLoaded', function() {
            generateQRCode();
        });

        // إنشاء رمز QR
        function generateQRCode() {
            const qrcodeElement = document.getElementById('qrcode');
            const loadingElement = document.getElementById('qr-loading');
            
            // إخفاء رسالة التحميل
            loadingElement.style.display = 'none';
            
            // إنشاء رمز QR
            new QRCode(qrcodeElement, {
                text: qrData,
                width: 200,
                height: 200,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
        }

        // طباعة تفاصيل المشارك
        function printParticipant() {
            window.print();
        }

        // تحميل رمز QR
        function downloadQR() {
            const qrCanvas = document.querySelector('#qrcode canvas');
            if (qrCanvas) {
                const link = document.createElement('a');
                link.download = `qr-code-${participantId}-${participantName}.png`;
                link.href = qrCanvas.toDataURL();
                link.click();
            } else {
                Swal.fire('تنبيه', 'رمز QR غير متوفر', 'warning');
            }
        }

        // نسخ الرمز المميز
        function copyToken() {
            const token = qrData;
            if (token) {
                navigator.clipboard.writeText(token).then(() => {
                    Swal.fire('نجح', 'تم نسخ الرمز المميز', 'success');
                }).catch(() => {
                    // Fallback for older browsers
                    const textArea = document.createElement('textarea');
                    textArea.value = token;
                    document.body.appendChild(textArea);
                    textArea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textArea);
                    Swal.fire('نجح', 'تم نسخ الرمز المميز', 'success');
                });
            } else {
                Swal.fire('تنبيه', 'الرمز المميز غير متوفر', 'warning');
            }
        }

        // مشاركة المشارك
        function shareParticipant() {
            const participantData = {
                name: participantName,
                id: participantId,
                token: qrData
            };

            if (navigator.share) {
                navigator.share({
                    title: 'مشارك TIEC',
                    text: `مشارك: ${participantData.name}`,
                    url: window.location.href
                });
            } else {
                // Fallback for browsers that don't support Web Share API
                const shareText = `مشارك TIEC:\nالاسم: ${participantData.name}\nالرمز: ${participantData.token}`;
                navigator.clipboard.writeText(shareText).then(() => {
                    Swal.fire('نجح', 'تم نسخ معلومات المشارك', 'success');
                }).catch(() => {
                    // Fallback for older browsers
                    const textArea = document.createElement('textarea');
                    textArea.value = shareText;
                    document.body.appendChild(textArea);
                    textArea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textArea);
                    Swal.fire('نجح', 'تم نسخ معلومات المشارك', 'success');
                });
            }
        }
    </script>
</body>
</html> 