<?php
// ملف مساعد للتنقل بين الصفحات
// يحتوي على روابط متناسقة لجميع صفحات النظام

function getNavigationLinks($current_page = '', $participant = null) {
    $links = [];
    
    // الصفحة الرئيسية - التسجيل الجديد
    $links['home'] = [
        'url' => 'index.php',
        'title' => 'الصفحة الرئيسية',
        'icon' => 'fas fa-home',
        'description' => 'تسجيل جديد في النظام'
    ];
    
    // تسجيل الدخول
    $links['login'] = [
        'url' => 'token_login.php',
        'title' => 'تسجيل الدخول',
        'icon' => 'fas fa-sign-in-alt',
        'description' => 'تسجيل الدخول بالرمز المميز'
    ];
    
    // الملف الشخصي (للمسجلين فقط)
    if ($participant) {
        $links['profile'] = [
            'url' => 'participant_profile.php',
            'title' => 'الملف الشخصي',
            'icon' => 'fas fa-user',
            'description' => 'عرض معلوماتي الشخصية'
        ];
        
        // تسجيل في خدمة جديدة
        $links['new_service'] = [
            'url' => 'register_new_service.php',
            'title' => 'تسجيل في خدمة جديدة',
            'icon' => 'fas fa-plus',
            'description' => 'إضافة خدمة جديدة'
        ];
        
        // تسجيلاتي
        $links['registrations'] = [
            'url' => 'view_my_registrations.php',
            'title' => 'تسجيلاتي',
            'icon' => 'fas fa-list',
            'description' => 'عرض جميع تسجيلاتي'
        ];
        
        // مولد الشهادات
        $links['certificate'] = [
            'url' => 'certificate_generator.php',
            'title' => 'إنشاء شهادة',
            'icon' => 'fas fa-certificate',
            'description' => 'إنشاء شهادة مشاركة'
        ];
        
        // تسجيل الحضور
        $links['attendance'] = [
            'url' => 'attendance_scanner.php',
            'title' => 'تسجيل الحضور',
            'icon' => 'fas fa-qrcode',
            'description' => 'مسح رمز QR للحضور'
        ];
        
        // تسجيل الخروج
        $links['logout'] = [
            'url' => 'logout.php',
            'title' => 'تسجيل خروج',
            'icon' => 'fas fa-sign-out-alt',
            'description' => 'الخروج من النظام'
        ];
    }
    
    return $links;
}

function renderNavigationBar($current_page = '', $participant = null) {
    $links = getNavigationLinks($current_page, $participant);
    
    echo '<nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(45deg, #667eea, #764ba2);">';
    echo '<div class="container">';
    echo '<a class="navbar-brand" href="index.php">';
    echo '<i class="fas fa-graduation-cap"></i> TIEC';
    echo '</a>';
    
    echo '<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">';
    echo '<span class="navbar-toggler-icon"></span>';
    echo '</button>';
    
    echo '<div class="collapse navbar-collapse" id="navbarNav">';
    echo '<ul class="navbar-nav me-auto">';
    
    foreach ($links as $key => $link) {
        if ($key === 'logout') continue; // سيتم عرضه في الجانب الأيمن
        
        $active_class = ($current_page === $key) ? 'active' : '';
        echo '<li class="nav-item">';
        echo '<a class="nav-link ' . $active_class . '" href="' . $link['url'] . '" title="' . $link['description'] . '">';
        echo '<i class="' . $link['icon'] . '"></i> ' . $link['title'];
        echo '</a>';
        echo '</li>';
    }
    
    echo '</ul>';
    
    // بيانات المشارك في أقصى اليمين
    if ($participant) {
        $firstLetter = mb_substr($participant['name'], 0, 1, 'UTF-8');
        echo '<ul class="navbar-nav ms-auto">';
        echo '<li class="nav-item dropdown">';
        echo '<a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">';
        // صورة رمزية دائرية
        echo '<span class="avatar rounded-circle bg-light text-primary fw-bold d-inline-flex align-items-center justify-content-center me-2" style="width:36px;height:36px;font-size:1.2rem;">' . htmlspecialchars($firstLetter) . '</span>';
        // الاسم
        echo '<span class="fw-bold">' . htmlspecialchars($participant['name']) . '</span>';
        echo '</a>';
        echo '<ul class="dropdown-menu dropdown-menu-end text-end" aria-labelledby="userDropdown">';
        echo '<li><a class="dropdown-item" href="participant_profile.php"><i class="fas fa-user me-2"></i> الملف الشخصي</a></li>';
        echo '<li><a class="dropdown-item" href="dashboard.php"><i class="fas fa-tachometer-alt me-2"></i> لوحة التحكم</a></li>';
        echo '<li><a class="dropdown-item" href="view_my_registrations.php"><i class="fas fa-list me-2"></i> تسجيلاتي</a></li>';
        echo '<li><a class="dropdown-item" href="register_new_service.php"><i class="fas fa-plus me-2"></i> خدمة جديدة</a></li>';
        echo '<li><a class="dropdown-item" href="certificate_generator.php"><i class="fas fa-certificate me-2"></i> شهادة</a></li>';
        echo '<li><hr class="dropdown-divider"></li>';
        echo '<li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> تسجيل خروج</a></li>';
        echo '</ul>';
        echo '</li>';
        echo '</ul>';
    }
    
    echo '</div>';
    echo '</div>';
    echo '</nav>';
}

function renderQuickActions($participant = null) {
    if (!$participant) return;
    
    echo '<div class="quick-actions mb-4">';
    echo '<div class="row">';
    
    $actions = [
        [
            'url' => 'participant_profile.php',
            'title' => 'الملف الشخصي',
            'icon' => 'fas fa-user',
            'color' => 'primary'
        ],
        [
            'url' => 'register_new_service.php',
            'title' => 'خدمة جديدة',
            'icon' => 'fas fa-plus',
            'color' => 'success'
        ],
        [
            'url' => 'view_my_registrations.php',
            'title' => 'تسجيلاتي',
            'icon' => 'fas fa-list',
            'color' => 'info'
        ],
        [
            'url' => 'certificate_generator.php',
            'title' => 'إنشاء شهادة',
            'icon' => 'fas fa-certificate',
            'color' => 'warning'
        ]
    ];
    
    foreach ($actions as $action) {
        echo '<div class="col-md-3 mb-2">';
        echo '<a href="' . $action['url'] . '" class="btn btn-' . $action['color'] . ' w-100">';
        echo '<i class="' . $action['icon'] . '"></i> ' . $action['title'];
        echo '</a>';
        echo '</div>';
    }
    
    echo '</div>';
    echo '</div>';
}

function renderBreadcrumb($pages) {
    echo '<nav aria-label="breadcrumb" class="mb-4">';
    echo '<ol class="breadcrumb">';
    
    foreach ($pages as $index => $page) {
        if ($index === count($pages) - 1) {
            echo '<li class="breadcrumb-item active" aria-current="page">' . $page['title'] . '</li>';
        } else {
            echo '<li class="breadcrumb-item"><a href="' . $page['url'] . '">' . $page['title'] . '</a></li>';
        }
    }
    
    echo '</ol>';
    echo '</nav>';
}

function renderWelcomeMessage($participant = null) {
    if (!$participant) return;
    
    echo '<div class="welcome-message mb-4">';
    echo '<div class="alert alert-success">';
    echo '<h5><i class="fas fa-user-check"></i> مرحباً ' . htmlspecialchars($participant['name']) . '</h5>';
    echo '<p class="mb-0">يمكنك الآن تصفح جميع خدمات النظام</p>';
    echo '</div>';
    echo '</div>';
}

function renderServiceStatus($participant_id) {
    // جلب إحصائيات سريعة
    global $pdo;
    
    try {
        // عدد التسجيلات
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE participant_id = ?");
        $stmt->execute([$participant_id]);
        $total_registrations = $stmt->fetchColumn();
        
        // عدد الحضور
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM attendance a 
            JOIN registrations r ON a.registration_id = r.id 
            WHERE r.participant_id = ?
        ");
        $stmt->execute([$participant_id]);
        $total_attendance = $stmt->fetchColumn();
        
        echo '<div class="status-cards mb-4">';
        echo '<div class="row">';
        echo '<div class="col-md-6">';
        echo '<div class="card text-center">';
        echo '<div class="card-body">';
        echo '<h3 class="text-primary">' . $total_registrations . '</h3>';
        echo '<p class="card-text">إجمالي التسجيلات</p>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '<div class="col-md-6">';
        echo '<div class="card text-center">';
        echo '<div class="card-body">';
        echo '<h3 class="text-success">' . $total_attendance . '</h3>';
        echo '<p class="card-text">إجمالي الحضور</p>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        
    } catch (Exception $e) {
        // تجاهل الأخطاء في حالة عدم وجود قاعدة بيانات
    }
}

function getPageTitle($page) {
    $titles = [
        'home' => 'الصفحة الرئيسية - TIEC',
        'login' => 'تسجيل الدخول - TIEC',
        'profile' => 'الملف الشخصي - TIEC',
        'new_service' => 'تسجيل في خدمة جديدة - TIEC',
        'registrations' => 'تسجيلاتي - TIEC',
        'certificate' => 'إنشاء شهادة - TIEC',
        'attendance' => 'تسجيل الحضور - TIEC'
    ];
    
    return $titles[$page] ?? 'TIEC';
}

function getPageDescription($page) {
    $descriptions = [
        'home' => 'سجل الآن في خدمات TIEC المتميزة',
        'login' => 'تسجيل الدخول بالرمز المميز',
        'profile' => 'عرض وإدارة الملف الشخصي',
        'new_service' => 'إضافة خدمة جديدة لحسابك',
        'registrations' => 'عرض جميع تسجيلاتك',
        'certificate' => 'إنشاء شهادة مشاركة',
        'attendance' => 'تسجيل الحضور للتدريبات'
    ];
    
    return $descriptions[$page] ?? 'نظام TIEC';
}
?> 