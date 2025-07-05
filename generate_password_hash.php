<?php
// Generate password hash for 'admin'
$password = 'admin';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Password: " . $password . "\n";
echo "Hash: " . $hash . "\n";

// Verify the hash works
if (password_verify($password, $hash)) {
    echo "Hash verification: SUCCESS\n";
} else {
    echo "Hash verification: FAILED\n";
}

// Also generate hash for other common passwords
$passwords = ['admin', 'password', '123456', 'admin123'];

echo "\nCommon password hashes:\n";
foreach ($passwords as $pwd) {
    $hash = password_hash($pwd, PASSWORD_DEFAULT);
    echo "Password: '$pwd' -> Hash: $hash\n";
}
?> 