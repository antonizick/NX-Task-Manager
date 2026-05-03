<?php
require_once 'auth_check.php';

// Because nulls are the freeloaders of the data world...
function resolvNulls(&$var) {
    if (strlen(trim($var)) == 0) {
        $var = null;
    }
}

if (isset($_POST['AddRecord'])) {

    // Collect all the sweet, sweet data from the form
    $name        = $_POST['name'];
    $link        = $_POST['link'];
    $so          = $_POST['so'];
    $category    = $_POST['category'];
    $description = $_POST['description'];
    $contact     = $_POST['contact'];
    $tier        = $_POST['tier'];
    $lclass      = $_POST['lclass'];
    $date_created = date('Y-m-d H:i:s'); // current time
    $last_updated = $date_created;       // same on creation

    // Resolve any pesky empty fields
    resolvNulls($so);
    resolvNulls($category);
    resolvNulls($contact);
    resolvNulls($tier);
    resolvNulls($lclass);

    require_once 'dbcon.php';

    // Prepare and bind like a well-dressed dinner party
    $stmt = $conn->prepare("INSERT INTO `dataNxlinks` (`Name`, `Category`, `SO`, `Description`, `contact`, `Link`, `date_created`, `last_updated`, `tier`, `lclass`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssisssssss", $name, $category, $so, $description, $contact, $link, $date_created, $last_updated, $tier, $lclass);

    if ($stmt->execute()) {
        // You did it! Cue applause.
        header('Location: ltask.php?message=Record+Added+Successfully');
        exit;
    } else {
        // Something broke. Make sure you cry in a logged file.
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
