-- =====================================================
-- Fix Admin Passwords - TIEC System
-- =====================================================

USE tiec_form;

-- Update admin passwords with correct hash for 'admin'
-- This hash was generated for the password 'admin'
UPDATE admins SET password = '$2y$10$4PceBtKWiteUPuMZJkEM3OB.8W4qMuuTSIB9nDK9pBMwaQYSaDsG.' WHERE username = 'admin';
UPDATE admins SET password = '$2y$10$4PceBtKWiteUPuMZJkEM3OB.8W4qMuuTSIB9nDK9pBMwaQYSaDsG.' WHERE username = 'manager';

-- Also update trainer passwords to use 'admin' as password
UPDATE trainers SET password = '$2y$10$4PceBtKWiteUPuMZJkEM3OB.8W4qMuuTSIB9nDK9pBMwaQYSaDsG.' WHERE username LIKE 'trainer%';

-- Alternative: If you want to use '123456' as password instead
-- UPDATE admins SET password = '$2y$10$d6n6Hc0noMzMBzbOhbsBWelYq2lH/IWZxZERYKIGBfFaqZaKGok8y' WHERE username = 'admin';
-- UPDATE admins SET password = '$2y$10$d6n6Hc0noMzMBzbOhbsBWelYq2lH/IWZxZERYKIGBfFaqZaKGok8y' WHERE username = 'manager';

SELECT 'Passwords updated successfully!' AS message;
SELECT 'Login with: admin/admin or manager/admin' AS login_info;
SELECT 'Trainer login: trainer1/admin, trainer2/admin, etc.' AS trainer_info; 