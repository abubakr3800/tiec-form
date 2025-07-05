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
    
    // إنشاء بيانات QR
    $qr_data = [
        'type' => 'admin',
        'id' => $admin['id'],
        'name' => $admin['name'],
        'username' => $admin['username'],
        'email' => $admin['email'],
        'role' => $admin['role'],
        'timestamp' => time()
    ];
    
    $qr_json = json_encode($qr_data);
    
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
    <title>QR Code المشرف - لوحة التحكم</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
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
        .qr-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin: 20px 0;
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
        .role-badge {
            font-size: 0.9em;
            padding: 8px 15px;
        }
        .qr-actions {
            margin-top: 20px;
        }
        .qr-actions .btn {
            margin: 5px;
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
                        <i class="fas fa-qrcode text-primary"></i>
                        QR Code المشرف
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
                                    <div class="info-label">تاريخ الإنشاء:</div>
                                    <div class="info-value"><?php echo date('Y-m-d H:i', strtotime($admin['created_at'])); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- QR Code Card -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-qrcode"></i>
                            QR Code المشرف
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="qr-container">
                            <div id="qrcode"></div>
                            <p class="text-muted mt-3">
                                <i class="fas fa-info-circle"></i>
                                يمكن مسح هذا QR Code للوصول السريع لبيانات المشرف
                            </p>
                        </div>
                        
                        <div class="qr-actions text-center">
                            <button class="btn btn-primary" onclick="downloadQR()">
                                <i class="fas fa-download"></i>
                                تحميل QR Code
                            </button>
                            <button class="btn btn-success" onclick="printQR()">
                                <i class="fas fa-print"></i>
                                طباعة QR Code
                            </button>
                            <button class="btn btn-info" onclick="shareQR()">
                                <i class="fas fa-share"></i>
                                مشاركة QR Code
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Generate QR Code
        const qrData = <?php echo json_encode($qr_json); ?>;
        
        QRCode.toCanvas(document.getElementById('qrcode'), qrData, {
            width: 200,
            margin: 2,
            color: {
                dark: '#000000',
                light: '#FFFFFF'
            }
        }, function (error) {
            if (error) console.error(error);
        });

        // Download QR Code
        function downloadQR() {
            const canvas = document.querySelector('#qrcode canvas');
            const link = document.createElement('a');
            link.download = 'admin-qr-<?php echo $admin_id; ?>.png';
            link.href = canvas.toDataURL();
            link.click();
        }

        // Print QR Code
        function printQR() {
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                    <head>
                        <title>QR Code المشرف</title>
                        <style>
                            body { font-family: Arial, sans-serif; text-align: center; padding: 20px; }
                            .qr-container { margin: 20px; }
                        </style>
                    </head>
                    <body>
                        <h2>QR Code المشرف: <?php echo htmlspecialchars($admin['name']); ?></h2>
                        <div class="qr-container">
                            <div id="qrcode"></div>
                        </div>
                        <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
                        <script>
                            QRCode.toCanvas(document.getElementById('qrcode'), '${qrData}', {
                                width: 300,
                                margin: 2
                            }, function (error) {
                                if (error) console.error(error);
                                else window.print();
                            });
                        </script>
                    </body>
                </html>
            `);
        }

        // Share QR Code
        function shareQR() {
            if (navigator.share) {
                navigator.share({
                    title: 'QR Code المشرف: <?php echo htmlspecialchars($admin['name']); ?>',
                    text: 'QR Code للمشرف <?php echo htmlspecialchars($admin['name']); ?>',
                    url: window.location.href
                });
            } else {
                // Fallback for browsers that don't support Web Share API
                const canvas = document.querySelector('#qrcode canvas');
                canvas.toBlob(function(blob) {
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = 'admin-qr-<?php echo $admin_id; ?>.png';
                    link.click();
                    URL.revokeObjectURL(url);
                });
            }
        }
    </script>
</body>
</html> 