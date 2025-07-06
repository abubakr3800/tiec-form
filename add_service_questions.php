<?php
/**
 * ملف إضافة الأسئلة المخصصة لكل خدمة
 * إضافة الأسئلة حسب نوع الخدمة
 */

require_once 'config/database.php';

try {
    $pdo = getDBConnection();
    
    echo "بدء إضافة الأسئلة المخصصة للخدمات...\n\n";
    
    // حذف الأسئلة الموجودة
    $pdo->exec("DELETE FROM service_questions");
    $pdo->exec("DELETE FROM question_options");
    echo "تم حذف الأسئلة القديمة\n\n";
    
    // جلب الخدمات
    $stmt = $pdo->query("SELECT * FROM services ORDER BY sort_order");
    $services = $stmt->fetchAll();
    
    echo "عدد الخدمات الموجودة: " . count($services) . "\n\n";
    
    foreach ($services as $service) {
        echo "إضافة أسئلة لخدمة: " . $service['name_ar'] . " (النوع: " . $service['service_type'] . ")\n";
        
        switch ($service['service_type']) {
            case 'coworking':
                addCoWorkingQuestions($pdo, $service['id']);
                break;
            case 'fablab':
                addFabLabQuestions($pdo, $service['id']);
                break;
            case 'mentoring':
                addMentoringQuestions($pdo, $service['id']);
                break;
            case 'training':
                addTrainingQuestions($pdo, $service['id']);
                break;
            default:
                echo "  - نوع خدمة غير معروف: " . $service['service_type'] . "\n";
                break;
        }
    }
    
    echo "\n✅ تم إضافة جميع الأسئلة بنجاح!\n";
    
} catch (Exception $e) {
    echo "❌ خطأ في إضافة الأسئلة: " . $e->getMessage() . "\n";
}

function addCoWorkingQuestions($pdo, $service_id) {
    $questions = [
        [
            'question_text_ar' => 'هدف الحجز',
            'question_text_en' => 'Booking Purpose',
            'question_type' => 'select',
            'is_required' => 1,
            'sort_order' => 1,
            'options' => [
                'Study' => 'دراسة',
                'Meeting' => 'اجتماع',
                'Freelance' => 'عمل حر',
                'Start-up' => 'شركة ناشئة',
                'One to one session' => 'جلسة فردية',
                'Mentorship' => 'إرشاد'
            ]
        ],
        [
            'question_text_ar' => 'تفاصيل العمل الخاصة بك (إذا كان Freelancer أو Start-up)',
            'question_text_en' => 'Work Details (if Freelancer or Start-up)',
            'question_type' => 'textarea',
            'is_required' => 0,
            'sort_order' => 2
        ],
        [
            'question_text_ar' => 'تاريخ الحجز',
            'question_text_en' => 'Booking Date',
            'question_type' => 'date',
            'is_required' => 1,
            'sort_order' => 3
        ],
        [
            'question_text_ar' => 'موعد الحجز (ساعة، دقيقة، ثانية)',
            'question_text_en' => 'Booking Time (Hour, Minute, Second)',
            'question_type' => 'time',
            'is_required' => 1,
            'sort_order' => 4
        ],
        [
            'question_text_ar' => 'عدد ساعات الحجز',
            'question_text_en' => 'Number of Booking Hours',
            'question_type' => 'number',
            'is_required' => 1,
            'sort_order' => 5
        ],
        [
            'question_text_ar' => 'الموافقة على الشروط والأحكام',
            'question_text_en' => 'Agreement to Terms and Conditions',
            'question_type' => 'checkbox',
            'is_required' => 1,
            'sort_order' => 6
        ]
    ];
    
    insertQuestions($pdo, $service_id, $questions);
}

function addFabLabQuestions($pdo, $service_id) {
    $questions = [
        [
            'question_text_ar' => 'نوع الحجز',
            'question_text_en' => 'Booking Type',
            'question_type' => 'select',
            'is_required' => 1,
            'sort_order' => 1,
            'options' => [
                'Mentorship' => 'إرشاد',
                'Fabrication' => 'تصنيع',
                'Printing' => 'طباعة',
                'Prototype' => 'نموذج أولي'
            ]
        ],
        [
            'question_text_ar' => 'الماكينة المطلوبة',
            'question_text_en' => 'Required Machine',
            'question_type' => 'select',
            'is_required' => 1,
            'sort_order' => 2,
            'options' => [
                'Laser cutting' => 'قطع ليزر',
                '3D Printing' => 'طباعة ثلاثية الأبعاد',
                'CNC' => 'تحكم رقمي',
                'IOT Tools' => 'أدوات إنترنت الأشياء',
                'أخرى' => 'أخرى'
            ]
        ],
        [
            'question_text_ar' => 'رفع ملف الخدمة (ملف واحد، 10MB)',
            'question_text_en' => 'Upload Service File (One file, 10MB)',
            'question_type' => 'file',
            'is_required' => 1,
            'sort_order' => 3
        ],
        [
            'question_text_ar' => 'توفر الخامات المطلوبة',
            'question_text_en' => 'Materials Availability',
            'question_type' => 'radio',
            'is_required' => 1,
            'sort_order' => 4,
            'options' => [
                'احتاج إلى توفير الأدوات' => 'احتاج إلى توفير الأدوات',
                'لا احتاج إلى توفير الأدوات ومتاحة معي' => 'لا احتاج إلى توفير الأدوات ومتاحة معي'
            ]
        ],
        [
            'question_text_ar' => 'نوع الخامات المطلوبة (إن وجدت)',
            'question_text_en' => 'Required Materials Type (if any)',
            'question_type' => 'text',
            'is_required' => 0,
            'sort_order' => 5
        ],
        [
            'question_text_ar' => 'اليوم المفضل للحجز',
            'question_text_en' => 'Preferred Booking Day',
            'question_type' => 'radio',
            'is_required' => 1,
            'sort_order' => 6,
            'options' => [
                'الأحد' => 'الأحد',
                'الخميس' => 'الخميس'
            ]
        ],
        [
            'question_text_ar' => 'تاريخ يوم الحجز',
            'question_text_en' => 'Booking Date',
            'question_type' => 'date',
            'is_required' => 1,
            'sort_order' => 7
        ],
        [
            'question_text_ar' => 'موعد الحجز (ساعة، دقيقة)',
            'question_text_en' => 'Booking Time (Hour, Minute)',
            'question_type' => 'time',
            'is_required' => 1,
            'sort_order' => 8
        ],
        [
            'question_text_ar' => 'عدد ساعات الحجز',
            'question_text_en' => 'Number of Booking Hours',
            'question_type' => 'number',
            'is_required' => 1,
            'sort_order' => 9
        ],
        [
            'question_text_ar' => 'الموافقة على الشروط والأحكام',
            'question_text_en' => 'Agreement to Terms and Conditions',
            'question_type' => 'checkbox',
            'is_required' => 1,
            'sort_order' => 10
        ],
        [
            'question_text_ar' => 'ملاحظات (اختياري)',
            'question_text_en' => 'Notes (Optional)',
            'question_type' => 'textarea',
            'is_required' => 0,
            'sort_order' => 11
        ]
    ];
    
    insertQuestions($pdo, $service_id, $questions);
}

function addMentoringQuestions($pdo, $service_id) {
    $questions = [
        [
            'question_text_ar' => 'اسم الشركة الناشئة أو الفكرة',
            'question_text_en' => 'Startup or Idea Name',
            'question_type' => 'text',
            'is_required' => 1,
            'sort_order' => 1
        ],
        [
            'question_text_ar' => 'عدد أفراد الفريق',
            'question_text_en' => 'Team Size',
            'question_type' => 'number',
            'is_required' => 1,
            'sort_order' => 2
        ],
        [
            'question_text_ar' => 'مرحلة الشركة الناشئة',
            'question_text_en' => 'Startup Stage',
            'question_type' => 'select',
            'is_required' => 1,
            'sort_order' => 3,
            'options' => [
                'Idea' => 'فكرة',
                'MVB' => 'نموذج أولي',
                'Pre-seed' => 'قبل البذرة',
                'Seed' => 'بذرة'
            ]
        ],
        [
            'question_text_ar' => 'نوع الخدمة المطلوبة',
            'question_text_en' => 'Required Service Type',
            'question_type' => 'radio',
            'is_required' => 1,
            'sort_order' => 4,
            'options' => [
                'Mentorship' => 'إرشاد',
                'Training' => 'تدريب'
            ]
        ],
        [
            'question_text_ar' => 'مجال الدعم المطلوب',
            'question_text_en' => 'Required Support Area',
            'question_type' => 'select',
            'is_required' => 1,
            'sort_order' => 5,
            'options' => [
                'Pitching' => 'عرض تقديمي',
                'Marketing' => 'تسويق',
                'Market Research' => 'بحث السوق',
                'Sales' => 'مبيعات',
                'BMC' => 'نموذج العمل التجاري',
                'Prototyping' => 'نموذج أولي',
                'Financial' => 'مالي',
                'Legal' => 'قانوني',
                'Product Development' => 'تطوير المنتج',
                'أخرى' => 'أخرى'
            ]
        ],
        [
            'question_text_ar' => 'نبذة عن الفكرة أو الشركة الناشئة (تفاصيل دقيقة عن المشكلة والحل والمنتج والتكنولوجيا)',
            'question_text_en' => 'Brief about the Idea or Startup (Detailed information about the problem, solution, product and technology)',
            'question_type' => 'textarea',
            'is_required' => 1,
            'sort_order' => 6
        ],
        [
            'question_text_ar' => 'ملاحظات (اختياري)',
            'question_text_en' => 'Notes (Optional)',
            'question_type' => 'textarea',
            'is_required' => 0,
            'sort_order' => 7
        ]
    ];
    
    insertQuestions($pdo, $service_id, $questions);
}

function addTrainingQuestions($pdo, $service_id) {
    $questions = [
        [
            'question_text_ar' => 'نوع المشارك',
            'question_text_en' => 'Participant Type',
            'question_type' => 'radio',
            'is_required' => 1,
            'sort_order' => 1,
            'options' => [
                'Entrepreneur' => 'رجل أعمال',
                'Start-Up' => 'شركة ناشئة'
            ]
        ],
        [
            'question_text_ar' => 'الاستشارات المطلوبة',
            'question_text_en' => 'Required Consultations',
            'question_type' => 'select',
            'is_required' => 1,
            'sort_order' => 2,
            'options' => [
                'BMC' => 'نموذج العمل التجاري',
                'Prototype' => 'نموذج أولي',
                'Pitch Deck' => 'عرض تقديمي',
                'Medical' => 'طبي',
                'Marketing' => 'تسويق',
                'أخرى' => 'أخرى'
            ]
        ],
        [
            'question_text_ar' => 'اسم الفكرة أو المشروع',
            'question_text_en' => 'Idea or Project Name',
            'question_type' => 'text',
            'is_required' => 1,
            'sort_order' => 3
        ],
        [
            'question_text_ar' => 'عدد أفراد الفريق',
            'question_text_en' => 'Team Size',
            'question_type' => 'number',
            'is_required' => 1,
            'sort_order' => 4
        ],
        [
            'question_text_ar' => 'شرح تفصيلي للفكرة',
            'question_text_en' => 'Detailed Explanation of the Idea',
            'question_type' => 'textarea',
            'is_required' => 1,
            'sort_order' => 5
        ],
        [
            'question_text_ar' => 'نوع المشكلة التي تعمل على حلها',
            'question_text_en' => 'Type of Problem You Are Solving',
            'question_type' => 'text',
            'is_required' => 1,
            'sort_order' => 6
        ],
        [
            'question_text_ar' => 'المرحلة الحالية للفكرة',
            'question_text_en' => 'Current Stage of the Idea',
            'question_type' => 'select',
            'is_required' => 1,
            'sort_order' => 7,
            'options' => [
                'Idea Generated' => 'فكرة مولدة',
                'Prototype' => 'نموذج أولي',
                'MVP' => 'المنتج الأدنى القابل للتطبيق',
                'Established' => 'مؤسس',
                'Scale-up' => 'توسع',
                'Pre-seed' => 'قبل البذرة'
            ]
        ],
        [
            'question_text_ar' => 'هل الفكرة مسجلة؟',
            'question_text_en' => 'Is the Idea Registered?',
            'question_type' => 'radio',
            'is_required' => 1,
            'sort_order' => 8,
            'options' => [
                'نعم' => 'نعم',
                'لا' => 'لا'
            ]
        ],
        [
            'question_text_ar' => 'هل لديك نموذج عمل تجاري؟',
            'question_text_en' => 'Do You Have a Business Model?',
            'question_type' => 'radio',
            'is_required' => 1,
            'sort_order' => 9,
            'options' => [
                'نعم' => 'نعم',
                'لا' => 'لا'
            ]
        ],
        [
            'question_text_ar' => 'رفع نموذج العمل التجاري (ملف واحد، 100MB)',
            'question_text_en' => 'Upload Business Model (One file, 100MB)',
            'question_type' => 'file',
            'is_required' => 0,
            'sort_order' => 10
        ],
        [
            'question_text_ar' => 'هل لديك نموذج أولي؟',
            'question_text_en' => 'Do You Have a Prototype?',
            'question_type' => 'radio',
            'is_required' => 1,
            'sort_order' => 11,
            'options' => [
                'نعم' => 'نعم',
                'لا' => 'لا'
            ]
        ],
        [
            'question_text_ar' => 'رابط أو فيديو النموذج الأولي',
            'question_text_en' => 'Prototype Link or Video',
            'question_type' => 'url',
            'is_required' => 0,
            'sort_order' => 12
        ],
        [
            'question_text_ar' => 'هل لديك عرض تقديمي Pitch Deck؟',
            'question_text_en' => 'Do You Have a Pitch Deck?',
            'question_type' => 'radio',
            'is_required' => 1,
            'sort_order' => 13,
            'options' => [
                'نعم' => 'نعم',
                'لا' => 'لا'
            ]
        ],
        [
            'question_text_ar' => 'رفع العرض التقديمي (ملف واحد، 100MB)',
            'question_text_en' => 'Upload Pitch Deck (One file, 100MB)',
            'question_type' => 'file',
            'is_required' => 0,
            'sort_order' => 14
        ],
        [
            'question_text_ar' => 'رابط الموقع أو الصفحة الخاصة بالشركة',
            'question_text_en' => 'Company Website or Page Link',
            'question_type' => 'url',
            'is_required' => 0,
            'sort_order' => 15
        ]
    ];
    
    insertQuestions($pdo, $service_id, $questions);
}

function insertQuestions($pdo, $service_id, $questions) {
    $stmt = $pdo->prepare("
        INSERT INTO service_questions (service_id, question_text_ar, question_text_en, question_type, is_required, sort_order, is_active) 
        VALUES (?, ?, ?, ?, ?, ?, 1)
    ");
    
    $options_stmt = $pdo->prepare("
        INSERT INTO question_options (question_id, option_text_ar, option_text_en, sort_order, is_active) 
        VALUES (?, ?, ?, ?, 1)
    ");
    
    foreach ($questions as $question) {
        $stmt->execute([
            $service_id,
            $question['question_text_ar'],
            $question['question_text_en'],
            $question['question_type'],
            $question['is_required'],
            $question['sort_order']
        ]);
        
        $question_id = $pdo->lastInsertId();
        
        // إضافة الخيارات إذا وجدت
        if (isset($question['options'])) {
            $sort_order = 1;
            foreach ($question['options'] as $value => $text) {
                $options_stmt->execute([
                    $question_id,
                    $text,
                    $value,
                    $sort_order
                ]);
                $sort_order++;
            }
        }
        
        echo "  - تم إضافة سؤال: " . $question['question_text_ar'] . "\n";
    }
}
?> 