<?php

// Database credentials — adjust your password before some hacker named Chad finds it
$host = '127.0.0.1:3308';
$user = '< user name >';
$password = '< giant password >';
$database = '< DB name >';

// Connect to the database
$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die(json_encode([
        "error" => "Epic fail: " . $conn->connect_error
    ]));
}
?>
