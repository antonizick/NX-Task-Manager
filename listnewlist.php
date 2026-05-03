<?php
require_once 'auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['lcode'])) {
    header('Location: lists.php');
    exit;
}

$lcode = trim($_POST['lcode']);

if ($lcode === '') {
    header('Location: lists.php?message=List+name+cannot+be+empty.');
    exit;
}

require_once 'dbcon.php';

$stmt = $conn->prepare(
    "INSERT INTO `dataLists` (`lcode`, `in1`, `in2`, `in3`, `Name`, `dat1`, `dat2`, `dat3`, `dat4`)
     VALUES (?, 999, 0, 0, '', '', '', '.', '.')"
);
$stmt->bind_param("s", $lcode);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    $stmt->close();
    $conn->close();
    header('Location: lists.php?message=List+created+successfully.');
} else {
    $stmt->close();
    $conn->close();
    header('Location: lists.php?message=Could+not+create+list.');
}
exit;
