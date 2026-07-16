<?php
require_once 'auth_check.php';
require_once 'lib/BackupManager.php';

$backup = BackupManager::find($_GET['file'] ?? '');
if (!$backup) {
    http_response_code(404);
    exit('Backup not found.');
}

header('Content-Type: application/sql');
header('Content-Disposition: attachment; filename="' . $backup['name'] . '"');
header('Content-Length: ' . $backup['size']);
readfile($backup['path']);
