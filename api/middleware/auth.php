<?php

require_once __DIR__ . "/../../core/Database.php";

// auth.php
function authenticate()
{
    $db = (new Database())->getConnection();
    $headers = getallheaders();
    $token = $headers['Authorization'] ?? '';

    if (!$token) {
        http_response_code(401);
        echo json_encode(['error' => 'Token required']);
        exit;
    }

    $stmt = $db->prepare('SELECT * FROM users WHERE token = :token');
    $stmt->execute(['token' => $token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid token']);
        exit;
    }

    return $user;
}