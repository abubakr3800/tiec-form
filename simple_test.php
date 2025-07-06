<?php
require_once 'config/database.php';

$service_id = isset($_GET['service_id']) ? intval($_GET['service_id']) : 1;
$pdo = getDBConnection();

// جلب اسم الخدمة
$stmt = $pdo->prepare("SELECT name_ar FROM services WHERE id = ?");
$stmt->execute([$service_id]);
$service = $stmt->fetch();

// جلب الأسئلة
$stmt = $pdo->prepare("SELECT * FROM service_questions WHERE service_id = ? AND is_active = 1 ORDER BY sort_order");
$stmt->execute([$service_id]);
$questions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>اختبار النموذج</title>
    <style>
        body { font-family: Tahoma, Arial, sans-serif; margin: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select, textarea { width: 100%; padding: 8px; margin-bottom: 10px; }
        .radio-group { margin-bottom: 10px; }
        .radio-group label { display: inline-block; margin-right: 15px; font-weight: normal; }
    </style>
</head>
<body>
    <h2>اختبار النموذج: <?php echo htmlspecialchars($service['name_ar']); ?></h2>
    
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="service_id" value="<?php echo $service_id; ?>">
        
        <?php foreach ($questions as $q): ?>
            <div class="form-group">
                <label><?php echo htmlspecialchars($q['question_text_ar']); ?><?php if ($q['is_required']) echo ' *'; ?></label>
                
                <?php
                switch ($q['question_type']) {
                    case 'text':
                        echo '<input type="text" name="question_' . $q['id'] . '"' . ($q['is_required'] ? ' required' : '') . '>';
                        break;
                        
                    case 'textarea':
                        echo '<textarea name="question_' . $q['id'] . '" rows="3"' . ($q['is_required'] ? ' required' : '') . '></textarea>';
                        break;
                        
                    case 'number':
                        echo '<input type="number" name="question_' . $q['id'] . '"' . ($q['is_required'] ? ' required' : '') . '>';
                        break;
                        
                    case 'date':
                        echo '<input type="date" name="question_' . $q['id'] . '"' . ($q['is_required'] ? ' required' : '') . '>';
                        break;
                        
                    case 'time':
                        echo '<input type="time" name="question_' . $q['id'] . '"' . ($q['is_required'] ? ' required' : '') . '>';
                        break;
                        
                    case 'file':
                        echo '<input type="file" name="question_' . $q['id'] . '"' . ($q['is_required'] ? ' required' : '') . '>';
                        break;
                        
                    case 'url':
                        echo '<input type="url" name="question_' . $q['id'] . '" placeholder="https://example.com"' . ($q['is_required'] ? ' required' : '') . '>';
                        break;
                        
                    case 'checkbox':
                        echo '<label><input type="checkbox" name="question_' . $q['id'] . '" value="1"' . ($q['is_required'] ? ' required' : '') . '> موافق</label>';
                        break;
                        
                    case 'select':
                        $opt_stmt = $pdo->prepare("SELECT * FROM question_options WHERE question_id = ? AND is_active = 1 ORDER BY sort_order");
                        $opt_stmt->execute([$q['id']]);
                        $options = $opt_stmt->fetchAll();
                        echo '<select name="question_' . $q['id'] . '"' . ($q['is_required'] ? ' required' : '') . '>';
                        echo '<option value="">اختر...</option>';
                        foreach ($options as $opt) {
                            echo '<option value="' . htmlspecialchars($opt['option_text_en']) . '">' . htmlspecialchars($opt['option_text_ar']) . '</option>';
                        }
                        echo '</select>';
                        break;
                        
                    case 'radio':
                        $opt_stmt = $pdo->prepare("SELECT * FROM question_options WHERE question_id = ? AND is_active = 1 ORDER BY sort_order");
                        $opt_stmt->execute([$q['id']]);
                        $options = $opt_stmt->fetchAll();
                        echo '<div class="radio-group">';
                        foreach ($options as $opt) {
                            echo '<label><input type="radio" name="question_' . $q['id'] . '" value="' . htmlspecialchars($opt['option_text_en']) . '"' . ($q['is_required'] ? ' required' : '') . '> ' . htmlspecialchars($opt['option_text_ar']) . '</label>';
                        }
                        echo '</div>';
                        break;
                        
                    default:
                        echo '<input type="text" name="question_' . $q['id'] . '"' . ($q['is_required'] ? ' required' : '') . '>';
                        break;
                }
                ?>
            </div>
        <?php endforeach; ?>
        
        <button type="submit">إرسال</button>
    </form>
    
    <hr>
    <h3>اختبار الخدمات المختلفة:</h3>
    <ul>
        <li><a href="?service_id=10">حجز مساحة عمل حرة</a></li>
        <li><a href="?service_id=11">مختبر التصنيع الرقمي</a></li>
        <li><a href="?service_id=12">خدمات الإرشاد</a></li>
        <li><a href="?service_id=13">التدريب المتخصص</a></li>
    </ul>
</body>
</html> 