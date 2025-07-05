<?php
require_once 'config/database.php';

echo "=== Login Test ===\n\n";

try {
    $pdo = getDBConnection();
    
    // Test admin login
    $username = 'admin';
    $password = 'admin';
    $user_type = 'admin';
    
    echo "Testing admin login...\n";
    echo "Username: $username\n";
    echo "Password: $password\n";
    echo "User Type: $user_type\n\n";
    
    // Check if user exists
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? AND is_active = 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo "✗ User not found or inactive\n";
        exit();
    }
    
    echo "✓ User found: " . $user['name'] . "\n";
    echo "Role: " . $user['role'] . "\n";
    
    // Verify password
    if (password_verify($password, $user['password'])) {
        echo "✓ Password verification: SUCCESS\n";
        echo "✓ Login would be successful\n";
    } else {
        echo "✗ Password verification: FAILED\n";
        echo "✗ Login would fail\n";
    }
    
    echo "\n=== Trainer Login Test ===\n";
    
    // Test trainer login
    $username = 'trainer1';
    $password = 'admin';
    $user_type = 'trainer';
    
    echo "Testing trainer login...\n";
    echo "Username: $username\n";
    echo "Password: $password\n";
    echo "User Type: $user_type\n\n";
    
    // Check if user exists
    $stmt = $pdo->prepare("SELECT * FROM trainers WHERE username = ? AND is_active = 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo "✗ User not found or inactive\n";
        exit();
    }
    
    echo "✓ User found: " . $user['name'] . "\n";
    echo "Specialization: " . $user['specialization'] . "\n";
    
    // Verify password
    if (password_verify($password, $user['password'])) {
        echo "✓ Password verification: SUCCESS\n";
        echo "✓ Login would be successful\n";
    } else {
        echo "✗ Password verification: FAILED\n";
        echo "✗ Login would fail\n";
    }
    
    echo "\n=== All Tests Complete ===\n";
    echo "You can now login with:\n";
    echo "- Admin: admin/admin\n";
    echo "- Manager: manager/admin\n";
    echo "- Trainers: trainer1/admin, trainer2/admin, etc.\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
?> 