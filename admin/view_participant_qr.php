<?php
session_start();
require_once '../cache/db.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// جلب معرف المشارك
$participant_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$participant_id) {
    header('Location: participants.php');
    exit();
}

try {
    // جلب بيانات المشارك مع معلومات الخدمة
    $stmt = $pdo->prepare("
        SELECT p.*, s.name as service_name, s.name_ar as service_name_ar 
        FROM participants p 
        LEFT JOIN services s ON p.service_id = s.id 
        WHERE p.id = ?
    ");
    $stmt->execute([$participant_id]);
    $participant = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$participant) {
        header('Location: participants.php');
        exit();
    }
    
    // إنشاء بيانات QR
    $qr_data = [
        'type' => 'participant',
        'id' => $participant['id'],
        'name' => $participant['name'],
        'email' => $participant['email'],
        'phone' => $participant['phone'],
        'service_id' => $participant['service_id'],
        'service_name' => $participant['service_name'],
        'token' => $participant['token'],
        'registration_date' => $participant['created_at'],
        'timestamp' => time()
    ];
    
    $qr_json = json_encode($qr_data);
    
} catch (Exception $e) {
    header('Location: participants.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code المشارك - لوحة التحكم</title>
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
        .participant-avatar {
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
        .token-badge {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 10px 15px;
            border-radius: 25px;
            font-family: monospace;
            font-size: 1.1em;
            margin: 10px 0;
            display: inline-block;
        }
        .service-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            display: inline-block;
            margin: 10px 0;
        }
        .qr-actions {
            margin-top: 20px;
        }
        .qr-actions .btn {
            margin: 5px;
        }
        .status-badge {
            font-size: 0.9em;
            padding: 8px 15px;
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
                        QR Code المشارك
                    </h2>
                    <a href="participants.php" class="back-btn">
                        <i class="fas fa-arrow-right"></i>
                        العودة للمشاركين
                    </a>
                </div>

                <!-- Participant Details Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="mb-0">
                            <i class="fas fa-user"></i>
                            معلومات المشارك
                        </h4>
                    </div>
                    <div class="card-body text-center">
                        <!-- Participant Avatar -->
                        <div class="participant-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        
                        <h4 class="mb-3"><?php echo htmlspecialchars($participant['name']); ?></h4>
                        
                        <!-- Token Display -->
                        <div class="token-badge">
                            <i class="fas fa-key"></i>
                            <?php echo htmlspecialchars($participant['token']); ?>
                        </div>
                        
                        <!-- Service Badge -->
                        <?php if ($participant['service_name']): ?>
                        <div class="service-badge">
                            <i class="fas fa-cogs"></i>
                            <?php echo htmlspecialchars($participant['service_name_ar'] ?: $participant['service_name']); ?>
                        </div>
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">البريد الإلكتروني:</div>
                                    <div class="info-value"><?php echo htmlspecialchars($participant['email']); ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">رقم الهاتف:</div>
                                    <div class="info-value"><?php echo htmlspecialchars($participant['phone']); ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">تاريخ التسجيل:</div>
                                    <div class="info-value"><?php echo date('Y-m-d H:i', strtotime($participant['created_at'])); ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">الحالة:</div>
                                    <div class="info-value">
                                        <?php if ($participant['training_confirmation']): ?>
                                            <span class="badge bg-success status-badge">مؤكد</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning status-badge">في الانتظار</span>
                                        <?php endif; ?>
                                    </div>
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
                            QR Code المشارك
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="qr-container">
                            <div id="qrcode"></div>
                            <p class="text-muted mt-3">
                                <i class="fas fa-info-circle"></i>
                                يمكن مسح هذا QR Code للوصول السريع لبيانات المشارك
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
            link.download = 'participant-qr-<?php echo $participant_id; ?>.png';
            link.href = canvas.toDataURL();
            link.click();
        }

        // Print QR Code
        function printQR() {
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                    <head>
                        <title>QR Code المشارك</title>
                        <style>
                            body { font-family: Arial, sans-serif; text-align: center; padding: 20px; }
                            .qr-container { margin: 20px; }
                        </style>
                    </head>
                    <body>
                        <h2>QR Code المشارك: <?php echo htmlspecialchars($participant['name']); ?></h2>
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
                    title: 'QR Code المشارك: <?php echo htmlspecialchars($participant['name']); ?>',
                    text: 'QR Code للمشارك <?php echo htmlspecialchars($participant['name']); ?>',
                    url: window.location.href
                });
            } else {
                // Fallback for browsers that don't support Web Share API
                const canvas = document.querySelector('#qrcode canvas');
                canvas.toBlob(function(blob) {
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = 'participant-qr-<?php echo $participant_id; ?>.png';
                    link.click();
                    URL.revokeObjectURL(url);
                });
            }
        }
    </script>
</body>
</html> 