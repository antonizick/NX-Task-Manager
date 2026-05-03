<?php
// parchive-fetcher.php — DataTables JSON feed for personal archived tasks
require_once 'auth_check.php';

header('Content-Type: application/json');

require_once 'dbcon.php';

// Query the archived personal tasks view
$sql = "SELECT * FROM viewPArchivedTasks";
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

