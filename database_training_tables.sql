-- جدول التدريبات
CREATE TABLE trainings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    title_ar VARCHAR(255),
    description TEXT,
    description_ar TEXT,
    trainer_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    max_participants INT DEFAULT 50,
    current_participants INT DEFAULT 0,
    status ENUM('active', 'inactive', 'completed', 'cancelled') DEFAULT 'active',
    location VARCHAR(255),
    location_ar VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (trainer_id) REFERENCES trainers(id) ON DELETE CASCADE
);

-- جدول تسجيل المشاركين في التدريبات
CREATE TABLE training_participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    training_id INT NOT NULL,
    participant_id INT NOT NULL,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('registered', 'attended', 'absent', 'cancelled') DEFAULT 'registered',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (training_id) REFERENCES trainings(id) ON DELETE CASCADE,
    FOREIGN KEY (participant_id) REFERENCES participants(id) ON DELETE CASCADE,
    UNIQUE KEY unique_training_participant (training_id, participant_id)
);

-- جدول تسجيل الحضور
CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    training_id INT NOT NULL,
    participant_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    check_in_time TIME,
    check_out_time TIME,
    status ENUM('present', 'absent', 'late', 'excused') DEFAULT 'present',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (training_id) REFERENCES trainings(id) ON DELETE CASCADE,
    FOREIGN KEY (participant_id) REFERENCES participants(id) ON DELETE CASCADE,
    UNIQUE KEY unique_attendance (training_id, participant_id, attendance_date)
);

-- إضافة بيانات تجريبية للتدريبات
INSERT INTO trainings (title, title_ar, description, description_ar, trainer_id, start_date, end_date, start_time, end_time, max_participants, location, location_ar) VALUES
('Web Development Basics', 'أساسيات تطوير الويب', 'Learn the fundamentals of web development', 'تعلم أساسيات تطوير الويب', 1, '2024-01-15', '2024-01-20', '09:00:00', '17:00:00', 30, 'Training Room A', 'قاعة التدريب أ'),
('Advanced PHP Programming', 'البرمجة المتقدمة بـ PHP', 'Advanced PHP concepts and frameworks', 'مفاهيم PHP المتقدمة والإطارات', 1, '2024-01-22', '2024-01-27', '10:00:00', '18:00:00', 25, 'Training Room B', 'قاعة التدريب ب'),
('Database Management', 'إدارة قواعد البيانات', 'Learn database design and management', 'تعلم تصميم وإدارة قواعد البيانات', 2, '2024-02-01', '2024-02-05', '08:00:00', '16:00:00', 20, 'Training Room C', 'قاعة التدريب ج');

-- إضافة مشاركين في التدريبات
INSERT INTO training_participants (training_id, participant_id, status) VALUES
(1, 1, 'registered'),
(1, 2, 'registered'),
(1, 3, 'registered'),
(2, 1, 'registered'),
(2, 4, 'registered'),
(3, 2, 'registered'),
(3, 5, 'registered');

-- تحديث عدد المشاركين في التدريبات
UPDATE trainings SET current_participants = (
    SELECT COUNT(*) FROM training_participants WHERE training_id = trainings.id AND status != 'cancelled'
); 