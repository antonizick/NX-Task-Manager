<?php
// pdata-fetcher.php — the silent backend wizard
require_once 'auth_check.php';

header('Content-Type: application/json');

require_once 'dbcon.php';


$sql = "SELECT * FROM viewDataStatus WHERE rowstat <> 88888";
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