<?php

header("Content-Type: application/json");
require_once __DIR__ . "/../../core/Database.php";

require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../middleware/role.php';

class UserAPI
{
    private $db;
    private $user; // authenticated user
    private $checkAdmin; // authorization

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->user = authenticate();
        $this->checkAdmin = admin($this->user['id']);
    }

    public function getAll()
    {
        // Auth
        $this->user['id'];
        $stmt = $this->db->query("SELECT * FROM users");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get($id)
    {
        // Auth
        $this->user['id'];

        // Authorization
        $this->checkAdmin;

        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        // Auth
        $this->user['id'];

        // Authorization
        $this->checkAdmin;

        $errors = [];

        $username = trim($data['username'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = trim($data['password'] ?? '');
        $role = trim($data['role'] ?? '');

        // Validations
        if (!$username || !$email || !$password) {
            $errors[] = "All fields are required.";
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email address.";
        }

        if (strlen($password) < 6) {
            $errors[] = "Password must be at least 6 characters.";
        }

        // Check if email exists
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) {
            $errors[] = "Email already exists.";
        }

        if (!empty($errors)) {
            return ['errors' => $errors];
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        $result = $stmt->execute([$username, $email, $hashedPassword, $role]);

        return ['success' => "User created successfully."];
    }

    public function update($id, $data)
    {
        // Auth
        $this->user['id'];

        // Authorization
        $this->checkAdmin;

        $errors = [];

        // Ensure user exists
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        if (!$stmt->fetch()) {
            return ['error' => "Invalid user ID."];
        }

        $username = trim($data['username'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = trim($data['password'] ?? '');
        $confirm_password = trim($data['confirm_password'] ?? '');
        $role = trim($data['role'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email address.";
        }

        // Check if email belongs to another user
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = :email AND id != :id LIMIT 1");
        $stmt->execute(['email' => $email, 'id' => $id]);
        if ($stmt->fetch()) {
            $errors[] = "Email already exists for another user.";
        }

        if (!empty($errors)) {
            return ['errors' => $errors];
        }

        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $this->db->prepare("UPDATE users SET username = :username, email = :email, password = :password, role = :role WHERE id = :id");
            $executed = $stmt->execute([
                'username' => $username,
                'email' => $email,
                'password' => $hashedPassword,
                'role' => $role,
                'id' => $id
            ]);
        } else {
            $stmt = $this->db->prepare("UPDATE users SET username = :username, email = :email, role = :role WHERE id = :id");
            $result = $stmt->execute([
                'username' => $username,
                'email' => $email,
                'role' => $role,
                'id' => $id
            ]);
        }

        return ['success' => "User updated successfully."];
    }

    public function delete($id)
    {
        // Auth
        $this->user['id'];

        // Authorization
        $this->checkAdmin;

        // Check if user exists
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        if (!$stmt->fetch()) {
            return ['error' => "Invalid user ID."];
        }

        $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
        $executed = $stmt->execute(['id' => $id]);

        if ($executed) {
            return ['success' => "User deleted successfully."];
        } else {
            return ['error' => "Delete failed. Please try again."];
        }
    }
}