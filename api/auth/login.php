<?php
require_once __DIR__ . "/../../core/Database.php";
$db = (new Database())->getConnection();

$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

$stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
$stmt->execute(['email' => $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($password, $user['password'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid credentials']);
    exit;
}

$token = bin2hex(random_bytes(32)); // Generate a random token
$stmt = $db->prepare("UPDATE users SET token = :token WHERE id = :id");
$stmt->execute(['token' => $token, 'id' => $user['id']]);

echo json_encode([
    'token' => $token,
    'user_id' => $user['id'],
    'username' => $user['username']
]);