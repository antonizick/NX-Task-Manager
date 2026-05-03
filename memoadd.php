<?php
require_once 'auth_check.php';
require_once 'dbcon.php'; // Keeping it classy and consistent

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['AddRecord'])) {
    // Get and sanitize input
    $name = trim($_POST['Name']);
    $memo = trim($_POST['memo']);

    // Basic validation
    if (!empty($name) && !empty($memo)) {
        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO `dataMemo`(`Name`, `memo`) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $memo);

        if ($stmt->execute()) {
            // Redirect or success message
            header("Location: memos.php");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "All fields are required.";
    }
} else {
    echo "Invalid request.";
}
?>
