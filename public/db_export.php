<?php
// Temporary database export endpoint - DELETE AFTER USE
$token = 'xK9mP2qR7vL4nJ8s';
if (($_GET['t'] ?? '') !== $token) {
    http_response_code(403);
    exit('Forbidden');
}
$db = __DIR__ . '/../storage/database.sqlite';
if (!file_exists($db)) {
    http_response_code(404);
    exit('Not found');
}
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="database.sqlite"');
header('Content-Length: ' . filesize($db));
header('Cache-Control: no-store');
readfile($db);
