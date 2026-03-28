<?php
require_once __DIR__ . '/init.php';

if (isSystemAdmin()) {
    jsonResponse(['error' => 'Platform operators cannot access this resource.'], 403);
}
