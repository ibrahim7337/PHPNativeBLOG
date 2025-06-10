<?php

require_once __DIR__ . "/../../core/Database.php";

function admin($id)
{
    $db = (new Database())->getConnection();

    $stmt = $db->prepare('SELECT * FROM users WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user['role'] != 'admin') {
        http_response_code(401);
        echo json_encode(['error' => 'You are not allowed to access']);
        exit;
    }

    return $user;
}