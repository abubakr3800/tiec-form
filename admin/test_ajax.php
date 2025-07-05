<?php
// Simple test to check what options_ajax.php returns
$url = 'http://localhost/tiec-form/admin/options_ajax.php?action=get_options';
$response = file_get_contents($url);

echo "Response from options_ajax.php:\n";
echo $response . "\n";

// Try to decode JSON
$json = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "\nJSON Error: " . json_last_error_msg() . "\n";
} else {
    echo "\nDecoded JSON:\n";
    print_r($json);
}
?> 