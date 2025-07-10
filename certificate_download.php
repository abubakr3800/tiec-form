<?php
require 'vendor/autoload.php'; // dompdf

use Dompdf\Dompdf;
use Dompdf\Options;

// 1. استقبل الاسم من الرابط أو الفورم
$username = isset($_GET['username']) ? htmlspecialchars($_GET['username']) : '';

// 2. حمّل محتوى الـ SVG
$svg = file_get_contents('cert/Web Development.svg');

// 3. استبدل مكان الاسم في الـ SVG
// تأكد أن في SVG لديك: <tspan id="username">...</tspan>
$svg = preg_replace('/(<tspan[^>]*id="username"[^>]*>)[\s\S]*?(<\/tspan>)/', '$1' . $username . '$2', $svg);

// 4. ضع الـ SVG داخل HTML
$html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { margin: 0; padding: 0; }
        .cert-container { width: 100%; height: 100%; }
        svg { width: 100%; height: auto; }
    </style>
</head>
<body>
    <div class="cert-container">
        $svg
    </div>
</body>
</html>
HTML;

// 5. dompdf إعدادات
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

// 6. أرسل الملف للتحميل
$dompdf->stream('certificate.pdf', ['Attachment' => true]);
exit; 