<?php
require_once 'auth_check.php';
require_once 'dbcon.php'; // Keeping it classy and consistent

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['AddRecord'])) {
    // Get and sanitize input
    $name = trim($_POST['name']);
    $color = trim($_POST['color']);
    $fcolor = trim($_POST['fcolor']);

    // Basic validation
    if (!empty($name) && !empty($color) && !empty($fcolor)) {
        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO dataTaskCategories (name, color, fcolor) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $color, $fcolor);

        if ($stmt->execute()) {
            // Redirect or success message
            header("Location: mx.php");
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
