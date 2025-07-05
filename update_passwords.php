<?php
require_once 'config/database.php';

echo "=== Updating Passwords ===\n\n";

try {
    $pdo = getDBConnection();
    
    // Generate new hash for 'admin' password
    $password = 'admin';
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    echo "New hash for 'admin': $hash\n\n";
    
    // Update admin passwords
    $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE username IN ('admin', 'manager')");
    $result = $stmt->execute([$hash]);
    
    if ($result) {
        echo "✓ Admin passwords updated successfully\n";
    } else {
        echo "✗ Failed to update admin passwords\n";
    }
    
    // Update trainer passwords
    $stmt = $pdo->prepare("UPDATE trainers SET password = ? WHERE username LIKE 'trainer%'");
    $result = $stmt->execute([$hash]);
    
    if ($result) {
        echo "✓ Trainer passwords updated successfully\n";
    } else {
        echo "✗ Failed to update trainer passwords\n";
    }
    
    // Verify the updates
    echo "\n=== Verification ===\n";
    
    // Check admin
    $stmt = $pdo->prepare("SELECT username, password FROM admins WHERE username = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch();
    
    if ($admin && password_verify('admin', $admin['password'])) {
        echo "✓ Admin password verification: SUCCESS\n";
    } else {
        echo "✗ Admin password verification: FAILED\n";
    }
    
    // Check trainer
    $stmt = $pdo->prepare("SELECT username, password FROM trainers WHERE username = 'trainer1'");
    $stmt->execute();
    $trainer = $stmt->fetch();
    
    if ($trainer && password_verify('admin', $trainer['password'])) {
        echo "✓ Trainer password verification: SUCCESS\n";
    } else {
        echo "✗ Trainer password verification: FAILED\n";
    }
    
    echo "\n=== Login Credentials ===\n";
    echo "Admin Login: admin/admin\n";
    echo "Manager Login: manager/admin\n";
    echo "Trainer Login: trainer1/admin, trainer2/admin, etc.\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Update Complete ===\n";
?> 