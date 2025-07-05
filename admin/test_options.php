<?php
require_once '../cache/db.php';

try {
    // Test if tables exist
    $stmt = $pdo->query("SHOW TABLES LIKE 'question_options'");
    if ($stmt->rowCount() == 0) {
        echo "question_options table does not exist\n";
        exit;
    }
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'service_questions'");
    if ($stmt->rowCount() == 0) {
        echo "service_questions table does not exist\n";
        exit;
    }
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'services'");
    if ($stmt->rowCount() == 0) {
        echo "services table does not exist\n";
        exit;
    }
    
    // Test the options query
    $stmt = $pdo->query("
        SELECT qo.*, sq.question_text, s.service_name
        FROM question_options qo
        LEFT JOIN service_questions sq ON qo.question_id = sq.id
        LEFT JOIN service_questions s ON sq.service_id = s.id
        ORDER BY qo.created_at DESC
    ");
    $options = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Number of options: " . count($options) . "\n\n";
    
    if (count($options) > 0) {
        echo "First option data:\n";
        print_r($options[0]);
    }
    
    echo "\n\nJSON response:\n";
    echo json_encode(['data' => $options], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 