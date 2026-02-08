<?php
require_once __DIR__ . '/../includes/Mailer.php';

echo "Attempting to send test email...\n";
$to = 'nayanmhrzn11@gmail.com'; // Sending to self
$subject = 'Bank Queue System - CLI Test';
$body = '<h1>It works!</h1><p>This is a test email from the CLI script.</p>';

$result = Mailer::send($to, $subject, $body);

if ($result['success']) {
    echo "SUCCESS: " . $result['message'] . "\n";
} else {
    echo "FAILURE: " . $result['message'] . "\n";
}
