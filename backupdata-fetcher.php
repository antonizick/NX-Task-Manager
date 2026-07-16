<?php
require_once 'auth_check.php';

header('Content-Type: application/json');

require_once 'lib/BackupManager.php';

$data = array_map(function ($b) {
    return [
        'name' => $b['name'],
        'size' => $b['size'],
        'mtime' => date('Y-m-d H:i:s', $b['mtime']),
    ];
}, BackupManager::list());

echo json_encode(['data' => $data]);
