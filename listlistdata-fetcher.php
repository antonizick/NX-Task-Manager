<?php
// pdata-fetcher.php — the silent backend wizard
require_once 'auth_check.php';

header('Content-Type: application/json');
$valuePass = isset($_POST['valpass']) ? $_POST['valpass'] : '';

require_once 'dbcon.php';

// Query the data table
if (!empty($valuePass)) {
    $lengthOfValpass = mb_strlen($valuePass, 'UTF-8');
    $sql = "SELECT * FROM viewListData WHERE lcode = '". $valuePass."'";
  	// $sql = "SELECT * FROM viewListData WHERE lcode = '". $valuePass ."' AND LENGTH(lcode) = LENGTH('". $valuePass ."')";
    // $sql = "SELECT * FROM viewListData WHERE lcode = '$valuePass'";

} else {
    $sql = "SELECT * FROM viewListData";
}

$result = $conn->query($sql);

$data = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}                                                                               

// Return JSON that DataTables understands
echo json_encode([
    "data" => $data
]);

$conn->close();
?> 