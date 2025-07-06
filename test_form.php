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
    <title>اختبار النموذج - <?php echo htmlspecialchars($service['name_ar']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: Tahoma, Arial, sans-serif; background: #f9f9f9; }
        .form-group { margin-bottom: 18px; }
        label { display: block; margin-bottom: 6px; font-weight: bold; }
        .radio-group, .checkbox-group { display: flex; gap: 15px; }
        .radio-group label, .checkbox-group label { font-weight: normal; }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h3>اختبار النموذج: <?php echo htmlspecialchars($service['name_ar']); ?></h3>
                    </div>
                    <div class="card-body">
                        <form method="post" enctype="multipart/form-data">
                            <input type="hidden" name="service_id" value="<?php echo $service_id; ?>">
                            
                            <?php foreach ($questions as $q): ?>
                                <div class="form-group">
                                    <label><?php echo htmlspecialchars($q['question_text_ar']); ?><?php if ($q['is_required']) echo ' *'; ?></label>
                                    
                                    <?php
                                    switch ($q['question_type']) {
                                        case 'text':
                                            echo '<input type="text" class="form-control" name="question_' . $q['id'] . '"' . ($q['is_required'] ? ' required' : '') . '>';
                                            break;
                                            
                                        case 'textarea':
                                            echo '<textarea class="form-control" name="question_' . $q['id'] . '" rows="3"' . ($q['is_required'] ? ' required' : '') . '></textarea>';
                                            break;
                                            
                                        case 'number':
                                            echo '<input type="number" class="form-control" name="question_' . $q['id'] . '"' . ($q['is_required'] ? ' required' : '') . '>';
                                            break;
                                            
                                        case 'date':
                                            echo '<input type="date" class="form-control" name="question_' . $q['id'] . '"' . ($q['is_required'] ? ' required' : '') . '>';
                                            break;
                                            
                                        case 'time':
                                            echo '<input type="time" class="form-control" name="question_' . $q['id'] . '"' . ($q['is_required'] ? ' required' : '') . '>';
                                            break;
                                            
                                        case 'file':
                                            echo '<input type="file" class="form-control" name="question_' . $q['id'] . '"' . ($q['is_required'] ? ' required' : '') . '>';
                                            break;
                                            
                                        case 'url':
                                            echo '<input type="url" class="form-control" name="question_' . $q['id'] . '" placeholder="https://example.com"' . ($q['is_required'] ? ' required' : '') . '>';
                                            break;
                                            
                                        case 'checkbox':
                                            echo '<div class="checkbox-group">';
                                            echo '<label><input type="checkbox" name="question_' . $q['id'] . '" value="1"' . ($q['is_required'] ? ' required' : '') . '> موافق</label>';
                                            echo '</div>';
                                            break;
                                            
                                        case 'select':
                                            $opt_stmt = $pdo->prepare("SELECT * FROM question_options WHERE question_id = ? AND is_active = 1 ORDER BY sort_order");
                                            $opt_stmt->execute([$q['id']]);
                                            $options = $opt_stmt->fetchAll();
                                            echo '<select class="form-select" name="question_' . $q['id'] . '"' . ($q['is_required'] ? ' required' : '') . '>';
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
                                            echo '<input type="text" class="form-control" name="question_' . $q['id'] . '"' . ($q['is_required'] ? ' required' : '') . '>';
                                            break;
                                    }
                                    ?>
                                </div>
                            <?php endforeach; ?>
                            
                            <button type="submit" class="btn btn-primary">إرسال</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 