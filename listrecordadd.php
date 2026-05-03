<?php
require_once 'auth_check.php';

function resolvNulls(&$var) {
    if (strlen(trim($var)) == 0) {
        $var = null;
    }
}

if (isset($_POST['AddRecord'])) {

    // Grab form fields
    $lcode = $_POST['lcode'];
    $in1 = $_POST['in1'] ?? 999;
    $in2 = $_POST['in2'] ?? 0;
    $in3 = $_POST['in3'] ?? 0;
    $Name = $_POST['Name'];
    $dat1 = $_POST['dat1'];
    $dat2 = $_POST['dat2'];
    $dat3 = $_POST['dat3'] ?? '.';;
    $dat4 = $_POST['dat4'] ?? '.';;

        // Null-check and assign '.' if needed
        if (is_null($dat3)) {
            $dat3 = '.';
        }
        if (is_null($dat4)) {
            $dat4 = '.';
        }
/*
    // Apply null resolver on all fields
    resolvNulls($lcode);
    resolvNulls($in1);
    resolvNulls($in2);
    resolvNulls($in3);
    resolvNulls($Name);
    resolvNulls($dat1);
    resolvNulls($dat2);
    resolvNulls($dat3);
    resolvNulls($dat4);
*/
    require_once 'dbcon.php';

    // Prepare and execute insert into `dataLists`
    $stmt = $conn->prepare("INSERT INTO `dataLists` (`lcode`, `in1`, `in2`, `in3`, `Name`, `dat1`, `dat2`,`dat3`, `dat4`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("siiisssss", $lcode, $in1, $in2, $in3, $Name, $dat1, $dat2, $dat3, $dat4);
    $stmt->execute();

    // Optional: check for success
    if ($stmt->affected_rows > 0) {
        header('Location: listList.php?c='.$lcode);
        exit;
    } else {
        echo "Oops. Something went wrong.";
    }

    $stmt->close();
    $conn->close();
}
?>
