<?php
require_once __DIR__ . '/../config/email_config.php';

echo "SMTP_USER: " . (defined('SMTP_USER') ? SMTP_USER : 'NOT DEFINED') . "\n";
echo "Has PUT_YOUR in USER? " . (strpos(SMTP_USER, 'PUT_YOUR') !== false ? 'YES' : 'NO') . "\n";
echo "SMTP_PASS: " . (defined('SMTP_PASS') ? SMTP_PASS : 'NOT DEFINED') . "\n";
echo "Has PUT_YOUR in PASS? " . (strpos(SMTP_PASS, 'PUT_YOUR') !== false ? 'YES' : 'NO') . "\n";
