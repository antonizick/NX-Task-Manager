<?php
require_once 'auth_check.php';
// memodelete.php

// Include database connection using your existing method
require_once 'dbcon.php';

// Check if 'indx' was provided via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['indx'])) {
    $indx = intval($_POST['indx']); // Sanitize as integer

    // Soft-delete: suppress from UI and rename to prevent future name collisions
    $deletedSuffix = 'userdeleted' . date('YmdHis');
    $sql = "UPDATE `dataMemo` SET `statcode` = 88888, `Name` = CONCAT(`Name`, ?) WHERE `indx` = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("si", $deletedSuffix, $indx);
        $stmt->execute();
        $stmt->close();
    }

    $conn->close();
}

// Redirect back to the memos list page no matter what
header("Location: memos.php");
exit;
