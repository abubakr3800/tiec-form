# TIEC System - Training and Consulting Services Management Platform

## 🎯 System Overview

The TIEC System is a comprehensive platform for managing training and consulting services at the Technology Innovation and Entrepreneurship Center (TIEC), featuring a smart registration system and automated attendance tracking. The system is designed to serve students, graduates, startups, and professionals.

## 🚀 Key Features

### 📝 Smart Registration System
- **Bilingual registration form** (Arabic/English) with modern interface
- **Data validation** and duplicate registration prevention
- **Unique QR code generation** for each participant for quick access
- **Dynamic questions** based on selected service type
- **Token system** for quick registration without re-entering data

### 🎓 Available Service Types

#### 1. Training Programs (Training)
- Professional training with specialized trainers
- Fixed schedules and limited capacity
- Custom questions based on training type
- Attendance and absence tracking

#### 2. Individual Consultations (Mentoring)
- Consultations for individuals and companies
- Advisory sessions for students and graduates
- Flexible scheduling as needed
- Questions about consultation field

#### 3. Digital Manufacturing Lab (FabLab)
- Booking work periods for project execution
- Available equipment for use
- Questions about required project
- Equipment usage tracking

#### 4. Co-Working Space
- Booking workspaces for collaborative work
- Flexible time periods
- Questions about work type
- Available space management

### 📱 Smart Attendance System
- **QR Scanner** for attendance registration
- **National ID search** as QR alternative
- **Automatic attendance tracking** and absence
- **Detailed attendance reports**

### 👥 User Management
- **Admin system** with different permissions
- **Trainer management** and specialists
- **Secure login** with password encryption
- **Role and permission management**

### 📊 Advanced Dashboard
- **Comprehensive statistics** for the system
- **Interactive tables** with DataTables
- **Data export** (Excel, PDF, Print)
- **Advanced search and filtering**

## 🏗️ Database Structure

### Main Tables

#### `participants` Table
```sql
- id (PK) - Unique identifier
- name - Full name
- national_id (unique) - National ID
- phone - Phone number
- email - Email address
- governorate - Governorate
- gender - Gender (male/female)
- age - Age
- participant_type - Participant type (student/employee/other)
- university - University (for students)
- education_stage - Education stage
- faculty - Faculty
- work_employer - Work employer (for employees)
- qr_code (unique) - Unique QR code
- created_at - Creation date
- updated_at - Update date
```

#### `services` Table
```sql
- id (PK) - Unique identifier
- name_ar - Service name in Arabic
- name_en - Service name in English
- description_ar - Service description in Arabic
- description_en - Service description in English
- service_type - Service type (training/mentoring/fablab/coworking)
- is_active - Activation status
- sort_order - Display order
- created_at - Creation date
- updated_at - Update date
```

#### `trainers` Table
```sql
- id (PK) - Unique identifier
- name_ar - Trainer name in Arabic
- name_en - Trainer name in English
- email - Email address
- phone - Phone number
- specialization - Specialization
- bio_ar - Biography in Arabic
- bio_en - Biography in English
- is_active - Activation status
- created_at - Creation date
- updated_at - Update date
```

#### `trainings` Table
```sql
- id (PK) - Unique identifier
- service_id (FK) - Service ID
- trainer_id (FK) - Trainer ID
- title_ar - Training title in Arabic
- title_en - Training title in English
- description_ar - Training description in Arabic
- description_en - Training description in English
- start_date - Start date
- end_date - End date
- start_time - Start time
- end_time - End time
- max_participants - Maximum participants
- current_participants - Current participants count
- is_active - Activation status
- created_at - Creation date
- updated_at - Update date
```

#### `registrations` Table
```sql
- id (PK) - Unique identifier
- participant_id (FK) - Participant ID
- service_id (FK) - Service ID
- training_id (FK) - Training ID (optional)
- registration_date - Registration date
- status - Status (pending/confirmed/cancelled)
- created_at - Creation date
- updated_at - Update date
```

#### `attendance` Table
```sql
- id (PK) - Unique identifier
- registration_id (FK) - Registration ID
- attendance_date - Attendance date
- check_in_time - Check-in time
- check_out_time - Check-out time
- status - Status (present/absent/late)
- notes - Notes
- created_at - Creation date
- updated_at - Update date
```

## 🎬 Usage Scenarios

### Scenario 1: New Registration
1. **User visits the website** (`index.php`)
2. **Selects service type** (training, consultation, fablab, workspace)
3. **Custom questions appear** for the selected service
4. **Fills personal data** (name, national ID, governorate, etc.)
5. **Selects participant type** (student, employee, other)
6. **Additional fields appear** based on type (university, faculty, work employer)
7. **Unique QR code is generated** automatically
8. **Registration is saved** with all answers
9. **QR code is displayed** for the participant to keep

### Scenario 2: Registration by Token
1. **User enters Token** (`token_login.php`)
2. **System validates the Token**
3. **Saved data appears** automatically
4. **Can register for new service** without re-entering data
5. **New registration is linked** to the same participant

### Scenario 3: Attendance Registration
1. **Manager opens QR Scanner page** (`attendance_scanner.php`)
2. **Participant scans QR code** or enters national ID
3. **System checks for registration** on today's date
4. **Attendance is registered automatically** with time
5. **Attendance confirmation appears** for participant

### Scenario 4: System Management (for Managers)
1. **Manager logs in** (`admin/login.php`)
2. **Accesses dashboard** (`admin/index.php`)
3. **Manages services and trainings** (`admin/services_manager.php`)
4. **Adds trainers** (`admin/trainers.php`)
5. **Tracks attendance** (`admin/attendance_manager.php`)
6. **Extracts reports** and data

## 🗺️ Page Navigation Map

### 📱 Participant Interface (Frontend)

#### 1. Main Registration Page
- **URL**: `index.php`
- **Description**: New registration page with comprehensive form
- **Features**: 
  - Bilingual form (Arabic/English)
  - Dynamic questions based on service
  - Unique QR code generation
- **Navigation to**: 
  - `process_registration.php` (on submit)
  - `already_registered.php` (if already registered)

#### 2. Token Login Page
- **URL**: `token_login.php`
- **Description**: Login using unique token
- **Features**:
  - Token input
  - Participant data display
  - Available services list
- **Navigation to**:
  - `index.php` (new registration)
  - `attendance_scanner.php` (attendance registration)

#### 3. View Registrations Page
- **URL**: `view_my_registrations.php`
- **Description**: Display all participant registrations
- **Features**:
  - List of previous registrations
  - Details of each registration
  - Attendance record

#### 4. Attendance Registration Page
- **URL**: `attendance_scanner.php`
- **Description**: QR Scanner for attendance registration
- **Features**:
  - QR code scanning
  - National ID input
  - Automatic attendance registration
- **Navigation to**:
  - Same page with confirmation message

#### 5. Already Registered Page
- **URL**: `already_registered.php`
- **Description**: Message for previously registered users
- **Features**:
  - Explanatory message
  - Links to other pages

### 🖥️ Admin Panel

#### 1. Admin Login Page
- **URL**: `admin/login.php`
- **Description**: Login for administrators
- **Features**:
  - Secure login form
  - Permission verification
- **Navigation to**:
  - `admin/index.php` (main dashboard)

#### 2. Main Dashboard
- **URL**: `admin/index.php`
- **Description**: Main admin panel page
- **Features**:
  - Quick statistics
  - Recent participants list
  - Quick links to management
- **Navigation to**:
  - All admin pages

#### 3. Services Management
- **URL**: `admin/services_manager.php`
- **Description**: Manage services and questions
- **Features**:
  - Add/edit/delete services
  - Manage questions for each service
  - Manage question options
- **Navigation to**:
  - `admin/services_ajax.php` (AJAX)
  - `admin/questions_ajax.php` (AJAX)

#### 4. Trainers Management
- **URL**: `admin/trainers.php`
- **Description**: Manage trainers and specialists
- **Features**:
  - Add/edit/delete trainers
  - Display trainer details
  - Manage specializations
- **Navigation to**:
  - `admin/save_trainer.php` (save trainer)
  - `admin/delete_trainer.php` (delete trainer)

#### 5. Trainings Management
- **URL**: `admin/trainings_manager.php`
- **Description**: Manage trainings and schedules
- **Features**:
  - Create new trainings
  - Link trainings to trainers
  - Set schedules and capacity
- **Navigation to**:
  - `admin/trainings_ajax.php` (AJAX)
  - `admin/save_training.php` (save training)

#### 6. Attendance Management
- **URL**: `admin/attendance_manager.php`
- **Description**: Track attendance and absence
- **Features**:
  - Display attendance records
  - Attendance reports
  - Absence management
- **Navigation to**:
  - `admin/attendance_ajax.php` (AJAX)

#### 7. Participants Management
- **URL**: `admin/participants.php`
- **Description**: View and manage participants
- **Features**:
  - List all participants
  - Details of each participant
  - Data export
- **Navigation to**:
  - `admin/get_participants.php` (AJAX)

#### 8. Admins Management
- **URL**: `admin/admins.php`
- **Description**: Manage administrators and permissions
- **Features**:
  - Add/edit/delete administrators
  - Manage roles and permissions
- **Navigation to**:
  - `admin/save_admin.php` (save admin)
  - `admin/delete_admin.php` (delete admin)

### 🔄 Main Navigation Paths

#### New Participant Path:
```
index.php → process_registration.php → (QR Code) → view_my_registrations.php
```

#### Registered Participant Path:
```
token_login.php → (Token) → view_my_registrations.php → attendance_scanner.php
```

#### Manager Path:
```
admin/login.php → admin/index.php → admin/services_manager.php → admin/trainers.php → admin/trainings_manager.php → admin/attendance_manager.php
```

#### Attendance Registration Path:
```
attendance_scanner.php → (QR/National ID) → (Attendance Confirmation)
```

## 🛠️ System Requirements

### Technical Requirements
- **PHP 7.4** or later
- **MySQL 5.7** or later
- **Apache/Nginx** web server
- **Modern browser** with JavaScript support
- **XAMPP/WAMP** for local development

### Additional Requirements
- **GD Library** for QR code generation
- **PDO Extension** for database connection
- **JSON Extension** for data processing
- **File Upload** for file uploads

## 📦 Installation and Setup

### 1. Database Setup

```bash
# Run database creation file
php create_complete_database.php
```

### 2. Connection Configuration

Edit `config/database.php`:

```php
$host = '127.0.0.1';
$db   = 'tiec_form';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';
```

### 3. File Upload

```bash
# Upload all files to web server directory
# Example: C:\xampp2\htdocs\Tiec\
```

### 4. Permissions Setup

```bash
# Ensure uploads/ directory is writable
mkdir uploads
chmod 755 uploads
```

## 🎯 How to Use

### For Participants

#### New Registration
```
http://localhost/Tiec/index.php
```

#### Token Login
```
http://localhost/Tiec/token_login.php
```

#### View Registrations
```
http://localhost/Tiec/view_my_registrations.php
```

#### Attendance Registration
```
http://localhost/Tiec/attendance_scanner.php
```

### For Managers

#### Main Dashboard
```
http://localhost/Tiec/admin/index.php
```

#### Services Management
```
http://localhost/Tiec/admin/services_manager.php
```

#### Trainers Management
```
http://localhost/Tiec/admin/trainers.php
```

#### Trainings Management
```
http://localhost/Tiec/admin/trainings_manager.php
```

#### Attendance Management
```
http://localhost/Tiec/admin/attendance_manager.php
```

#### Participants Management
```
http://localhost/Tiec/admin/participants.php
```

## 🔒 Security

### Data Protection
- **Password encryption** using `password_hash()`
- **Session validation** to prevent unauthorized access
- **SQL Injection protection** using Prepared Statements
- **Input data validation**
- **Token encryption** for secure access

### User Permissions
- **Managers (Admin)**: Complete system management
- **Trainers**: Training and attendance management
- **Participants**: Registration and data viewing

## 📊 Reports and Statistics

### Attendance Reports
- **Daily report**: Attendance and absence count
- **Monthly report**: Monthly statistics
- **Training report**: Details for each training

### System Statistics
- **Participant count**: Total registered participants
- **Training count**: Active trainings
- **Attendance rate**: Attendance to absence ratio
- **Most requested services**: Service ranking

## 🔧 Troubleshooting

### Common Issues and Solutions

#### 1. Database Error
```bash
# Run database creation file
php create_complete_database.php
```

#### 2. QR Scanner Error
- Ensure HTTPS support or use localhost
- Check camera settings in browser

#### 3. Token Error
- Check session settings
- Verify cookie validity

#### 4. File Upload Error
- Ensure uploads/ directory permissions
- Check file size (maximum 10MB)

## 📈 Future Improvements

### Phase 1 (Completed)
- ✅ Basic registration system
- ✅ QR-based attendance system
- ✅ Admin dashboard
- ✅ Services and trainers management

### Phase 2 (In Development)
- 🔄 SMS/Email notification system
- 🔄 Interactive calendar for appointment booking
- 🔄 Service rating system
- 🔄 Mobile application

### Phase 3 (Future)
- 📱 iOS and Android applications
- 🤖 Smart service recommendation system
- 📊 Advanced analytics
- 🔗 API for external system integration

## 📞 Technical Support

### For Technical Help
- Review `system_scenario.txt` for complete details
- Check `README_NEW_SYSTEM.md` for new features
- Review `database_schema.sql` for database structure

### For Issues
1. Check XAMPP error logs
2. Verify database settings
3. Check file and folder permissions
4. Review `check_tables.php` to verify table integrity

## 📄 License

This project is open source and available for commercial and personal use.

## 👥 Development Team

This system was developed with ❤️ for **Technology Innovation and Entrepreneurship Center (TIEC)**

---

**Last Update**: December 2024  
**Version**: 2.0  
**Status**: Stable and Tested 