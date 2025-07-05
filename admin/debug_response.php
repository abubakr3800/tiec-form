<?php
// Debug file to test the participants response
require_once '../cache/db.php';

try {
    // $pdo is already available from cache/db.php
    
    // Test the same query as get_participants.php
    $stmt = $pdo->query("
        SELECT 
            id,
            name,
            national_id,
            governorate,
            CASE 
                WHEN gender = 'male' THEN 'ذكر'
                WHEN gender = 'female' THEN 'أنثى'
                ELSE gender
            END as gender,
            age,
            phone,
            CASE 
                WHEN participant_type = 'student' THEN 'طالب'
                WHEN participant_type = 'employee' THEN 'موظف'
                WHEN participant_type = 'other' THEN 'أخرى'
                ELSE participant_type
            END as participant_type,
            DATE_FORMAT(registration_date, '%Y-%m-%d %H:%i') as registration_date,
            training_confirmation
        FROM participants 
        ORDER BY registration_date DESC
    ");
    
    $participants = $stmt->fetchAll();
    
    echo "Number of participants: " . count($participants) . "\n\n";
    
    if (count($participants) > 0) {
        echo "First participant data:\n";
        print_r($participants[0]);
    }
    
    echo "\n\nJSON response:\n";
    echo json_encode(['data' => $participants], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 