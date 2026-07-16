<?php
require_once 'auth_check.php';
require_once 'dbcon.php';
require_once 'lib/BackupManager.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['AddRecord'])) {
    $label = trim($_POST['label'] ?? '');
    try {
        BackupManager::create($conn, $label);
        header('Location: backup.php');
    } catch (Throwable $e) {
        header('Location: backup.php?message=' . urlencode('Backup failed: ' . $e->getMessage()));
    }
    exit();
} else {
    echo 'Invalid request.';
}
