<?php
/**
 * Application configuration
 */

// Error logging - must run first
$logDir = dirname(__DIR__) . '/logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true);
}
$logFile = $logDir . '/php-error.log';
ini_set('log_errors', 1);
ini_set('error_log', $logFile);
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Custom error handler for extra context
set_error_handler(function ($severity, $message, $file, $line) use ($logFile) {
    $entry = date('[Y-m-d H:i:s]') . " [$severity] $message in $file on line $line" . PHP_EOL;
    @file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
    return false; // Let PHP handle normally too
});

// Session & security
session_start();
define('APP_NAME', 'Webspace Invoice');
define('APP_URL', 'https://www.webspace.ng/invoice');
define('SITE_EMAIL', 'noreply@webspace.ng'); // For sending emails

// Timezone
date_default_timezone_set('Africa/Lagos');
