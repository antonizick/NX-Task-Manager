<?php
require_once 'auth_check.php';

function resolvNulls(&$var) {
    if (strlen(trim($var)) == 0) {
        $var = 0;
    }
}

function resolvDateNulls(&$var) {
    if (strlen(trim($var)) == 0) {
        $var = null;
    }
}

if (isset($_POST['AddRecord'])) {

    $priority2day = $_POST['priority2day'];
    $description = $_POST['description'];
    $urgency = $_POST['urgency'];
    $status = $_POST['status'];
    $elenav = $_POST['elenav'];
    $project = $_POST['project'];
    $narritive = $_POST['narritive'];
    $cryodate = $_POST['cryodate'];
    $deadline1 = $_POST['deadline1'];
    $links = $_POST['links'];
    $dwe = "personal";

    // Clean up nulls
    resolvNulls($priority2day);
    resolvNulls($urgency);
    resolvNulls($status);
    resolvNulls($elenav);
    resolvDateNulls($cryodate);
    resolvDateNulls($deadline1);

    require_once 'dbcon.php';

    // Step 1: Insert the new record (pkind auto-increments)
    $stmt = $conn->prepare("INSERT INTO `datatasks` (`priority2day`, `description`, `urgency`, `status`, `elenav`, `project`, `narritive`, `cryo date`, `deadline1`, `links`, `dwe`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("isisissssss", $priority2day, $description, $urgency, $status, $elenav, $project, $narritive, $cryodate, $deadline1, $links, $dwe);
    $stmt->execute();
    $stmt->close();

    $conn->close();

    // Optional success message / redirect
    header('location:ptask.php?message=Record Added Successfully');
    exit;
}
?>
