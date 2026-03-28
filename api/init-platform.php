<?php
require_once __DIR__ . '/init.php';

if (!isSystemAdmin()) {
    jsonResponse(['error' => 'Forbidden'], 403);
}
