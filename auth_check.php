<?php

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    // AJAX / JSON requests get a 401 JSON response instead of a redirect
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
        && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    if ($isAjax) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Session expired. Please log in again.']);
        exit;
    }

    header('Location: login.php');
    exit;
}
