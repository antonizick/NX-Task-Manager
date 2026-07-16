<?php
// pdata-fetcher.php — the silent backend wizard
require_once 'auth_check.php';

header('Content-Type: application/json');
$valuePass = isset($_POST['valpass']) ? $_POST['valpass'] : '';

require_once 'dbcon.php';

// Query the data table
if (!empty($valuePass)) {
    $stmt = $conn->prepare("SELECT * FROM viewListData WHERE lcode = ?");
    $stmt->bind_param("s", $valuePass);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM viewListData");
}

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