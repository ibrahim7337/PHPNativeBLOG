<?php
require_once __DIR__ . "/../../core/Database.php";
require_once __DIR__ . "/../middleware/auth.php";

$db = (new Database())->getConnection();
$user = authenticate();

$token = null; // Set token null
$stmt = $db->prepare("UPDATE users SET token = :token WHERE id = :id");
$stmt->execute(['token' => $token, 'id' => $user['id']]);

echo json_encode([
    'token' => $token,
]);