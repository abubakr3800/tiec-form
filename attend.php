<?php
session_start();
require_once 'cache/db.php';

// التحقق من وجود token في cookies
$participant_token = isset($_COOKIE['participant_token']) ? $_COOKIE['participant_token'] : null;
$participant_data = null;

if ($participant_token) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM participants WHERE token = ?");
        $stmt->execute([$participant_token]);
        $participant_data = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // Handle error silently
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الحضور - TIEC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .container {
            padding-top: 2rem;
            padding-bottom: 2rem;
        }
        
        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }
        
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 20px 20px 0 0 !important;
            padding: 1.5rem;
            text-align: center;
        }
        
        .qr-container {
            text-align: center;
            padding: 2rem;
        }
        
        #reader {
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
            border-radius: 15px;
            overflow: hidden;
        }
        
        .participant-info {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 1rem;
            border-radius: 15px;
            margin: 1rem 0;
        }
        
        .training-info {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            padding: 1rem;
            border-radius: 15px;
            margin: 1rem 0;
        }
        
        .error-message {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            padding: 1rem;
            border-radius: 15px;
            margin: 1rem 0;
        }
        
        .btn-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            color: white;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            color: white;
        }
        
        .status-badge {
            font-size: 0.9em;
            padding: 8px 15px;
            border-radius: 20px;
        }
        
        .loading {
            display: none;
            text-align: center;
            padding: 2rem;
        }
        
        .spinner-border {
            width: 3rem;
            height: 3rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Header Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h2 class="mb-0">
                            <i class="fas fa-qrcode"></i>
                            نظام تسجيل الحضور
                        </h2>
                        <p class="mb-0 mt-2">قم بمسح QR Code للمشارك لتسجيل الحضور</p>
                    </div>
                </div>

                <!-- QR Scanner Card -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="qr-container">
                            <div id="reader"></div>
                        </div>
                        
                        <!-- Loading -->
                        <div class="loading" id="loading">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">جاري التحميل...</span>
                            </div>
                            <p class="mt-3">جاري تسجيل الحضور...</p>
                        </div>
                        
                        <!-- Results -->
                        <div id="results"></div>
                    </div>
                </div>

                <!-- Current Participant Info (if available) -->
                <?php if ($participant_data): ?>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-user"></i>
                            معلومات المشارك الحالي
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="participant-info">
                            <h6><i class="fas fa-user"></i> <?php echo htmlspecialchars($participant_data['name']); ?></h6>
                            <p class="mb-1"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($participant_data['email']); ?></p>
                            <p class="mb-0"><i class="fas fa-phone"></i> <?php echo htmlspecialchars($participant_data['phone']); ?></p>
                        </div>
                        
                        <!-- Check current trainings -->
                        <div id="current-trainings">
                            <h6 class="mt-3"><i class="fas fa-calendar"></i> التدريبات الحالية:</h6>
                            <div id="trainings-list"></div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        let html5QrcodeScanner = null;
        let isProcessing = false;

        // Initialize QR Scanner
        function initScanner() {
            html5QrcodeScanner = new Html5QrcodeScanner(
                "reader", 
                { 
                    fps: 10, 
                    qrbox: { width: 250, height: 250 },
                    aspectRatio: 1.0
                },
                false
            );
            
            html5QrcodeScanner.render(onScanSuccess, onScanFailure);
        }

        // Handle successful scan
        function onScanSuccess(decodedText, decodedResult) {
            if (isProcessing) return;
            
            isProcessing = true;
            showLoading();
            
            try {
                // Parse QR data
                const qrData = JSON.parse(decodedText);
                
                if (qrData.type === 'participant' && qrData.token) {
                    registerAttendance(qrData.token);
                } else {
                    showError('QR Code غير صالح');
                }
            } catch (error) {
                showError('QR Code غير صالح');
            }
        }

        // Handle scan failure
        function onScanFailure(error) {
            // Handle scan failure silently
        }

        // Register attendance
        function registerAttendance(token) {
            fetch('register_attendance.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ token: token })
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                isProcessing = false;
                
                if (data.success) {
                    showSuccess(data.message, data.training_info);
                } else {
                    showError(data.message);
                }
            })
            .catch(error => {
                hideLoading();
                isProcessing = false;
                showError('حدث خطأ في الاتصال');
            });
        }

        // Show loading
        function showLoading() {
            document.getElementById('loading').style.display = 'block';
            document.getElementById('results').innerHTML = '';
        }

        // Hide loading
        function hideLoading() {
            document.getElementById('loading').style.display = 'none';
        }

        // Show success message
        function showSuccess(message, trainingInfo = null) {
            let html = `
                <div class="alert alert-success">
                    <h5><i class="fas fa-check-circle"></i> تم تسجيل الحضور بنجاح</h5>
                    <p>${message}</p>
            `;
            
            if (trainingInfo) {
                html += `
                    <div class="training-info">
                        <h6><i class="fas fa-graduation-cap"></i> معلومات التدريب:</h6>
                        <p><strong>التدريب:</strong> ${trainingInfo.title}</p>
                        <p><strong>التاريخ:</strong> ${trainingInfo.date}</p>
                        <p><strong>الوقت:</strong> ${trainingInfo.time}</p>
                        <p><strong>المدرب:</strong> ${trainingInfo.trainer}</p>
                    </div>
                `;
            }
            
            html += '</div>';
            document.getElementById('results').innerHTML = html;
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                document.getElementById('results').innerHTML = '';
            }, 5000);
        }

        // Show error message
        function showError(message) {
            const html = `
                <div class="alert alert-danger">
                    <h5><i class="fas fa-exclamation-triangle"></i> خطأ</h5>
                    <p>${message}</p>
                    <button class="btn btn-custom mt-2" onclick="contactAdmin()">
                        <i class="fas fa-phone"></i> اتصل بالمشرف
                    </button>
                </div>
            `;
            document.getElementById('results').innerHTML = html;
        }

        // Contact admin function
        function contactAdmin() {
            Swal.fire({
                title: 'اتصل بالمشرف',
                text: 'يرجى الاتصال بالمشرف لتسجيلك في التدريب',
                icon: 'info',
                confirmButtonText: 'حسناً',
                confirmButtonColor: '#667eea'
            });
        }

        // Load current participant trainings
        <?php if ($participant_data): ?>
        function loadCurrentTrainings() {
            fetch(`get_participant_trainings.php?token=${encodeURIComponent('<?php echo $participant_data['token']; ?>')}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.trainings.length > 0) {
                        let html = '';
                        data.trainings.forEach(training => {
                            html += `
                                <div class="training-info">
                                    <h6>${training.title}</h6>
                                    <p><strong>التاريخ:</strong> ${training.start_date} - ${training.end_date}</p>
                                    <p><strong>الوقت:</strong> ${training.start_time} - ${training.end_time}</p>
                                    <p><strong>المدرب:</strong> ${training.trainer_name}</p>
                                    <span class="badge bg-success status-badge">${training.status}</span>
                                </div>
                            `;
                        });
                        document.getElementById('trainings-list').innerHTML = html;
                    } else {
                        document.getElementById('trainings-list').innerHTML = '<p class="text-muted">لا توجد تدريبات مسجلة حالياً</p>';
                    }
                })
                .catch(error => {
                    document.getElementById('trainings-list').innerHTML = '<p class="text-danger">خطأ في تحميل التدريبات</p>';
                });
        }
        
        // Load trainings on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadCurrentTrainings();
        });
        <?php endif; ?>

        // Initialize scanner when page loads
        document.addEventListener('DOMContentLoaded', function() {
            initScanner();
        });
    </script>
</body>
</html> 