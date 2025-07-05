<?php
require_once __DIR__ . '/../cache/db.php'; 
$userData = null;
if (!empty($_GET['token'])) {
    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
        $stmt = $pdo->prepare('SELECT * FROM participants WHERE user_token = ? LIMIT 1');
        $stmt->execute([$_GET['token']]);
        $userData = $stmt->fetch();
        $userData['user_token'] = md5($userData['user_token']);
        // $userData['cookie_token'] = md5($userData['cookie_token']);
        $_SESSION['user_token'] = $userData['user_token'];
        // $_SESSION['cookie_token'] = $userData['cookie_token'];
    } catch (Exception $e) {
        $userData = null;
    }
}
?><!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registration Success / تم التسجيل</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow text-center">
                <div class="card-header bg-success text-white">
                    <h4>تم التسجيل بنجاح / Registration Successful</h4>
                </div>
                <div class="card-body">
                    <?php if ($userData): ?>
                        <h5 class="mb-3">مرحباً، <?= htmlspecialchars($userData['full_name_ar']) ?>!</h5>
                        <p>احتفظ بهذا الكود لاستخدامه لاحقاً / Please keep this code for future use.</p>
                        <div class="mb-3" style="display: flex; justify-content: center; align-items: center;">
                            <div id="qrcode"></div>
                            <!-- <div>QR Code</div> -->
                            <!-- <img src="https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=<?= urlencode($userData['user_token']) ?>&choe=UTF-8" alt="QR Code" /> -->
                        </div>
                        <div class="alert alert-info">رمزك الفريد: <b><?= htmlspecialchars($userData['user_token']) ?></b></div>
                    <?php else: ?>
                        <div class="alert alert-danger">لم يتم العثور على التسجيل أو الرمز غير صحيح.<br>Registration not found or invalid token.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($userData): ?>
<script src="../assets/qr/qrcode.min.js"></script>
<script>
  var token = "<?= htmlspecialchars($userData['user_token']) ?>";
  if (token) {
    new QRCode(document.getElementById("qrcode"), {
      text: token,
      width: 200,
      height: 200
    });
  }
</script>
<?php endif; ?>
</body>
</html> 