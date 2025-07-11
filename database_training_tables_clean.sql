-- جدول التدريبات
CREATE TABLE IF NOT EXISTS trainings (
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
CREATE TABLE IF NOT EXISTS training_participants (
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
CREATE TABLE IF NOT EXISTS attendance (
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