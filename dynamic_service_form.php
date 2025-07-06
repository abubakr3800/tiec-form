<?php
require_once 'config/database.php';

$service_id = isset($_GET['service_id']) ? intval($_GET['service_id']) : 0;
if (!$service_id) {
    die('يرجى تحديد الخدمة المطلوبة. مثال: ?service_id=1');
}

$pdo = getDBConnection();

// جلب اسم الخدمة
$stmt = $pdo->prepare("SELECT name_ar FROM services WHERE id = ?");
$stmt->execute([$service_id]);
$service = $stmt->fetch();
if (!$service) {
    die('الخدمة غير موجودة');
}

// جلب الأسئلة
$stmt = $pdo->prepare("SELECT * FROM service_questions WHERE service_id = ? AND is_active = 1 ORDER BY sort_order, id");
$stmt->execute([$service_id]);
$questions = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>نموذج خدمة: <?php echo htmlspecialchars($service['name_ar']); ?></title>
    <style>
        body { font-family: Tahoma, Arial, sans-serif; background: #f9f9f9; }
        .form-group { margin-bottom: 18px; }
        label { display: block; margin-bottom: 6px; font-weight: bold; }
        input, select, textarea { width: 100%; padding: 7px; border: 1px solid #ccc; border-radius: 4px; }
        .radio-group, .checkbox-group { display: flex; gap: 15px; }
        .radio-group label, .checkbox-group label { font-weight: normal; }
        button { padding: 10px 30px; background: #007bff; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>
    <h2>نموذج خدمة: <?php echo htmlspecialchars($service['name_ar']); ?></h2>
    <form method="post" enctype="multipart/form-data" action="submit_service_form.php">
        <input type="hidden" name="service_id" value="<?php echo $service_id; ?>">
        <?php foreach ($questions as $q): ?>
            <div class="form-group">
                <label><?php echo htmlspecialchars($q['question_text_ar']); ?><?php if ($q['is_required']) echo ' *'; ?></label>
                <?php
                switch ($q['question_type']) {
                    case 'text':
                        echo '<input type="text" name="q_' . $q['id'] . '"' . ($q['is_required'] ? ' required' : '') . '>';
                        break;
                    case 'textarea':
                        echo '<textarea name="q_' . $q['id'] . '"' . ($q['is_required'] ? ' required' : '') . '></textarea>';
                        break;
                    case 'number':
                        echo '<input type="number" name="q_' . $q['id'] . '"' . ($q['is_required'] ? ' required' : '') . '>';
                        break;
                    case 'date':
                        echo '<input type="date" name="q_' . $q['id'] . '"' . ($q['is_required'] ? ' required' : '') . '>';
                        break;
                    case 'time':
                        echo '<input type="time" name="q_' . $q['id'] . '"' . ($q['is_required'] ? ' required' : '') . '>';
                        break;
                    case 'file':
                        echo '<input type="file" name="q_' . $q['id'] . '"' . ($q['is_required'] ? ' required' : '') . '>';
                        break;
                    case 'select':
                        $opt_stmt = $pdo->prepare("SELECT * FROM question_options WHERE question_id = ? AND is_active = 1 ORDER BY sort_order, id");
                        $opt_stmt->execute([$q['id']]);
                        $options = $opt_stmt->fetchAll();
                        echo '<select name="q_' . $q['id'] . '"' . ($q['is_required'] ? ' required' : '') . '>';
                        echo '<option value="">اختر...</option>';
                        foreach ($options as $opt) {
                            echo '<option value="' . htmlspecialchars($opt['option_text_en']) . '">' . htmlspecialchars($opt['option_text_ar']) . '</option>';
                        }
                        echo '</select>';
                        break;
                    case 'radio':
                        $opt_stmt = $pdo->prepare("SELECT * FROM question_options WHERE question_id = ? AND is_active = 1 ORDER BY sort_order, id");
                        $opt_stmt->execute([$q['id']]);
                        $options = $opt_stmt->fetchAll();
                        echo '<div class="radio-group">';
                        foreach ($options as $opt) {
                            echo '<label><input type="radio" name="q_' . $q['id'] . '" value="' . htmlspecialchars($opt['option_text_en']) . '"' . ($q['is_required'] ? ' required' : '') . '> ' . htmlspecialchars($opt['option_text_ar']) . '</label>';
                        }
                        echo '</div>';
                        break;
                    case 'checkbox':
                        echo '<div class="checkbox-group">';
                        echo '<label><input type="checkbox" name="q_' . $q['id'] . '" value="1"' . ($q['is_required'] ? ' required' : '') . '> موافق</label>';
                        echo '</div>';
                        break;
                    case 'url':
                        echo '<input type="url" name="q_' . $q['id'] . '"' . ($q['is_required'] ? ' required' : '') . '>';
                        break;
                }
                ?>
            </div>
        <?php endforeach; ?>
        <button type="submit">إرسال</button>
    </form>
</body>
</html> 