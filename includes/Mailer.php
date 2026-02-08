<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Config
$configFile = __DIR__ . '/../config/email_config.php';
if (file_exists($configFile)) {
    require_once $configFile;
}

// Load PHPMailer
require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

class Mailer {
    /**
     * Send an email or log it if sending fails (or if not configured).
     * 
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $body Email body (HTML)
     * @return array [bool success, string message]
     */
    public static function send($to, $subject, $body) {
        $logMode = true;
        
        // Check if SMTP is configured (non-placeholder)
        if (defined('SMTP_HOST') && defined('SMTP_USER') && defined('SMTP_PASS')) {
            if (strpos(SMTP_USER, 'PUT_YOUR') === false) {
                $logMode = false;
            }
        }

        if (!$logMode) {
            $mail = new PHPMailer(true);
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host       = SMTP_HOST;
                $mail->SMTPAuth   = true;
                $mail->Username   = SMTP_USER;
                $mail->Password   = SMTP_PASS;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = SMTP_PORT;

                // Recipients
                $mail->setFrom(SMTP_USER, defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'Bank Queue');
                $mail->addAddress($to);

                // Content
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body    = $body;

                $mail->send();
                return ['success' => true, 'message' => 'Email sent via SMTP'];
            } catch (Exception $e) {
                // Determine if it's an auth error or something else
                $errorMsg = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                
                // Enhanced Logging
                $logDir = __DIR__ . '/../logs';
                if (!is_dir($logDir)) mkdir($logDir, 0777, true);
                $timestamp = date('Y-m-d H:i:s');
                file_put_contents($logDir . '/email_error.log', "[$timestamp] $errorMsg\n", FILE_APPEND);

                self::logEmail($to, $subject, "FAILED SEND: $errorMsg\n\n$body");
                return ['success' => false, 'message' => $errorMsg];
            }
        } else {
            // Fallback: Log to file
            if (self::logEmail($to, $subject, $body)) {
                return ['success' => true, 'message' => 'Email logged to local file (SMTP not configured)'];
            }
            return ['success' => false, 'message' => 'Failed to write to log file'];
        }
    }

    private static function logEmail($to, $subject, $body) {
        $logDir = __DIR__ . '/../logs'; // Ensure this directory exists
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }

        $logFile = $logDir . '/email_log.txt';
        $timestamp = date('Y-m-d H:i:s');
        $entry = "[$timestamp] TO: $to | SUBJECT: $subject\nBODY: $body\n" . str_repeat('-', 40) . "\n";

        return file_put_contents($logFile, $entry, FILE_APPEND) !== false;
    }
}
