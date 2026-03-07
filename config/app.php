<?php
/**
 * Application configuration
 */

// Session & security
session_start();
define('APP_NAME', 'Webspace Invoice');
define('APP_URL', 'http://localhost/webspace-invoice-system'); // Update for your setup
define('SITE_EMAIL', 'noreply@yourdomain.com'); // For sending emails

// Timezone
date_default_timezone_set('Africa/Lagos');
