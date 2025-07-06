<?php
require_once 'config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'طريقة طلب غير صحيحة']);
    exit();
}

if (empty($_POST['service_id'])) {
    echo json_encode(['success' => false, 'message' => 'معرف الخدمة مطلوب']);
    exit();
}

try {
    $pdo = getDBConnection();
    $service_id = (int)$_POST['service_id'];
    
    // جلب الأسئلة للخدمة المحددة
    $stmt = $pdo->prepare("
        SELECT * FROM service_questions 
        WHERE service_id = ? AND is_active = 1 
        ORDER BY sort_order
    ");
    $stmt->execute([$service_id]);
    $questions = $stmt->fetchAll();
    
    if (empty($questions)) {
        echo json_encode(['success' => false, 'message' => 'لا توجد أسئلة لهذه الخدمة']);
        exit();
    }
    
    $html = '';
    
    foreach ($questions as $question) {
        $html .= '<div class="col-12 mb-3">';
        $html .= '<label for="question_' . $question['id'] . '" class="form-label">';
        $html .= htmlspecialchars($question['question_text_ar']);
        if ($question['is_required']) {
            $html .= ' *';
        }
        $html .= '</label>';
        
        switch ($question['question_type']) {
            case 'text':
                $html .= '<input type="text" class="form-control" id="question_' . $question['id'] . '" ';
                $html .= 'name="question_' . $question['id'] . '"';
                if ($question['is_required']) {
                    $html .= ' required';
                }
                $html .= '>';
                break;
                
            case 'textarea':
                $html .= '<textarea class="form-control" id="question_' . $question['id'] . '" ';
                $html .= 'name="question_' . $question['id'] . '" rows="3"';
                if ($question['is_required']) {
                    $html .= ' required';
                }
                $html .= '></textarea>';
                break;
                
            case 'number':
                $html .= '<input type="number" class="form-control" id="question_' . $question['id'] . '" ';
                $html .= 'name="question_' . $question['id'] . '"';
                if ($question['is_required']) {
                    $html .= ' required';
                }
                $html .= '>';
                break;
                
            case 'date':
                $html .= '<input type="date" class="form-control" id="question_' . $question['id'] . '" ';
                $html .= 'name="question_' . $question['id'] . '"';
                if ($question['is_required']) {
                    $html .= ' required';
                }
                $html .= '>';
                break;
                
            case 'time':
                $html .= '<input type="time" class="form-control" id="question_' . $question['id'] . '" ';
                $html .= 'name="question_' . $question['id'] . '"';
                if ($question['is_required']) {
                    $html .= ' required';
                }
                $html .= '>';
                break;
                
            case 'file':
                $html .= '<input type="file" class="form-control" id="question_' . $question['id'] . '" ';
                $html .= 'name="question_' . $question['id'] . '"';
                if ($question['is_required']) {
                    $html .= ' required';
                }
                $html .= '>';
                break;
                
            case 'url':
                $html .= '<input type="url" class="form-control" id="question_' . $question['id'] . '" ';
                $html .= 'name="question_' . $question['id'] . '" placeholder="https://example.com"';
                if ($question['is_required']) {
                    $html .= ' required';
                }
                $html .= '>';
                break;
                
            case 'checkbox':
                $html .= '<div class="form-check">';
                $html .= '<input class="form-check-input" type="checkbox" ';
                $html .= 'id="question_' . $question['id'] . '" ';
                $html .= 'name="question_' . $question['id'] . '" value="1"';
                if ($question['is_required']) {
                    $html .= ' required';
                }
                $html .= '>';
                $html .= '<label class="form-check-label" for="question_' . $question['id'] . '">';
                $html .= 'موافق';
                $html .= '</label>';
                $html .= '</div>';
                break;
                
            case 'select':
            case 'radio':
                // جلب خيارات السؤال
                $options_stmt = $pdo->prepare("
                    SELECT * FROM question_options 
                    WHERE question_id = ? AND is_active = 1 
                    ORDER BY sort_order
                ");
                $options_stmt->execute([$question['id']]);
                $options = $options_stmt->fetchAll();
                
                if ($question['question_type'] === 'select') {
                    $html .= '<select class="form-select" id="question_' . $question['id'] . '" ';
                    $html .= 'name="question_' . $question['id'] . '"';
                    if ($question['is_required']) {
                        $html .= ' required';
                    }
                    $html .= '>';
                    $html .= '<option value="">اختر...</option>';
                    
                    foreach ($options as $option) {
                        $html .= '<option value="' . htmlspecialchars($option['option_text_en']) . '">';
                        $html .= htmlspecialchars($option['option_text_ar']);
                        $html .= '</option>';
                    }
                    
                    $html .= '</select>';
                } else { // radio
                    foreach ($options as $option) {
                        $html .= '<div class="form-check">';
                        $html .= '<input class="form-check-input" type="radio" ';
                        $html .= 'id="option_' . $option['id'] . '" ';
                        $html .= 'name="question_' . $question['id'] . '" ';
                        $html .= 'value="' . htmlspecialchars($option['option_text_en']) . '"';
                        if ($question['is_required']) {
                            $html .= ' required';
                        }
                        $html .= '>';
                        $html .= '<label class="form-check-label" for="option_' . $option['id'] . '">';
                        $html .= htmlspecialchars($option['option_text_ar']);
                        $html .= '</label>';
                        $html .= '</div>';
                    }
                }
                break;
                
            default:
                // إذا كان نوع السؤال غير معروف، نعرضه كنص عادي
                $html .= '<input type="text" class="form-control" id="question_' . $question['id'] . '" ';
                $html .= 'name="question_' . $question['id'] . '"';
                if ($question['is_required']) {
                    $html .= ' required';
                }
                $html .= '>';
                break;
        }
        
        $html .= '</div>';
    }
    
    echo json_encode(['success' => true, 'html' => $html]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'خطأ في قاعدة البيانات: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'خطأ غير متوقع: ' . $e->getMessage()]);
}
?> 