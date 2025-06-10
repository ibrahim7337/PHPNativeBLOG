<?php

require_once __DIR__ . "/../../core/Database.php";

$db = (new Database())->getConnection();

$data = json_decode(file_get_contents('php://input'), true);

$username = $data['username'] ?? "";
$email = $data['email'] ?? "";
$password = $data['password'] ?? "";

// Validation
$errors = [];

if (!$username || !$email || !$password) {
    echo json_encode(['error' => 'Missing fields']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email address.";
}

if (strlen($password) < 6) {
    $errors[] = "Password must be at least 6 characters.";
}

// Check if email exists
$stmt = $db->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
$stmt->execute(['email' => $email]);
if ($stmt->fetch()) {
    $errors[] = "Email already exists.";
}

if (!empty($errors)) {
    echo json_encode(['errors' => $errors]);
    exit;
}

// Hash password
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// Insert into database
$query = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
$stmt = $db->prepare($query);
$result = $stmt->execute([
    $username,
    $email,
    $hashedPassword,
]);

echo json_encode(['success' => $result]);