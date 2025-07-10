<?php
require 'vendor/autoload.php';

use Knp\Snappy\Pdf;

// 1. استقبل الاسم من الرابط أو الفورم
$username = isset($_GET['username']) ? htmlspecialchars($_GET['username']) : '';

// 2. حمّل محتوى الـ SVG
$svg = file_get_contents('cert/Web Development.svg');

// 3. استبدل مكان الاسم في الـ SVG
$svg = preg_replace('/(<tspan[^>]*id="username"[^>]*>)[\s\S]*?(<\/tspan>)/', '$1' . $username . '$2', $svg);

// 4. ضع الـ SVG داخل HTML
$html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { margin: 0; padding: 0; }
        .cert-container { width: 100vw; height: 100vh; }
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

// 5. كشف تلقائي لمسار wkhtmltopdf
$wkhtmltopdfPaths = [
    // Windows default
    'C:/Program Files/wkhtmltopdf/bin/wkhtmltopdf.exe',
    'C:/Program Files (x86)/wkhtmltopdf/bin/wkhtmltopdf.exe',
    // Linux default
    '/usr/local/bin/wkhtmltopdf',
    '/usr/bin/wkhtmltopdf',
    // PATH
    'wkhtmltopdf',
];
$wkhtmltopdf = null;
foreach ($wkhtmltopdfPaths as $path) {
    if ($path === 'wkhtmltopdf') {
        // Assume in PATH
        $wkhtmltopdf = $path;
        break;
    } elseif (file_exists($path)) {
        $wkhtmltopdf = $path;
        break;
    }
}
if (!$wkhtmltopdf) {
    die('wkhtmltopdf executable not found. Please install wkhtmltopdf and set the correct path.');
}

// 6. إعداد snappy
$snappy = new Pdf($wkhtmltopdf);

// 7. أرسل الملف للتحميل
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="certificate.pdf"');
echo $snappy->getOutputFromHtml($html);
exit; 