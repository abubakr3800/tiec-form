<?php
require_once 'config/database.php';

echo "=== Password Test Script ===\n\n";

// Test password hashing
$test_password = 'admin';
$hash = password_hash($test_password, PASSWORD_DEFAULT);

echo "Test Password: $test_password\n";
echo "Generated Hash: $hash\n\n";

// Test password verification
if (password_verify($test_password, $hash)) {
    echo "✓ Password verification: SUCCESS\n";
} else {
    echo "✗ Password verification: FAILED\n";
}

// Test with the hash we're using in the database
$db_hash = '$2y$10$4PceBtKWiteUPuMZJkEM3OB.8W4qMuuTSIB9nDK9pBMwaQYSaDsG.';
if (password_verify($test_password, $db_hash)) {
    echo "✓ Database hash verification: SUCCESS\n";
} else {
    echo "✗ Database hash verification: FAILED\n";
}

// Test database connection and admin login
try {
    $pdo = getDBConnection();
    echo "\n=== Database Connection Test ===\n";
    echo "✓ Database connection: SUCCESS\n";
    
    // Test admin login
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = 'admin' AND is_active = 1");
    $stmt->execute();
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "✓ Admin user found in database\n";
        echo "Admin name: " . $admin['name'] . "\n";
        echo "Admin role: " . $admin['role'] . "\n";
        
        // Test password verification
        if (password_verify('admin', $admin['password'])) {
            echo "✓ Admin password verification: SUCCESS\n";
        } else {
            echo "✗ Admin password verification: FAILED\n";
            echo "Current hash in DB: " . $admin['password'] . "\n";
        }
    } else {
        echo "✗ Admin user not found in database\n";
    }
    
    // Test trainer login
    $stmt = $pdo->prepare("SELECT * FROM trainers WHERE username = 'trainer1' AND is_active = 1");
    $stmt->execute();
    $trainer = $stmt->fetch();
    
    if ($trainer) {
        echo "\n✓ Trainer user found in database\n";
        echo "Trainer name: " . $trainer['name'] . "\n";
        echo "Trainer specialization: " . $trainer['specialization'] . "\n";
        
        // Test password verification
        if (password_verify('admin', $trainer['password'])) {
            echo "✓ Trainer password verification: SUCCESS\n";
        } else {
            echo "✗ Trainer password verification: FAILED\n";
            echo "Current hash in DB: " . $trainer['password'] . "\n";
        }
    } else {
        echo "\n✗ Trainer user not found in database\n";
    }
    
} catch (Exception $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
?> 