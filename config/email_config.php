<?php
// SMTP Configuration for Gmail
// usage: Gmail Address + App Password (Not your login password)

define('SMTP_HOST', 'smtp.gmail.com');

// TODO: USER MUST UPDATE THESE VALUES
// 1. Go to https://myaccount.google.com/security
// 2. Enable 2-Step Verification if not enabled.
// 3. Search for "App Passwords" in the search bar.
// 4. Create a new App Password named "BankQueue".
// 5. Copy the 16-character password and paste it below.

define('SMTP_USER', 'nayanmhrzn11@gmail.com');
define('SMTP_PASS', 'xsipxoxjwrkuuqym'); // Spaces removed;
define('SMTP_PORT', 587);
define('SMTP_FROM_NAME', 'Bank Queue System');

// Notes:
// If you see "SMTP Error: Could not authenticate", check your App Password.
// If you see "SMTP connect() failed", check your internet connection or firewall.
